<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Services\IprogSmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    protected IprogSmsService $smsService;

    public function __construct(IprogSmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Store a new ticket (mobile API)
     */
    public function store(Request $request)
    {
        try {
            // Validate the incoming data
            $validatedData = $request->validate([
                'citation_number' => 'nullable|string|max:255',
                'issued_date' => 'nullable|date',
                'issued_time' => 'nullable|date_format:H:i',
                'issued_by' => 'nullable|string|max:255',
                'driver_lastname' => 'required|string|max:255',
                'driver_firstname' => 'required|string|max:255',
                'driver_middlename' => 'nullable|string|max:255',
                'driver_address' => 'nullable|string|max:255',
                'dl_number' => 'nullable|string|max:255',
                'dl_type' => 'nullable|string|max:255',
                'driver_contact' => 'nullable|string|max:255',
                'plate_number' => 'nullable|string|max:255',
                'cr_number' => 'nullable|string|max:255',
                'vehicle_year' => 'nullable|string|max:4',
                'vehicle_make' => 'nullable|string|max:255',
                'vehicle_model' => 'nullable|string|max:255',
                'vehicle_type' => 'nullable|string|max:255',
                'or_number' => 'nullable|string|max:255',
                'owner_name' => 'nullable|string|max:255',
                'owner_address' => 'nullable|string|max:255',
                'violations' => 'nullable|array',
                'violations.*' => 'string|max:255',
                'violations_others_text' => 'nullable|string|max:255',
                'place' => 'nullable|string|max:255',
                'accident' => 'nullable|boolean',
                'incident_notes' => 'nullable|string',
                'remarks' => 'nullable|string',
                'admitted_or_protest' => 'nullable|string|max:255',
                'court_date' => 'nullable|date',
                'court_time' => 'nullable|date_format:H:i',
                'apprehending_officer' => 'nullable|string|max:255',
                'tomeco_did' => 'nullable|string|max:255',
                'signature' => 'nullable|string',
                'driver_signature' => 'nullable|string',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:51200',
                'images' => 'nullable|array',
                'price' => 'nullable|numeric|min:0',
            ]);

            // Handle multiple image uploads
            if ($request->hasFile('images')) {
                $imagePaths = [];
                foreach ($request->file('images') as $image) {
                    $imagePath = $image->store('ticket-images', 'public');
                    $imagePaths[] = $imagePath;
                }
                $validatedData['images'] = $imagePaths;
            }

            // Handle base64 signatures
            if ($request->has('signature') && $request->signature) {
                $signaturePath = $this->saveBase64Image($request->signature, 'signatures');
                if ($signaturePath) {
                    $validatedData['signature'] = $signaturePath;
                }
            }

            if ($request->has('driver_signature') && $request->driver_signature) {
                $driverSignaturePath = $this->saveBase64Image($request->driver_signature, 'signatures');
                if ($driverSignaturePath) {
                    $validatedData['driver_signature'] = $driverSignaturePath;
                }
            }

            // Convert accident value
            if ($request->has('accident')) {
                $validatedData['accident'] = $request->input('accident') == '1' || $request->input('accident') === true || $request->input('accident') === 'true';
            }

            // Generate automatic citation number if not provided
            if (empty($validatedData['citation_number'])) {
                $validatedData['citation_number'] = $this->generateCitationNumber();
            }

            // Set default status to Unpaid
            $validatedData['status'] = 'Unpaid';

            // Calculate price from violations if not provided
            if (empty($validatedData['price']) || $validatedData['price'] == 0) {
                $validatedData['price'] = $this->calculatePriceFromViolations($validatedData['violations'] ?? []);
            }

            // Normalize officer names (trim whitespace) to ensure consistent matching
            if (isset($validatedData['apprehending_officer'])) {
                $validatedData['apprehending_officer'] = trim($validatedData['apprehending_officer']);
            }
            if (isset($validatedData['issued_by'])) {
                $validatedData['issued_by'] = trim($validatedData['issued_by']);
            }

            // Log ticket creation for debugging
            Log::info('Creating ticket', [
                'apprehending_officer' => $validatedData['apprehending_officer'] ?? null,
                'issued_by' => $validatedData['issued_by'] ?? null,
                'citation_number' => $validatedData['citation_number'] ?? null,
            ]);

            // Create the ticket
            $ticket = Ticket::create($validatedData);

            // Log ticket creation details for debugging (especially for synced tickets)
            Log::info('Ticket created', [
                'ticket_id' => $ticket->id,
                'citation_number' => $ticket->citation_number,
                'driver_contact' => $ticket->driver_contact,
                'driver_firstname' => $ticket->driver_firstname,
                'driver_lastname' => $ticket->driver_lastname,
                'has_driver_contact' => !empty($ticket->driver_contact),
                'driver_contact_length' => $ticket->driver_contact ? strlen($ticket->driver_contact) : 0,
                'source' => $request->header('User-Agent', 'unknown'), // Helps identify if from sync
                'request_data' => [
                    'has_driver_contact_in_request' => $request->has('driver_contact'),
                    'driver_contact_value' => $request->input('driver_contact'),
                ],
            ]);

            // Send SMS notification (best effort)
            try {
                // Check if driver contact exists before attempting SMS
                if (empty($ticket->driver_contact)) {
                    Log::warning('SMS skipped for ticket #' . $ticket->citation_number . ' - No driver contact number provided');
                } else {
                    $smsSent = $this->smsService->sendTicketCreatedNotification($ticket);
                    if ($smsSent) {
                        Log::info('SMS notification sent successfully for ticket #' . $ticket->citation_number, [
                            'driver_contact' => $ticket->driver_contact,
                        ]);
                    } else {
                        Log::warning('SMS notification failed for ticket #' . $ticket->citation_number . ' - ticket still created successfully', [
                            'driver_contact' => $ticket->driver_contact,
                        ]);
                    }
                }
            } catch (\Exception $smsException) {
                Log::error('SMS notification exception for ticket #' . $ticket->citation_number . ': ' . $smsException->getMessage(), [
                    'driver_contact' => $ticket->driver_contact,
                    'exception' => $smsException->getTraceAsString(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Traffic ticket created successfully!',
                'data' => $ticket,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to create ticket: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'There was a problem saving the ticket. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get list of tickets for the authenticated enforcer
     */
    public function index(Request $request)
    {
        try {
            $enforcer = $request->user();
            
            // Normalize the enforcer's fullname for comparison (trim and lowercase)
            $enforcerFullname = trim($enforcer->fullname ?? '');
            
            // Log for debugging
            Log::info('Fetching tickets for enforcer', [
                'enforcer_id' => $enforcer->id,
                'enforcer_fullname' => $enforcerFullname,
            ]);
            
            // Get tickets issued by this enforcer
            // Use case-insensitive comparison and handle null/empty values
            $tickets = Ticket::where(function($query) use ($enforcerFullname) {
                    $query->whereRaw('LOWER(TRIM(apprehending_officer)) = ?', [strtolower($enforcerFullname)])
                          ->orWhereRaw('LOWER(TRIM(issued_by)) = ?', [strtolower($enforcerFullname)]);
                })
                ->orderBy('created_at', 'desc')
                ->get();

            // Log results for debugging
            Log::info('Tickets found for enforcer', [
                'enforcer_fullname' => $enforcerFullname,
                'ticket_count' => $tickets->count(),
                'sample_ticket' => $tickets->first() ? [
                    'id' => $tickets->first()->id,
                    'apprehending_officer' => $tickets->first()->apprehending_officer,
                    'issued_by' => $tickets->first()->issued_by,
                ] : null,
            ]);

            // Format image URLs
            $tickets->transform(function ($ticket) {
                if ($ticket->images && is_array($ticket->images)) {
                    $ticket->image_urls = array_map(function($image) {
                        return url('storage/' . $image);
                    }, $ticket->images);
                }
                return $ticket;
            });

            return response()->json([
                'success' => true,
                'data' => $tickets,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch tickets: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tickets',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get a specific ticket
     */
    public function show(Request $request, $id)
    {
        try {
            $ticket = Ticket::findOrFail($id);
            
            // Format image URLs
            if ($ticket->images && is_array($ticket->images)) {
                $ticket->image_urls = array_map(function($image) {
                    return url('storage/' . $image);
                }, $ticket->images);
            }

            return response()->json([
                'success' => true,
                'data' => $ticket,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch ticket: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found',
            ], 404);
        }
    }

    /**
     * Save base64 image to storage
     */
    private function saveBase64Image($base64String, $folder = 'signatures')
    {
        try {
            // Remove data URL prefix if present
            if (strpos($base64String, ',') !== false) {
                $base64String = explode(',', $base64String)[1];
            }

            $imageData = base64_decode($base64String);
            if ($imageData === false) {
                return null;
            }

            $filename = $folder . '/' . uniqid() . '_' . time() . '.png';
            Storage::disk('public')->put($filename, $imageData);

            return $filename;
        } catch (\Exception $e) {
            Log::error('Failed to save base64 image: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate citation number
     */
    private function generateCitationNumber()
    {
        $year = date('Y');
        $lastTicket = Ticket::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastTicket && $lastTicket->citation_number) {
            // Extract number from citation (format: TOMECO-YYYY-XXXX)
            preg_match('/-(\d+)$/', $lastTicket->citation_number, $matches);
            $lastNumber = isset($matches[1]) ? (int)$matches[1] : 0;
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'TOMECO-' . $year . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate price from violations
     */
    private function calculatePriceFromViolations(array $violations)
    {
        // Default price per violation (can be customized)
        $pricePerViolation = 1.00;
        return count($violations) * $pricePerViolation;
    }
}

