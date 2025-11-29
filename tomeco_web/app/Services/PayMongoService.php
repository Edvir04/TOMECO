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

            Log::error('PayMongo Checkout Session Error: ' . $response->body());
            Log::error('PayMongo Response Status: ' . $response->status());
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
     * Verify webhook signature
     */
    public function verifyWebhookSignature($payload, $signature, $secret)
    {
        $computedSignature = hash_hmac('sha256', $payload, $secret);
        return hash_equals($computedSignature, $signature);
    }
}

