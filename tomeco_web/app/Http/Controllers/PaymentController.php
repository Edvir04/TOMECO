<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Services\PayMongoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

            // Calculate payment amount (you can customize this based on your business logic)
            // For now, using a default amount or from ticket if available
            $amount = 500.00; // Default amount in PHP pesos
            // You can add a 'fine_amount' or 'payment_amount' field to tickets table if needed

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
                return redirect()->route('violator.portal')
                    ->with('error', 'Unable to initiate payment. Please try again later.')
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
        try {
            $payload = $request->getContent();
            $signature = $request->header('Paymongo-Signature');
            
            // Verify webhook signature
            $webhookSecret = config('services.paymongo.webhook_secret');
            if ($webhookSecret && !$this->payMongoService->verifyWebhookSignature($payload, $signature, $webhookSecret)) {
                Log::warning('Invalid PayMongo webhook signature');
                return response()->json(['error' => 'Invalid signature'], 401);
            }

            $data = json_decode($payload, true);
            
            if (!isset($data['data']['attributes']['type'])) {
                return response()->json(['error' => 'Invalid webhook data'], 400);
            }

            $eventType = $data['data']['attributes']['type'];
            
            // Handle different event types
            switch ($eventType) {
                case 'payment.paid':
                    $this->handlePaymentPaid($data);
                    break;
                case 'payment.failed':
                    $this->handlePaymentFailed($data);
                    break;
                default:
                    Log::info('Unhandled PayMongo webhook event: ' . $eventType);
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('PayMongo webhook error: ' . $e->getMessage());
            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Handle successful payment
     */
    private function handlePaymentPaid($data)
    {
        try {
            $metadata = $data['data']['attributes']['metadata'] ?? [];
            $citationNumber = $metadata['citation_number'] ?? null;
            $ticketId = $metadata['ticket_id'] ?? null;
            $amount = $data['data']['attributes']['amount'] ?? 0;
            $amount = $amount / 100; // Convert from centavos to pesos

            if ($ticketId) {
                $ticket = Ticket::find($ticketId);
                if ($ticket) {
                    // Update ticket payment status
                    // You can add a 'payment_status' field to tickets table
                    // $ticket->update(['payment_status' => 'paid', 'paid_at' => now()]);
                    
                    Log::info('Payment successful for ticket: ' . $ticketId . ' - Citation: ' . $citationNumber);
                }
            }

            // You can also create a payment record here
            // Payment::create([...]);
        } catch (\Exception $e) {
            Log::error('Error handling payment paid: ' . $e->getMessage());
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

