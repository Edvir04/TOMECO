<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayMongoService
{
    private $secretKey;
    private $publicKey;
    private $apiUrl;

    public function __construct()
    {
        $this->secretKey = config('services.paymongo.secret_key');
        $this->publicKey = config('services.paymongo.public_key');
        $this->apiUrl = config('services.paymongo.api_url');
    }

    /**
     * Create a payment intent for GCash
     */
    public function createPaymentIntent($amount, $description, $metadata = [])
    {
        try {
            $httpClient = Http::withBasicAuth($this->secretKey, '')
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ]);

            // For Windows/development: disable SSL verification if certificate bundle is missing
            if (config('app.env') === 'local' || config('app.debug')) {
                $httpClient = $httpClient->withoutVerifying();
            }

            $response = $httpClient->post($this->apiUrl . '/payment_intents', [
                'data' => [
                    'attributes' => [
                        'amount' => (int)($amount * 100), // Convert to centavos
                        'currency' => 'PHP',
                        'payment_method_allowed' => ['gcash'],
                        'description' => $description,
                        'metadata' => $metadata,
                    ],
                ],
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('PayMongo Payment Intent Error: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('PayMongo Service Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Attach payment method to payment intent
     */
    public function attachPaymentMethod($paymentIntentId, $paymentMethodId)
    {
        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($this->apiUrl . '/payment_intents/' . $paymentIntentId . '/attach', [
                    'data' => [
                        'attributes' => [
                            'payment_method' => $paymentMethodId,
                            'return_url' => route('gcash.payment.return'),
                        ],
                    ],
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('PayMongo Attach Payment Method Error: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('PayMongo Attach Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a payment method (for GCash)
     */
    public function createPaymentMethod($type = 'gcash')
    {
        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($this->apiUrl . '/payment_methods', [
                    'data' => [
                        'attributes' => [
                            'type' => $type,
                        ],
                    ],
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('PayMongo Payment Method Error: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('PayMongo Payment Method Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a checkout session (simpler approach)
     */
    public function createCheckoutSession($amount, $description, $successUrl, $cancelUrl, $metadata = [])
    {
        try {
            // Configure HTTP client with SSL verification
            $httpClient = Http::withBasicAuth($this->secretKey, '')
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ]);

            // For Windows/development: disable SSL verification if certificate bundle is missing
            // For production, ensure proper SSL certificates are configured
            if (config('app.env') === 'local' || config('app.debug')) {
                $httpClient = $httpClient->withoutVerifying();
            }

            $response = $httpClient->post($this->apiUrl . '/checkout_sessions', [
                'data' => [
                    'attributes' => [
                        'line_items' => [
                            [
                                'currency' => 'PHP',
                                'amount' => (int)($amount * 100), // Convert to centavos
                                'name' => $description,
                                'quantity' => 1,
                            ],
                        ],
                        'payment_method_types' => ['gcash'],
                        'success_url' => $successUrl,
                        'cancel_url' => $cancelUrl,
                        'description' => $description,
                        'metadata' => $metadata,
                    ],
                ],
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            // Log detailed error information
            $errorBody = $response->body();
            $errorStatus = $response->status();
            Log::error('PayMongo Checkout Session Error: ' . $errorBody);
            Log::error('PayMongo Response Status: ' . $errorStatus);
            
            // Try to parse error details if available
            $errorData = json_decode($errorBody, true);
            if (isset($errorData['errors'])) {
                foreach ($errorData['errors'] as $error) {
                    Log::error('PayMongo Error Detail: ' . json_encode($error));
                }
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('PayMongo Checkout Session Error: ' . $e->getMessage());
            Log::error('PayMongo Checkout Session Exception: ' . $e->getTraceAsString());
            return null;
        }
    }

    /**
     * Retrieve payment intent
     */
    public function getPaymentIntent($paymentIntentId)
    {
        try {
            $httpClient = Http::withBasicAuth($this->secretKey, '');

            // For Windows/development: disable SSL verification if certificate bundle is missing
            if (config('app.env') === 'local' || config('app.debug')) {
                $httpClient = $httpClient->withoutVerifying();
            }

            $response = $httpClient->get($this->apiUrl . '/payment_intents/' . $paymentIntentId);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('PayMongo Get Payment Intent Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Retrieve checkout session by ID
     */
    public function getCheckoutSession($checkoutSessionId)
    {
        try {
            $httpClient = Http::withBasicAuth($this->secretKey, '');

            // For Windows/development: disable SSL verification if certificate bundle is missing
            if (config('app.env') === 'local' || config('app.debug')) {
                $httpClient = $httpClient->withoutVerifying();
            }

            $response = $httpClient->get($this->apiUrl . '/checkout_sessions/' . $checkoutSessionId);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('PayMongo Get Checkout Session Error: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('PayMongo Get Checkout Session Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * List checkout sessions (with pagination)
     * PayMongo API supports limit and after parameters for pagination
     */
    public function listCheckoutSessions($limit = 100, $after = null)
    {
        try {
            $httpClient = Http::withBasicAuth($this->secretKey, '');

            // For Windows/development: disable SSL verification if certificate bundle is missing
            if (config('app.env') === 'local' || config('app.debug')) {
                $httpClient = $httpClient->withoutVerifying();
            }

            $url = $this->apiUrl . '/checkout_sessions?limit=' . $limit;
            if ($after) {
                $url .= '&after=' . $after;
            }

            $response = $httpClient->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('PayMongo List Checkout Sessions Error: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('PayMongo List Checkout Sessions Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Retrieve payment by ID
     */
    public function getPayment($paymentId)
    {
        try {
            $httpClient = Http::withBasicAuth($this->secretKey, '');

            // For Windows/development: disable SSL verification if certificate bundle is missing
            if (config('app.env') === 'local' || config('app.debug')) {
                $httpClient = $httpClient->withoutVerifying();
            }

            $response = $httpClient->get($this->apiUrl . '/payments/' . $paymentId);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('PayMongo Get Payment Error: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('PayMongo Get Payment Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Verify webhook signature
     * PayMongo uses format: t=timestamp,te=signature,li=...
     */
    public function verifyWebhookSignature($payload, $signature, $secret)
    {
        if (empty($signature) || empty($secret)) {
            return false;
        }
        
        // Parse PayMongo signature format: t=timestamp,te=signature,li=...
        $signatureParts = [];
        foreach (explode(',', $signature) as $part) {
            $keyValue = explode('=', $part, 2);
            if (count($keyValue) === 2) {
                $signatureParts[$keyValue[0]] = $keyValue[1];
            }
        }
        
        // Extract timestamp and signature
        $timestamp = $signatureParts['t'] ?? null;
        $receivedSignature = $signatureParts['te'] ?? null;
        
        if (!$timestamp || !$receivedSignature) {
            return false;
        }
        
        // Compute expected signature: HMAC-SHA256 of timestamp + payload
        $signedPayload = $timestamp . '.' . $payload;
        $expectedSignature = hash_hmac('sha256', $signedPayload, $secret);
        
        return hash_equals($expectedSignature, $receivedSignature);
    }
}

