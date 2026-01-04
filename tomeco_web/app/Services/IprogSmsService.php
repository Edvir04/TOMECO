<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class IprogSmsService
{
    protected ?string $apiToken;
    protected ?string $apiUrl;

    public function __construct()
    {
        $this->apiToken = config('services.iprogsms.token') ?: env('IPROG_SMS_API_TOKEN');
        $this->apiUrl = config('services.iprogsms.url') ?: env('IPROG_SMS_API_URL', 'https://www.iprogsms.com/api/v1/sms_messages');
        
        // Log configuration status (without exposing sensitive data)
        if (empty($this->apiToken)) {
            Log::warning('IPROG SMS: API token not configured. Please set IPROG_SMS_API_TOKEN in .env file.');
        }
        if (empty($this->apiUrl)) {
            Log::warning('IPROG SMS: API URL not configured. Please set IPROG_SMS_API_URL in .env file.');
        }
    }

    /**
     * Send a ticket creation notification to the driver's phone number.
     */
    public function sendTicketCreatedNotification(Ticket $ticket): bool
    {
        if (!$ticket->driver_contact) {
            Log::info('IPROG SMS: No driver contact number provided for ticket #' . ($ticket->citation_number ?? 'N/A'));
            return false;
        }

        Log::info('IPROG SMS: Attempting to send notification for ticket #' . ($ticket->citation_number ?? 'N/A'), [
            'phone' => $ticket->driver_contact,
        ]);

        $issuedDate = $ticket->issued_date
            ? $ticket->issued_date->format('M d, Y')
            : now()->format('M d, Y');

        // Format violations
        $violationsText = 'N/A';
        if ($ticket->violations && is_array($ticket->violations)) {
            $violationsList = array_filter($ticket->violations);
            if (!empty($violationsList)) {
                $violationsText = implode(', ', $violationsList);
                if ($ticket->violations_others_text) {
                    $violationsText .= ', ' . $ticket->violations_others_text;
                }
            } elseif ($ticket->violations_others_text) {
                $violationsText = $ticket->violations_others_text;
            }
        } elseif ($ticket->violations_others_text) {
            $violationsText = $ticket->violations_others_text;
        }

        // Format price
        $priceText = '';
        if ($ticket->price && $ticket->price > 0) {
            // Use "PHP" instead of "₱" to avoid encoding issues in SMS
            $priceText = sprintf('Amount: PHP %s. ', number_format($ticket->price, 2));
        }

        // Build message with website link, violations, and price
        $websiteUrl = env('VIOLATOR_PORTAL_URL', env('APP_URL', 'http://localhost:8001') . '/violator/portal');
        
        // Format message with full clickable URL
        $message = sprintf(
            "Hi %s %s, ticket #%s was issued on %s.\n\nViolations: %s\n%s\nView your ticket: %s\n\nPlease comply with TOMECO instructions.",
            trim($ticket->driver_firstname ?? ''),
            trim($ticket->driver_lastname ?? ''),
            $ticket->citation_number ?? 'N/A',
            $issuedDate,
            $violationsText,
            $priceText ? rtrim($priceText, '. ') . "\n" : '',
            $websiteUrl
        );

        return $this->sendMessage($ticket->driver_contact, $message);
    }

    /**
     * Send a generic SMS message via IPROG SMS.
     */
    public function sendMessage(string $phoneNumber, string $message): bool
    {
        // Check API credentials
        if (!$this->apiToken || !$this->apiUrl) {
            Log::warning('IPROG SMS: Missing API credentials.', [
                'has_token' => !empty($this->apiToken),
                'has_url' => !empty($this->apiUrl),
                'url' => $this->apiUrl,
            ]);
            return false;
        }

        // Normalize phone number
        $normalizedNumber = $this->normalizePhoneNumber($phoneNumber);
        if (!$normalizedNumber) {
            Log::warning('IPROG SMS: Invalid phone number provided.', [
                'original' => $phoneNumber,
                'normalized' => null,
            ]);
            return false;
        }

        Log::info('IPROG SMS: Sending message', [
            'original_phone' => $phoneNumber,
            'normalized_phone' => $normalizedNumber,
            'message_length' => strlen($message),
            'api_url' => $this->apiUrl,
        ]);

        try {
            // Prepare request data
            $requestData = [
                'api_token' => $this->apiToken,
                'message' => $message,
                'phone_number' => $normalizedNumber,
            ];

            Log::debug('IPROG SMS: Request data', [
                'url' => $this->apiUrl,
                'data_keys' => array_keys($requestData),
                'phone_number' => $normalizedNumber,
                'message_preview' => substr($message, 0, 50) . '...',
            ]);

            // Make API request - try both form and JSON formats
            // Some SMS APIs prefer JSON, others prefer form data
            $response = Http::timeout(30)
                ->withoutVerifying() // Bypass SSL issues in local development
                ->asForm()
                ->post($this->apiUrl, $requestData);

            $responseBody = $response->body();
            $responseData = json_decode($responseBody, true);
            
            Log::info('IPROG SMS: API Response', [
                'http_status' => $response->status(),
                'http_successful' => $response->successful(),
                'api_status' => $responseData['status'] ?? null,
                'api_message' => $responseData['message'] ?? null,
                'body' => $responseBody,
            ]);

            // Check if API actually succeeded (not just HTTP status)
            $apiSuccess = false;
            $messageId = null;
            $statusLink = null;
            
            if ($response->successful()) {
                // Check the actual API response status in the body
                if (isset($responseData['status'])) {
                    // Some APIs return status 200 for success, others use different codes
                    $apiSuccess = ($responseData['status'] == 200 || $responseData['status'] == 'success' || $responseData['status'] === true);
                } else {
                    // If no status field, assume success if HTTP is 200
                    $apiSuccess = true;
                }
                
                // Extract message ID and status link if available
                $messageId = $responseData['message_id'] ?? null;
                $statusLink = $responseData['message_status_link'] ?? null;
            }

            if ($apiSuccess) {
                Log::info('IPROG SMS: Message sent successfully', [
                    'phone' => $normalizedNumber,
                    'message_id' => $messageId,
                    'status_link' => $statusLink,
                    'api_message' => $responseData['message'] ?? null,
                ]);
                
                // Note: "queued for delivery" means SMS is accepted but may take time to deliver
                // Check delivery status if status link is provided
                if ($statusLink && $messageId) {
                    Log::info('IPROG SMS: Message queued for delivery. Check status at: ' . $statusLink);
                }
                
                return true;
            }

            // Check for phishing/suspicious URL error
            if (isset($responseData['message']) && is_array($responseData['message'])) {
                $errorMessages = $responseData['message'];
                $hasPhishingError = false;
                foreach ($errorMessages as $errorMsg) {
                    if (stripos($errorMsg, 'phishing') !== false || 
                        stripos($errorMsg, 'suspicious') !== false ||
                        stripos($errorMsg, 'url') !== false) {
                        $hasPhishingError = true;
                        break;
                    }
                }
                
                if ($hasPhishingError) {
                    Log::warning('IPROG SMS: Message blocked due to suspicious URL/phishing detection.', [
                        'error_messages' => $errorMessages,
                    ]);
                    // Note: Retry logic with alternative message formats would need to be implemented
                    // in sendTicketCreatedNotification method where ticket data is available
                }
            }

            // If form data failed, try JSON format
            if ($response->status() === 400 || $response->status() === 422) {
                Log::info('IPROG SMS: Form data failed, trying JSON format');
                
                $response = Http::timeout(30)
                    ->withoutVerifying()
                    ->asJson()
                    ->post($this->apiUrl, $requestData);

                $jsonBody = $response->body();
                $jsonData = json_decode($jsonBody, true);
                
                Log::info('IPROG SMS: JSON API Response', [
                    'http_status' => $response->status(),
                    'api_status' => $jsonData['status'] ?? null,
                    'api_message' => $jsonData['message'] ?? null,
                    'body' => $jsonBody,
                ]);

                $jsonSuccess = false;
                if ($response->successful()) {
                    if (isset($jsonData['status'])) {
                        $jsonSuccess = ($jsonData['status'] == 200 || $jsonData['status'] == 'success' || $jsonData['status'] === true);
                    } else {
                        $jsonSuccess = true;
                    }
                }

                if ($jsonSuccess) {
                    Log::info('IPROG SMS: Message sent successfully (JSON format)', [
                        'phone' => $normalizedNumber,
                    ]);
                    return true;
                }
            }

            Log::error('IPROG SMS: Failed to send message.', [
                'status' => $response->status(),
                'body' => $response->body(),
                'headers' => $response->headers(),
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('IPROG SMS: Connection error.', [
                'error' => $e->getMessage(),
                'url' => $this->apiUrl,
            ]);
        } catch (\Throwable $e) {
            Log::error('IPROG SMS: Exception while sending message.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return false;
    }

    /**
     * Normalize Philippine phone numbers to 63XXXXXXXXXX format.
     */
    protected function normalizePhoneNumber(?string $number): ?string
    {
        if (!$number) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $number);

        if (!$digits) {
            return null;
        }

        // Common PH formats we want to accept:
        //  - 09XXXXXXXXX
        //  - +639XXXXXXXXX / 639XXXXXXXXX
        //  - 9XXXXXXXXX
        //  - 00963XXXXXXXXX (international)

        if (Str::startsWith($digits, '09') && strlen($digits) >= 11) {
            $digits = '63' . substr($digits, 1);
        } elseif (Str::startsWith($digits, '9') && strlen($digits) === 10) {
            $digits = '63' . $digits;
        } elseif (Str::startsWith($digits, '00') && strlen($digits) > 2) {
            $digits = substr($digits, 2);
        }

        if (!Str::startsWith($digits, '63')) {
            $digits = '63' . $digits;
        }

        // Basic sanity check: PH mobile numbers should be 12 digits (63 + 10 digits)
        if (strlen($digits) < 12) {
            return null;
        }

        return $digits;
    }

    /**
     * Send DSS penalty notification based on violation count
     */
    public function sendDssPenaltyNotification(Ticket $ticket, int $violationCount, string $penaltyLevel, float $totalPendingFine = 0): bool
    {
        if (!$ticket->driver_contact) {
            Log::info('IPROG SMS: No driver contact number provided for DSS notification for ticket #' . ($ticket->citation_number ?? 'N/A'));
            return false;
        }

        $driverName = trim(($ticket->driver_firstname ?? '') . ' ' . ($ticket->driver_lastname ?? ''));
        $citationNumber = $ticket->citation_number ?? 'N/A';
        
        $message = $this->buildDssPenaltyMessage($driverName, $citationNumber, $violationCount, $penaltyLevel, $totalPendingFine);
        
        return $this->sendMessage($ticket->driver_contact, $message);
    }

    /**
     * Build DSS penalty message based on violation count and penalty level
     */
    protected function buildDssPenaltyMessage(string $driverName, string $citationNumber, int $violationCount, string $penaltyLevel, float $totalPendingFine): string
    {
        $baseMessage = "TOMECO DSS Alert - {$driverName}\n";
        $baseMessage .= "Citation: {$citationNumber}\n";
        $baseMessage .= "Unpaid Violations: {$violationCount}\n\n";

        switch ($violationCount) {
            case 2:
                // 2nd violation: Warning + increased fine
                $message = $baseMessage;
                $message .= "WARNING: This is your 2nd unpaid violation.\n";
                $message .= "Your fine has been increased due to repeat offenses.\n";
                $message .= "Total Pending Fine: PHP " . number_format($totalPendingFine, 2) . "\n\n";
                $message .= "Please pay all pending tickets immediately to avoid further penalties.";
                break;

            case 3:
                // 3rd violation: Temporary suspension + pay all pending
                $message = $baseMessage;
                $message .= "CRITICAL: This is your 3rd unpaid violation.\n";
                $message .= "Your Driver's License is TEMPORARILY SUSPENDED.\n\n";
                $message .= "Total Pending Fine: PHP " . number_format($totalPendingFine, 2) . "\n\n";
                $message .= "You MUST pay ALL pending tickets before your suspension can be lifted.\n";
                $message .= "Contact TOMECO immediately to resolve this matter.";
                break;

            case 4:
                // 4th violation: Extended suspension + legal action
                $message = $baseMessage;
                $message .= "SEVERE: This is your 4th unpaid violation.\n";
                $message .= "Your Driver's License suspension has been EXTENDED.\n";
                $message .= "LEGAL ACTION will be initiated against you.\n\n";
                $message .= "Total Pending Fine: PHP " . number_format($totalPendingFine, 2) . "\n\n";
                $message .= "You are required to pay all pending tickets and appear before the court.\n";
                $message .= "Contact TOMECO immediately to avoid further legal consequences.";
                break;

            default:
                // 5th violation and above: Permanent ban + legal proceedings
                $message = $baseMessage;
                $message .= "PERMANENT BAN: This is your {$violationCount}th unpaid violation.\n";
                $message .= "Your Driver's License is PERMANENTLY BANNED.\n";
                $message .= "LEGAL PROCEEDINGS will be initiated immediately.\n\n";
                $message .= "Total Pending Fine: PHP " . number_format($totalPendingFine, 2) . "\n\n";
                $message .= "You must pay all pending tickets and appear in court.\n";
                $message .= "Contact TOMECO and legal counsel immediately.";
                break;
        }

        return $message;
    }
}

