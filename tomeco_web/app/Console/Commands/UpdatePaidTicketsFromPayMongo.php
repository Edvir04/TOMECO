<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use App\Services\PayMongoService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdatePaidTicketsFromPayMongo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:update-paid-dates 
                            {--limit=100 : Number of checkout sessions to fetch per page}
                            {--max-pages=10 : Maximum number of pages to process}
                            {--use-updated-at : Use ticket updated_at as fallback if PayMongo data not available}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update paid_at dates for paid tickets by fetching payment data from PayMongo API';

    protected $payMongoService;

    public function __construct(PayMongoService $payMongoService)
    {
        parent::__construct();
        $this->payMongoService = $payMongoService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to update paid tickets with payment dates from PayMongo...');

        // Get all paid tickets that don't have paid_at set
        $ticketsWithoutPaidAt = Ticket::where('status', 'Paid')
            ->whereNull('paid_at')
            ->get();

        $this->info("Found {$ticketsWithoutPaidAt->count()} paid tickets without paid_at date.");

        if ($ticketsWithoutPaidAt->isEmpty()) {
            $this->info('No tickets to update. Exiting.');
            return 0;
        }

        // Create a map of citation numbers to tickets for quick lookup
        $ticketsByCitation = $ticketsWithoutPaidAt->keyBy('citation_number');

        $limit = (int) $this->option('limit');
        $maxPages = (int) $this->option('max-pages');
        $updatedCount = 0;
        $processedCount = 0;
        $after = null;
        $page = 1;

        $this->info("Fetching checkout sessions from PayMongo (limit: {$limit}, max pages: {$maxPages})...");

        do {
            $this->info("Processing page {$page}...");

            // Fetch checkout sessions from PayMongo
            $response = $this->payMongoService->listCheckoutSessions($limit, $after);

            if (!$response) {
                $this->warn('No response from PayMongo API. This could be due to:');
                $this->warn('- API authentication issues');
                $this->warn('- Network connectivity problems');
                $this->warn('- Invalid API configuration');
                $this->warn('Response: ' . json_encode($response));
                break;
            }

            if (!isset($response['data'])) {
                $this->warn('Invalid response structure from PayMongo API.');
                $this->warn('Response keys: ' . implode(', ', array_keys($response)));
                $this->warn('Full response: ' . json_encode($response));
                break;
            }

            $checkoutSessions = $response['data'];
            $this->info("Found " . count($checkoutSessions) . " checkout sessions on page {$page}.");

            foreach ($checkoutSessions as $session) {
                $processedCount++;

                try {
                    $attributes = $session['attributes'] ?? [];
                    $metadata = $attributes['metadata'] ?? [];
                    $citationNumber = $metadata['citation_number'] ?? null;

                    if (!$citationNumber) {
                        continue; // Skip sessions without citation number in metadata
                    }

                    // Check if we have a ticket for this citation number
                    if (!$ticketsByCitation->has($citationNumber)) {
                        continue; // Skip if we don't have a matching ticket
                    }

                    $ticket = $ticketsByCitation->get($citationNumber);

                    // Check if this session is paid
                    $status = $attributes['status'] ?? null;
                    if ($status !== 'paid') {
                        continue; // Skip unpaid sessions
                    }

                    // Extract payment date
                    $paidAt = null;
                    
                    // Try to get paid_at from checkout session
                    $paidAt = $attributes['paid_at'] ?? 
                             $attributes['created_at'] ?? 
                             null;

                    // If not found, try to get from payment data
                    if (!$paidAt && isset($attributes['payment'])) {
                        $paymentData = $attributes['payment'];
                        if (is_array($paymentData) && isset($paymentData['attributes'])) {
                            $paidAt = $paymentData['attributes']['paid_at'] ?? 
                                     $paymentData['attributes']['created_at'] ?? null;
                        }
                    }

                    // If still not found, use created_at as fallback
                    if (!$paidAt) {
                        $paidAt = $attributes['created_at'] ?? null;
                    }

                    if ($paidAt) {
                        try {
                            $paidAtDate = Carbon::parse($paidAt);
                            
                            // Update the ticket
                            $ticket->paid_at = $paidAtDate;
                            $ticket->save();

                            $updatedCount++;
                            $this->info("✓ Updated ticket #{$ticket->id} (Citation: {$citationNumber}) with paid_at: {$paidAtDate->format('Y-m-d H:i:s')}");

                            // Remove from map to avoid processing again
                            $ticketsByCitation->forget($citationNumber);

                            // If all tickets are updated, we can stop
                            if ($ticketsByCitation->isEmpty()) {
                                $this->info('All tickets have been updated. Stopping...');
                                break 2; // Break out of both loops
                            }
                        } catch (\Exception $e) {
                            $this->warn("Failed to parse date for ticket #{$ticket->id}: {$e->getMessage()}");
                            Log::error("Failed to update ticket #{$ticket->id} with paid_at", [
                                'error' => $e->getMessage(),
                                'paid_at_raw' => $paidAt
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    $this->warn("Error processing checkout session: {$e->getMessage()}");
                    Log::error("Error processing checkout session", [
                        'error' => $e->getMessage(),
                        'session_id' => $session['id'] ?? 'unknown'
                    ]);
                }
            }

            // Check if there are more pages
            $hasMore = isset($response['has_more']) && $response['has_more'] === true;
            if ($hasMore && isset($response['data']) && !empty($response['data'])) {
                // Get the last item's ID for pagination
                $lastItem = end($response['data']);
                $after = $lastItem['id'] ?? null;
            } else {
                $hasMore = false;
            }

            $page++;

            // Add a small delay to avoid rate limiting
            if ($hasMore && $page <= $maxPages) {
                sleep(1);
            }

        } while ($hasMore && $page <= $maxPages && !$ticketsByCitation->isEmpty());

        $this->info("\n=== Summary ===");
        $this->info("Processed checkout sessions: {$processedCount}");
        $this->info("Updated tickets: {$updatedCount}");
        $this->info("Remaining tickets without paid_at: {$ticketsByCitation->count()}");

        // If --use-updated-at flag is set and there are still tickets without paid_at,
        // use their updated_at timestamp as a fallback
        if ($this->option('use-updated-at') && $ticketsByCitation->count() > 0) {
            $this->info("\nUsing updated_at as fallback for remaining tickets...");
            
            foreach ($ticketsByCitation as $ticket) {
                if ($ticket->updated_at) {
                    $ticket->paid_at = $ticket->updated_at;
                    $ticket->save();
                    $updatedCount++;
                    $this->info("✓ Updated ticket #{$ticket->id} (Citation: {$ticket->citation_number}) with updated_at: {$ticket->updated_at->format('Y-m-d H:i:s')}");
                }
            }
            
            $this->info("Updated {$updatedCount} additional tickets using updated_at as fallback.");
        }

        if ($ticketsByCitation->count() > 0 && !$this->option('use-updated-at')) {
            $this->warn("\nSome tickets could not be updated. Possible reasons:");
            $this->warn("- Payment not found in PayMongo (checkout session not created or deleted)");
            $this->warn("- Payment not yet completed");
            $this->warn("- Citation number mismatch in metadata");
            $this->warn("- Reached maximum pages limit");
            $this->info("\nTip: Run with --use-updated-at flag to use ticket updated_at as fallback");
        }

        return 0;
    }
}
