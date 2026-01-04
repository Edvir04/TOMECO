<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Services\PayMongoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PaymentController extends Controller
{
    protected $payMongoService;

    public function __construct(PayMongoService $payMongoService)
    {
        $this->payMongoService = $payMongoService;
    }

    /**
     * Initiate GCash payment via PayMongo
     */
    public function initiateGCashPayment(Request $request)
    {
        $request->validate([
            'citation_number' => 'required|string|max:255',
            'ticket_id' => 'nullable|integer|exists:tickets,id',
        ]);

        try {
            $citationNumber = $request->input('citation_number');
            $ticketId = $request->input('ticket_id');
            
            // Get ticket information
            $ticket = $ticketId 
                ? Ticket::find($ticketId)
                : Ticket::where('citation_number', $citationNumber)->first();

            if (!$ticket) {
                return redirect()->route('violator.portal')
                    ->with('error', 'Ticket not found.')
                    ->withInput();
            }

            // Calculate payment amount from ticket price, or use default if not set
            $amount = $ticket->price ?? 1.00; // Use ticket price, default to 1.00 PHP if not set

            // Prepare payment data
            $description = 'Traffic Violation Ticket Payment - Citation #' . $citationNumber;
            $metadata = [
                'citation_number' => $citationNumber,
                'ticket_id' => $ticket->id,
                'customer_name' => trim(($ticket->driver_firstname ?? '') . ' ' . ($ticket->driver_lastname ?? '')),
            ];

            // Create checkout session with PayMongo
            $successUrl = route('violator.payment.success', ['citation_number' => $citationNumber]);
            $cancelUrl = route('violator.payment.cancel', ['citation_number' => $citationNumber]);

            $checkoutSession = $this->payMongoService->createCheckoutSession(
                $amount,
                $description,
                $successUrl,
                $cancelUrl,
                $metadata
            );

            if (!$checkoutSession || !isset($checkoutSession['data']['attributes']['checkout_url'])) {
                Log::error('Failed to create PayMongo checkout session');
                Log::error('Checkout session response: ' . json_encode($checkoutSession));
                return redirect()->route('violator.portal')
                    ->with('error', 'Unable to initiate payment. Please check your PayMongo account configuration or try again later.')
                    ->withInput();
            }

            // Store payment data in session
            session([
                'payment_data' => [
                    'citation_number' => $citationNumber,
                    'ticket_id' => $ticket->id,
                    'amount' => $amount,
                    'description' => $description,
                    'checkout_session_id' => $checkoutSession['data']['id'],
                ],
                'payment_type' => 'gcash'
            ]);

            // Redirect to PayMongo checkout page
            $checkoutUrl = $checkoutSession['data']['attributes']['checkout_url'];
            return redirect($checkoutUrl);

        } catch (\Exception $e) {
            Log::error('GCash payment initiation error: ' . $e->getMessage());
            return redirect()->route('violator.portal')
                ->with('error', 'Error initiating payment. Please try again.')
                ->withInput();
        }
    }

    /**
     * Process GCash payment (fallback - redirects to PayMongo)
     */
    public function processGCashPayment(Request $request)
    {
        $paymentData = session('payment_data');
        
        if (!$paymentData) {
            return redirect()->route('violator.portal')
                ->with('error', 'Payment session expired. Please try again.');
        }

        // This should not be reached if checkout session was created successfully
        // But kept as fallback
        return view('gcash-payment', [
            'payment_data' => $paymentData
        ]);
    }

    /**
     * Handle successful payment return
     */
    public function paymentSuccess(Request $request, $citationNumber)
    {
        try {
            $paymentData = session('payment_data');
            
            if (!$paymentData || $paymentData['citation_number'] !== $citationNumber) {
                return redirect()->route('violator.portal')
                    ->with('error', 'Invalid payment session.');
            }

            // Clear payment session
            session()->forget('payment_data');

            return redirect()->route('violator.portal')
                ->with('success', 'Payment successful! Your ticket has been paid. Citation #' . $citationNumber);

        } catch (\Exception $e) {
            Log::error('Payment success error: ' . $e->getMessage());
            return redirect()->route('violator.portal')
                ->with('error', 'Error processing payment confirmation.');
        }
    }

    /**
     * Handle cancelled payment
     */
    public function paymentCancel(Request $request, $citationNumber)
    {
        session()->forget('payment_data');
        
        return redirect()->route('violator.portal')
            ->with('error', 'Payment was cancelled. You can try again anytime.');
    }

    /**
     * Handle PayMongo webhook
     */
    public function gcashCallback(Request $request)
    {
        // Log that the method was called
        Log::info('=== PAYMONGO WEBHOOK CALLBACK CALLED ===', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
        ]);
        
        try {
            $payload = $request->getContent();
            $signature = $request->header('Paymongo-Signature');
            
            // Log incoming webhook for debugging
            Log::info('PayMongo webhook received', [
                'has_signature' => !empty($signature),
                'payload_length' => strlen($payload),
                'payload_preview' => substr($payload, 0, 200),
                'headers' => $request->headers->all()
            ]);
            
            // Verify webhook signature
            $webhookSecret = config('services.paymongo.webhook_secret');
            if ($webhookSecret && !$this->payMongoService->verifyWebhookSignature($payload, $signature, $webhookSecret)) {
                Log::warning('Invalid PayMongo webhook signature', [
                    'has_secret' => !empty($webhookSecret),
                    'signature' => $signature
                ]);
                return response()->json(['error' => 'Invalid signature'], 401);
            }

            $data = json_decode($payload, true);
            
            // Log the webhook data structure
            Log::info('PayMongo webhook data structure', [
                'has_data' => isset($data['data']),
                'event_type' => $data['data']['attributes']['type'] ?? 'unknown',
                'full_data' => $data
            ]);
            
            if (!isset($data['data']['attributes']['type'])) {
                Log::error('Invalid webhook data - missing event type', ['data' => $data]);
                return response()->json(['error' => 'Invalid webhook data'], 400);
            }

            $eventType = $data['data']['attributes']['type'];
            Log::info('Processing PayMongo webhook event: ' . $eventType, [
                'event_type' => $eventType,
                'event_structure' => array_keys($data['data']['attributes'] ?? [])
            ]);
            
            // Handle different event types
            // PayMongo uses various formats:
            // - checkout_session.payment.paid (with dots)
            // - checkout_session.payment_succeeded (with underscores)
            // - payment.paid
            // - source.chargeable (for some payment methods)
            
            // Normalize event type for comparison (handle both dots and underscores)
            $normalizedEventType = str_replace('_', '.', strtolower($eventType));
            
            // Check if this is a payment success event (more flexible matching)
            if (str_contains($normalizedEventType, 'payment.paid') || 
                str_contains($normalizedEventType, 'payment.succeeded') ||
                str_contains($normalizedEventType, 'checkout.session.payment.paid') ||
                str_contains($normalizedEventType, 'checkout.session.payment.succeeded')) {
                Log::info('Detected payment success event, calling handlePaymentPaid');
                $this->handlePaymentPaid($data);
            }
            // Check if this is a payment failed event
            elseif (str_contains($normalizedEventType, 'payment.failed') || 
                    str_contains($normalizedEventType, 'checkout.session.payment.failed')) {
                Log::info('Detected payment failed event, calling handlePaymentFailed');
                $this->handlePaymentFailed($data);
            }
            else {
                Log::info('Unhandled PayMongo webhook event: ' . $eventType, [
                    'normalized_type' => $normalizedEventType,
                    'data' => $data
                ]);
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('PayMongo webhook error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Handle successful payment
     */
    private function handlePaymentPaid($data)
    {
        try {
            Log::info('Processing payment paid webhook', ['data_structure' => $data]);
            
            // Handle both payment and checkout_session webhook structures
            $attributes = $data['data']['attributes'] ?? [];
            
            // Initialize variables
            $metadata = [];
            $amount = 0;
            $citationNumber = null;
            $ticketId = null;
            $paidAt = null;
            
            // For checkout_session events, the data structure is different
            // PayMongo checkout_session webhook structure: data.attributes.data.attributes
            if (isset($attributes['data']) && isset($attributes['data']['attributes'])) {
                // This is a checkout_session event
                $checkoutAttributes = $attributes['data']['attributes'];
                $metadata = $checkoutAttributes['metadata'] ?? [];
                
                // Get amount from checkout session line items
                $lineItems = $checkoutAttributes['line_items'] ?? [];
                if (!empty($lineItems)) {
                    $amount = $lineItems[0]['amount'] ?? 0;
                }
                
                // Try to get payment date from checkout session
                // Check for paid_at, created_at, or updated_at in various locations
                $paidAt = $checkoutAttributes['paid_at'] ?? 
                         $checkoutAttributes['created_at'] ?? 
                         $attributes['paid_at'] ?? 
                         $attributes['created_at'] ?? null;
                
                // Also check in nested payment data if available
                if (!$paidAt && isset($checkoutAttributes['payment'])) {
                    $paymentData = $checkoutAttributes['payment'];
                    if (is_array($paymentData) && isset($paymentData['attributes'])) {
                        $paidAt = $paymentData['attributes']['paid_at'] ?? 
                                 $paymentData['attributes']['created_at'] ?? null;
                    }
                }
                
                Log::info('Detected checkout_session event structure');
            } 
            // Alternative structure: metadata might be directly in attributes
            elseif (isset($attributes['metadata'])) {
                // This is a payment event or checkout_session with different structure
                $metadata = $attributes['metadata'] ?? [];
                $amount = $attributes['amount'] ?? 0;
                
                // Get payment date from payment attributes
                $paidAt = $attributes['paid_at'] ?? 
                         $attributes['created_at'] ?? 
                         $attributes['updated_at'] ?? null;
                
                Log::info('Detected payment event structure');
            }
            // Try to find metadata in nested structures
            else {
                // Check if metadata is in line_items
                if (isset($attributes['line_items']) && !empty($attributes['line_items'])) {
                    $lineItem = $attributes['line_items'][0];
                    $metadata = $lineItem['metadata'] ?? [];
                    $amount = $lineItem['amount'] ?? 0;
                    Log::info('Found metadata in line_items');
                }
                
                // Try to get payment date from top-level attributes
                $paidAt = $attributes['paid_at'] ?? 
                         $attributes['created_at'] ?? 
                         $attributes['updated_at'] ?? null;
            }
            
            // Extract ticket information from metadata
            $citationNumber = $metadata['citation_number'] ?? null;
            $ticketId = isset($metadata['ticket_id']) ? (int)$metadata['ticket_id'] : null;
            
            // Convert amount from centavos to pesos
            $amount = $amount / 100;
            
            // Parse payment date if available (PayMongo uses ISO 8601 format)
            if ($paidAt) {
                try {
                    $paidAtDate = Carbon::parse($paidAt);
                    Log::info('Extracted payment date from PayMongo', [
                        'paid_at_raw' => $paidAt,
                        'paid_at_parsed' => $paidAtDate->toDateTimeString()
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Failed to parse payment date from PayMongo', [
                        'paid_at' => $paidAt,
                        'error' => $e->getMessage()
                    ]);
                    $paidAtDate = null;
                }
            } else {
                // If no payment date found in webhook, use current time
                $paidAtDate = Carbon::now();
                Log::info('No payment date found in webhook, using current time');
            }

            Log::info('Extracted payment information', [
                'ticket_id' => $ticketId,
                'citation_number' => $citationNumber,
                'amount' => $amount,
                'metadata' => $metadata
            ]);

            // Find ticket by ID first, then fallback to citation number
            $ticket = null;
            if ($ticketId) {
                $ticket = Ticket::find($ticketId);
                if ($ticket) {
                    Log::info('Found ticket by ID', ['ticket_id' => $ticketId]);
                }
            }
            
            // Fallback: find by citation number if ticket not found by ID
            if (!$ticket && $citationNumber) {
                $ticket = Ticket::where('citation_number', $citationNumber)->first();
                if ($ticket) {
                    Log::info('Found ticket by citation number', ['citation_number' => $citationNumber]);
                }
            }

            if ($ticket) {
                // Get current status before update
                $oldStatus = $ticket->status;
                
                // Update ticket payment status and paid_at date from PayMongo
                $ticket->status = 'Paid';
                // Set paid_at from PayMongo API data, or use current time if not available
                if (isset($paidAtDate) && $paidAtDate) {
                    $ticket->paid_at = $paidAtDate;
                } elseif (!$ticket->paid_at) {
                    // Only set to current time if paid_at is not already set
                    $ticket->paid_at = Carbon::now();
                }
                
                $saved = $ticket->save();
                
                // Handle DSS payment reset if ticket was just paid
                if ($oldStatus !== 'Paid') {
                    $ticketController = app(\App\Http\Controllers\TicketController::class);
                    $ticketController->handlePaymentReset($ticket);
                }
                
                // Verify the update worked
                $ticket->refresh();
                $newStatus = $ticket->status;
                
                if ($saved && $newStatus === 'Paid') {
                    Log::info('✅ Payment successful - Ticket status updated', [
                        'ticket_id' => $ticket->id,
                        'citation_number' => $ticket->citation_number,
                        'amount' => 'PHP ' . number_format($amount, 2),
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'paid_at' => $ticket->paid_at ? $ticket->paid_at->toDateTimeString() : 'not set',
                        'saved' => $saved
                    ]);
                } else {
                    Log::error('❌ Failed to update ticket status', [
                        'ticket_id' => $ticket->id,
                        'saved' => $saved,
                        'expected_status' => 'Paid',
                        'actual_status' => $newStatus
                    ]);
                }
            } else {
                Log::warning('❌ Ticket not found for payment', [
                    'ticket_id' => $ticketId,
                    'citation_number' => $citationNumber,
                    'metadata' => $metadata
                ]);
            }

            // You can also create a payment record here
            // Payment::create([...]);
        } catch (\Exception $e) {
            Log::error('❌ Error handling payment paid: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'webhook_data' => json_encode($data)
            ]);
        }
    }

    /**
     * Handle failed payment
     */
    private function handlePaymentFailed($data)
    {
        try {
            $metadata = $data['data']['attributes']['metadata'] ?? [];
            $citationNumber = $metadata['citation_number'] ?? null;
            $ticketId = $metadata['ticket_id'] ?? null;

            Log::warning('Payment failed for ticket: ' . $ticketId . ' - Citation: ' . $citationNumber);
        } catch (\Exception $e) {
            Log::error('Error handling payment failed: ' . $e->getMessage());
        }
    }
}

