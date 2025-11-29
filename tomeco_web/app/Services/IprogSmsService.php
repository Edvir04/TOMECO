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
    }

    /**
     * Send a ticket creation notification to the driver's phone number.
     */
    public function sendTicketCreatedNotification(Ticket $ticket): bool
    {
        if (!$ticket->driver_contact) {
            return false;
        }

        $issuedDate = $ticket->issued_date
            ? $ticket->issued_date->format('M d, Y')
            : now()->format('M d, Y');

        $message = sprintf(
            'Hi %s %s, ticket #%s was issued on %s. Please comply with TOMECO instructions.',
            trim($ticket->driver_firstname ?? ''),
            trim($ticket->driver_lastname ?? ''),
            $ticket->citation_number ?? 'N/A',
            $issuedDate
        );

        return $this->sendMessage($ticket->driver_contact, $message);
    }

    /**
     * Send a generic SMS message via IPROG SMS.
     */
    public function sendMessage(string $phoneNumber, string $message): bool
    {
        if (!$this->apiToken || !$this->apiUrl) {
            Log::warning('IPROG SMS: Missing API credentials.');
            return false;
        }

        $normalizedNumber = $this->normalizePhoneNumber($phoneNumber);
        if (!$normalizedNumber) {
            Log::warning('IPROG SMS: Invalid phone number provided.', ['phone_number' => $phoneNumber]);
            return false;
        }

        try {
            $response = Http::asForm()
                ->post($this->apiUrl, [
                    'api_token' => $this->apiToken,
                    'message' => $message,
                    'phone_number' => $normalizedNumber,
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('IPROG SMS: Failed to send message.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::error('IPROG SMS: Exception while sending message.', [
                'error' => $e->getMessage(),
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
}

