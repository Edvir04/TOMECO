<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Violation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\IprogSmsService;
use Illuminate\Support\Facades\Log; // Useful for debugging
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class TicketController extends Controller
{
    protected IprogSmsService $smsService;

    public function __construct(IprogSmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Convert stored signature value into a browser-friendly URL/data URL.
     * Supports already-absolute URLs or base64 data strings.
     */
    private function getSignatureUrl($value): ?string
    {
        if (!$value) {
            return null;
        }

        // Already a data URL or an absolute URL
        if (str_starts_with($value, 'data:') || filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        // Normalize storage path (handles values like "signatures/abc.png" or "/storage/signatures/abc.png")
        $normalized = ltrim($value, '/');
        // If it already includes "storage/", avoid duplicating
        if (str_starts_with($normalized, 'storage/')) {
            return asset($normalized);
        }

        return asset('storage/' . $normalized);
    }

    public function index()
    {
        // Calculate 3 business days ago (excluding weekends)
        $threeBusinessDaysAgo = Carbon::now();
        $businessDaysCount = 0;
        while ($businessDaysCount < 3) {
            $threeBusinessDaysAgo->subDay();
            // Skip weekends (Saturday = 6, Sunday = 0)
            if ($threeBusinessDaysAgo->dayOfWeek !== Carbon::SATURDAY && $threeBusinessDaysAgo->dayOfWeek !== Carbon::SUNDAY) {
                $businessDaysCount++;
            }
        }

        // Get tickets for court action (unpaid and older than 3 business days) - no pagination, show all
        // Exclude archived tickets
        $courtActionTickets = Ticket::where(function($query) {
                $query->where('status', 'Unpaid')
                      ->orWhereNull('status');
            })
            ->where('is_archived', false)
            ->where(function($query) use ($threeBusinessDaysAgo) {
                $query->where(function($q) use ($threeBusinessDaysAgo) {
                    // Use issued_date if available, otherwise fall back to created_at
                    $q->whereNotNull('issued_date')
                      ->whereDate('issued_date', '<=', $threeBusinessDaysAgo->format('Y-m-d'));
                })->orWhere(function($q) use ($threeBusinessDaysAgo) {
                    $q->whereNull('issued_date')
                      ->whereDate('created_at', '<=', $threeBusinessDaysAgo->format('Y-m-d'));
                });
            })
            ->orderByRaw('COALESCE(issued_date, created_at) DESC')
            ->get();

        // Get court action ticket IDs to exclude from main unpaid tickets table
        $courtActionTicketIds = Ticket::where(function($query) {
                $query->where('status', 'Unpaid')
                      ->orWhereNull('status');
            })
            ->where('is_archived', false)
            ->where(function($query) use ($threeBusinessDaysAgo) {
                $query->where(function($q) use ($threeBusinessDaysAgo) {
                    $q->whereNotNull('issued_date')
                      ->whereDate('issued_date', '<=', $threeBusinessDaysAgo->format('Y-m-d'));
                })->orWhere(function($q) use ($threeBusinessDaysAgo) {
                    $q->whereNull('issued_date')
                      ->whereDate('created_at', '<=', $threeBusinessDaysAgo->format('Y-m-d'));
                });
            })
            ->pluck('id');

        // Paginate unpaid tickets for the main table (excluding court action tickets and archived tickets)
        $unpaidTickets = Ticket::where(function($query) {
                $query->where('status', 'Unpaid')
                      ->orWhereNull('status');
            })
            ->where('is_archived', false)
            ->whereNotIn('id', $courtActionTicketIds)
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'unpaid_page');
        
        // Automatically archive tickets that have been paid for 10+ days
        $tenDaysAgo = Carbon::now()->subDays(10);
        Ticket::where('status', 'Paid')
            ->where('is_archived', false)
            ->whereNotNull('paid_at')
            ->where('paid_at', '<=', $tenDaysAgo)
            ->update(['is_archived' => true]);

        // Get all paid tickets for the paid tickets modal (excluding archived tickets, no pagination)
        $paidTickets = Ticket::where('status', 'Paid')
            ->where('is_archived', false)
            ->orderBy('created_at', 'desc')
            ->get();

        // Paginate archived tickets for display on main page
        $archivedTickets = Ticket::where('is_archived', true)
            ->orderBy('paid_at', 'desc')
            ->paginate(10, ['*'], 'archived_page');

        // Get all active violations ordered by newest first
        $violations = Violation::where('is_active', true)
            ->orWhereNull('is_active')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('layout.ticket-issuance', [
            'tickets' => $unpaidTickets,
            'paidTickets' => $paidTickets,
            'courtActionTickets' => $courtActionTickets,
            'archivedTickets' => $archivedTickets,
            'violations' => $violations
        ]);
    }
    public function store(Request $request)
    {
        // 1. Validate the incoming data
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
            'driver_contact' => 'nullable|string|max:255',
            'plate_number' => 'nullable|string|max:255',
            'cr_number' => 'nullable|string|max:255',
            'vehicle_year' => 'nullable|string|max:4',
            'vehicle_make' => 'nullable|string|max:255',
            'vehicle_model' => 'nullable|string|max:255',
            'owner_name' => 'nullable|string|max:255',
            'owner_address' => 'nullable|string|max:255',
            'violations' => 'nullable|array', // Ensures 'violations' is an array
            'violations.*' => 'string|max:255', // Ensures each item in the array is a string
            'violations_others_text' => 'nullable|string|max:255',
            'place' => 'nullable|string|max:255',
            'incident_notes' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'court_date' => 'nullable|date',
            'court_time' => 'nullable|date_format:H:i',
            'apprehending_officer' => 'nullable|string|max:255',
            'tomeco_did' => 'nullable|string|max:255',
            'signature' => 'nullable|string',
            'driver_signature' => 'nullable|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:51200', // 50MB max per image
            'images' => 'nullable|array',
            // Status is automated, not included in form validation
        ]);

        try {
            // 2. Handle multiple image uploads
            if ($request->hasFile('images')) {
                $imagePaths = [];
                foreach ($request->file('images') as $image) {
                    $imagePath = $image->store('ticket-images', 'public');
                    $imagePaths[] = $imagePath;
                }
                $validatedData['images'] = $imagePaths;
            }
            
            // Convert accident radio button value to boolean
            if ($request->has('accident')) {
                $validatedData['accident'] = $request->input('accident') == '1' ? true : false;
            }

            // Generate automatic citation number if not provided
            if (empty($validatedData['citation_number'])) {
                $validatedData['citation_number'] = $this->generateCitationNumber();
            }

            // Set default status to Unpaid (automated)
            $validatedData['status'] = 'Unpaid';
            
            // Calculate price from violations (price is sent from frontend, calculated based on selected violations)
            // If price is not provided, calculate it from violations
            if (empty($validatedData['price']) || $validatedData['price'] == 0) {
                $validatedData['price'] = $this->calculatePriceFromViolations($validatedData['violations'] ?? []);
            }

            // 3. Create the ticket
            $ticket = Ticket::create($validatedData);

            // Apply DSS logic for repeat offenders (only for unpaid tickets)
            $this->applyDssPenalties($ticket);

            // Send SMS notification (best effort - don't fail ticket creation if SMS fails)
            try {
                $smsSent = $this->smsService->sendTicketCreatedNotification($ticket);
                if ($smsSent) {
                    Log::info('SMS notification sent successfully for ticket #' . $ticket->citation_number);
                } else {
                    Log::warning('SMS notification failed for ticket #' . $ticket->citation_number . ' - ticket still created successfully');
                }
            } catch (\Exception $smsException) {
                // Don't fail ticket creation if SMS fails
                Log::error('SMS notification exception for ticket #' . $ticket->citation_number . ': ' . $smsException->getMessage());
            }

            // 3. Redirect back with a success message
            return redirect()->back()->with('success', 'Traffic ticket created successfully!');

        } catch (\Exception $e) {
            // Optional: Log the error for debugging
            Log::error('Failed to create ticket: ' . $e->getMessage());

            // 4. Redirect back with an error message
            return redirect()->back()->with('error', 'There was a problem saving the ticket. Please try again.');
        }
    }

    public function show($id)
    {
        try {
            $ticket = Ticket::findOrFail($id);
            
            // Format time fields to H:i format (remove seconds if present)
            if ($ticket->issued_time) {
                $ticket->issued_time = substr($ticket->issued_time, 0, 5);
            }
            if ($ticket->court_time) {
                $ticket->court_time = substr($ticket->court_time, 0, 5);
            }
            
            // Get full URLs for images if they exist
            if ($ticket->images && is_array($ticket->images)) {
                $ticket->image_urls = array_map(function($image) {
                    return asset('storage/' . $image);
                }, $ticket->images);
            } elseif ($ticket->image) {
                // Fallback for old single image format
                $ticket->image_urls = [asset('storage/' . $ticket->image)];
            }

            // Normalize signature URLs for browser display
            $ticket->signature = $this->getSignatureUrl($ticket->signature);
            $ticket->driver_signature = $this->getSignatureUrl($ticket->driver_signature);
            
            return response()->json($ticket);
        } catch (\Exception $e) {
            Log::error('Failed to fetch ticket: ' . $e->getMessage());
            return response()->json(['error' => 'Ticket not found'], 404);
        }
    }

    public function print($id)
    {
        try {
            $ticket = Ticket::findOrFail($id);
            
            // Format time fields to H:i format (remove seconds if present)
            if ($ticket->issued_time) {
                $ticket->issued_time = substr($ticket->issued_time, 0, 5);
            }
            if ($ticket->court_time) {
                $ticket->court_time = substr($ticket->court_time, 0, 5);
            }
            
            // Get full URLs for images if they exist
            if ($ticket->images && is_array($ticket->images)) {
                $ticket->image_urls = array_map(function($image) {
                    return asset('storage/' . $image);
                }, $ticket->images);
            } elseif ($ticket->image) {
                // Fallback for old single image format
                $ticket->image_urls = [asset('storage/' . $ticket->image)];
            }

            // Normalize signature URLs for print view
            $ticket->signature = $this->getSignatureUrl($ticket->signature);
            $ticket->driver_signature = $this->getSignatureUrl($ticket->driver_signature);
            
            return view('layout.ticket-print', ['ticket' => $ticket]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch ticket for print: ' . $e->getMessage());
            return redirect()->route('admin.ticket-issuance')->with('error', 'Ticket not found');
        }
    }

    public function update(Request $request, $id)
    {
        // 1. Validate the incoming data
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
            'driver_contact' => 'nullable|string|max:255',
            'plate_number' => 'nullable|string|max:255',
            'cr_number' => 'nullable|string|max:255',
            'vehicle_year' => 'nullable|string|max:4',
            'vehicle_make' => 'nullable|string|max:255',
            'vehicle_model' => 'nullable|string|max:255',
            'owner_name' => 'nullable|string|max:255',
            'owner_address' => 'nullable|string|max:255',
            'violations' => 'nullable|array',
            'violations.*' => 'string|max:255',
            'violations_others_text' => 'nullable|string|max:255',
            'place' => 'nullable|string|max:255',
            'incident_notes' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'court_date' => 'nullable|date',
            'court_time' => 'nullable|date_format:H:i',
            'apprehending_officer' => 'nullable|string|max:255',
            'tomeco_did' => 'nullable|string|max:255',
            'signature' => 'nullable|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:51200', // 50MB max per image
            'images' => 'nullable|array',
            'vehicle_type' => 'nullable|string|max:255',
            'or_number' => 'nullable|string|max:255',
            'dl_type' => 'nullable|string|max:255',
            'accident' => 'nullable|boolean',
            'admitted_or_protest' => 'nullable|string|max:255',
            'driver_signature' => 'nullable|string',
            // Status is automated, not included in form validation
        ]);

        try {
            $ticket = Ticket::findOrFail($id);
            
            // Convert accident radio button value to boolean
            if ($request->has('accident')) {
                $validatedData['accident'] = $request->input('accident') == '1' ? true : false;
            }
            
            // Handle multiple image uploads - append new images to existing ones
            // Get existing images that should be kept (from hidden input)
            $existingImagesToKeep = [];
            if ($request->has('existing_images')) {
                $existingImagesJson = $request->input('existing_images');
                if ($existingImagesJson) {
                    $existingImagesToKeep = json_decode($existingImagesJson, true) ?? [];
                }
            }
            
            // If no existing images specified to keep, use all existing images
            if (empty($existingImagesToKeep)) {
                $existingImagesToKeep = $ticket->images ?? [];
            }
            
            // Delete images that were removed (exist in DB but not in existingImagesToKeep)
            $allExistingImages = $ticket->images ?? [];
            foreach ($allExistingImages as $oldImage) {
                if (!in_array($oldImage, $existingImagesToKeep) && $oldImage && Storage::disk('public')->exists($oldImage)) {
                    Storage::disk('public')->delete($oldImage);
                }
            }
            
            // Upload new images
            $newImagePaths = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePath = $image->store('ticket-images', 'public');
                    $newImagePaths[] = $imagePath;
                }
            }
            
            // Merge kept existing images with new images
            $validatedData['images'] = array_merge($existingImagesToKeep, $newImagePaths);
            
            $ticket->update($validatedData);

            return redirect()->back()->with('success', 'Traffic ticket updated successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to update ticket: ' . $e->getMessage());
            return redirect()->back()->with('error', 'There was a problem updating the ticket. Please try again.');
        }
    }

    public function destroy($id)
    {
        try {
            $ticket = Ticket::findOrFail($id);
            $ticket->delete();

            return redirect()->back()->with('success', 'Traffic ticket deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to delete ticket: ' . $e->getMessage());
            return redirect()->back()->with('error', 'There was a problem deleting the ticket. Please try again.');
        }
    }

    /**
     * Show the violator portal page
     */
    public function violatorPortal()
    {
        return view('violator-portal');
    }

    /**
     * Search for ticket by citation number (for violator portal)
     */
    public function searchByCitation(Request $request)
    {
        $request->validate([
            'citation_number' => 'required|string|max:255',
        ]);

        try {
            $citationNumber = $request->input('citation_number');
            $ticket = Ticket::where('citation_number', $citationNumber)->first();

            if ($ticket) {
                // Get full URL for image if exists
                // Get full URLs for images if they exist
                if ($ticket->images && is_array($ticket->images)) {
                    $ticket->image_urls = array_map(function($image) {
                        return asset('storage/' . $image);
                    }, $ticket->images);
                } elseif ($ticket->image) {
                    // Fallback for old single image format
                    $ticket->image_urls = [asset('storage/' . $ticket->image)];
                }
                
                // Get full URL for signature if exists
                if ($ticket->signature) {
                    // If signature is a base64 string, keep it as is
                    // If it's a file path, convert to URL
                    if (!str_starts_with($ticket->signature, 'data:')) {
                        $ticket->signature_url = asset('storage/' . $ticket->signature);
                    } else {
                        $ticket->signature_url = $ticket->signature;
                    }
                }
                
                // Get full URL for driver signature if exists
                if ($ticket->driver_signature) {
                    // If driver signature is a base64 string, keep it as is
                    // If it's a file path, convert to URL
                    if (!str_starts_with($ticket->driver_signature, 'data:')) {
                        $ticket->driver_signature_url = asset('storage/' . $ticket->driver_signature);
                    } else {
                        $ticket->driver_signature_url = $ticket->driver_signature;
                    }
                }

                return view('violator-portal', [
                    'ticket' => $ticket,
                    'citation_number' => $citationNumber
                ]);
            } else {
                return redirect()->route('violator.portal')
                    ->with('error', 'No ticket found with citation number: ' . $citationNumber)
                    ->withInput();
            }
        } catch (\Exception $e) {
            Log::error('Failed to search ticket: ' . $e->getMessage());
            return redirect()->route('violator.portal')
                ->with('error', 'There was a problem searching for the ticket. Please try again.')
                ->withInput();
        }
    }

    /**
     * Generate automatic citation number
     * Format: YYYY-NNNNNN (Year + 6-digit sequential number)
     * Example: 2025-000001, 2025-000002, etc.
     */
    private function generateCitationNumber(): string
    {
        $year = date('Y');
        
        // Get the last citation number for this year
        $lastTicket = Ticket::where('citation_number', 'like', $year . '-%')
            ->orderBy('citation_number', 'desc')
            ->first();
        
        if ($lastTicket && $lastTicket->citation_number) {
            // Extract the sequential number from the last citation
            $parts = explode('-', $lastTicket->citation_number);
            if (count($parts) === 2 && $parts[0] === $year) {
                $lastNumber = (int) $parts[1];
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }
        } else {
            // First ticket for this year
            $nextNumber = 1;
        }
        
        // Format as YYYY-NNNNNN (6 digits)
        return $year . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate total price from violations
     * Each violation has a price (currently all set to 1.00 for testing)
     */
    private function calculatePriceFromViolations(array $violations): float
    {
        if (empty($violations)) {
            return 500.00; // Default minimum price
        }

        // Get violation prices from database
        $violationModels = Violation::whereIn('name', $violations)->get();
        $violationPrices = [];
        foreach ($violationModels as $violation) {
            $violationPrices[$violation->name] = floatval($violation->price);
        }

        // Fallback hardcoded prices for violations not in database (for backward compatibility)
        $fallbackPrices = [
            // Additional violations from checklist (shown first)
            'Driving without D/L' => 500.00,
            'Unregistered Vehicle' => 500.00,
            'Illegal Parking' => 500.00,
            'Disregarding Traffic Sign' => 500.00,
            'Obstruction' => 500.00,
            'Truck Ban' => 500.00,
            'Operating Along National Highway' => 500.00,
            'No Helmet' => 500.00,
            'Defective Head Light' => 500.00,
            'Violation to CO # 2007-10-31 "The Anti-Littering Ordinance"' => 500.00,
            'Violation to CO # 2009-10-160 "The Anti-Smoking Ordinance."' => 500.00,
            'Violation to CO # 2007-10-66 "The anti-urinating and Defecating Ordinance."' => 500.00,
            // Section violations (Sec. 1-73)
            'Sec. 1: Failure to give right of way to Police and other emergency vehicles giving audible signals.' => 500.00,
            'Sec. 2: Allowing passengers to ride on board or, hitch to one\'s vehicle.' => 500.00,
            'Sec. 3: Driving or parking on a side walk.' => 500.00,
            'Sec. 4: Obscure or dirty plate number.' => 500.00,
            'Sec. 5: Defective headlights, taillights, stop lights, wiper and other accessories.' => 500.00,
            'Sec. 6: Failure to give the necessary signal when starting or stopping.' => 500.00,
            'Sec. 7: Illegal Parking.' => 500.00,
            'Sec. 8: Failure to carry the official receipt of registration of the current year.' => 500.00,
            'Sec. 9: Operating an unsafe, unsightly or dilapidated vehicle.' => 500.00,
            'Sec. 10: Unauthorized use of improvised plates.' => 500.00,
            'Sec. 11: Driving a vehicle with passengers in excess of capacity.' => 500.00,
            'Sec. 12: Driving a vehicle with horn that emits exceptionally loud and startling or disagreeable sound.' => 500.00,
            'Sec. 13: Driving a vehicle with a defective brake system.' => 500.00,
            'Sec. 14: Driving a freight or cargo vehicle loaded in excess of authorized capacity.' => 500.00,
            'Sec. 15: Driving vehicle recklessly.' => 500.00,
            'Sec. 16: Obstructing or impeding the passage of other vehicles, loading and unloading of passengers at intersections or within prohibited areas.' => 500.00,
            'Sec. 17: Driving with unsigned license.' => 500.00,
            'Sec. 18: Driving with invalid or delinquent driver license.' => 500.00,
            'Sec. 19: Driving a vehicle with a delinquent suspended or invalid registration or without the proper license plate for the current year of registration.' => 500.00,
            'Sec. 20: Driving without first securing a driver\'s license.' => 500.00,
            'Sec. 21: Driving without carrying a driver\'s license with him.' => 500.00,
            'Sec. 22: Using or attempting to use a fake license, identification card, registration, certificate, vehicle plate number, and tag or sticker.' => 500.00,
            'Sec. 23: Falsely or fraudulently representing as valid and enforced a delinquent suspended or revoked license.' => 500.00,
            'Sec. 24: Using a vehicle registered for private use as that for hire or allowing another person to use the driver\'s license of the authorized or real driver of the vehicle.' => 500.00,
            'Sec. 25: Cutting corners of blind curbs.' => 500.00,
            'Sec. 26: Making U-Turn on the approach or on top of the bridge or elsewhere but not at intersection.' => 500.00,
            'Sec. 27: Overtaking or passing on curb, at intersection and approaches of bridge, bill and along places where overtaking is prohibited.' => 500.00,
            'Sec. 28: Coming out of Side Street or driveways without precautions.' => 500.00,
            'Sec. 29: Vehicle racing on roads or streets.' => 500.00,
            'Sec. 30: Failure to stop on entering a thru-street.' => 500.00,
            'Sec. 31: Failure to consider proper clearance when overtaking.' => 500.00,
            'Sec. 32: Failure to observe the right-hand rule to yield the right-of-way at highway intersection.' => 500.00,
            'Sec. 33: Driving on a wrong side of the street.' => 500.00,
            'Sec. 34: Backing against the flow of traffic.' => 500.00,
            'Sec. 35: Turning from wrong lane.' => 500.00,
            'Sec. 36: Driving without lights during the hours prescribed by law.' => 500.00,
            'Sec. 37: Driving or crossing the safety island not intended for motor vehicle.' => 500.00,
            'Sec. 38: Disregarding automatic signaling devices, lights or any traffic signal, sign or makings.' => 500.00,
            'Sec. 39: Failure to stop or slow down on crosswalk or pedestrian lanes with or without pedestrians crossing.' => 500.00,
            'Sec. 40: Over-speeding or fast driving.' => 500.00,
            'Sec. 41: Failure to slow down on school zones, hospital zones, churches, courtrooms and the likes.' => 500.00,
            'Sec. 42: Entering a "DO NOT ENTER" sign.' => 500.00,
            'Sec. 43: Disregarding a "NO LEFT TURN" sign.' => 500.00,
            'Sec. 44: Passing a "THRU-RED LIGHT".' => 500.00,
            'Sec. 45: Allowing passengers in excess of the capacity of the front seat.' => 500.00,
            'Sec. 46: Loading or unloading passengers within the prohibited zone.' => 500.00,
            'Sec. 47: Soliciting passengers at street corners.' => 500.00,
            'Sec. 48: Loading and unloading passenger in the middle of the road.' => 500.00,
            'Sec. 49: Loading and unloading passenger at intersection.' => 500.00,
            'Sec. 50: Parking a vehicle or permit the same to stand attended or unattended upon a highway.' => 500.00,
            'Sec. 51: Driving a vehicle with open muffler or making unnecessary noise.' => 500.00,
            'Sec. 52: Failure to display a red flag or red light at the rear end of the load which extends beyond the projected length of the vehicle.' => 500.00,
            'Sec. 53: Driving a vehicle emitting excessive smoke.' => 500.00,
            'Sec. 54: Driving along a highway without proper permit for motor vehicles with metallic tires.' => 500.00,
            'Sec. 55: Operating a service vehicle without a commercial or trade name and the words "NOT FOR HIRE" painted in both sides of the motor vehicle.' => 500.00,
            'Sec. 56: Driving a motor truck without capacity marking plainly lettered on both sides of the motor vehicle.' => 500.00,
            'Sec. 57: Driving a vehicle with a broken windshield.' => 500.00,
            'Sec. 58: Driving a motor vehicle with a red light or halogen lamp forward or overhead of the same.' => 500.00,
            'Sec. 59: Driving with inappropriate driver\'s license or conductor\'s license.' => 500.00,
            'Sec. 60: Refusal to show or surrender the driver\'s license and/or conductor\'s license.' => 500.00,
            'Sec. 61: Operating a vehicle loaded with soil, sand, gravel, stones and the likes without canvass covering.' => 500.00,
            'Sec. 62: Operating a motor vehicle equipped with an unauthorized siren.' => 500.00,
            'Sec. 63: Driving while under the influence of liquor or narcotics drugs.' => 500.00,
            'Sec. 64: Failure to carry the conductor\'s license.' => 500.00,
            'Sec. 65: Serving as conductor without first securing a conductor\'s permit.' => 500.00,
            'Sec. 66: Carrying freight or cargo in excess of the registered net carrying capacity.' => 500.00,
            'Sec. 67: Hostile or arrogant attitude of a driver or conductor towards a lawful Authority or improper conduct or behavior like bribery and other similar offenses tending to corrupt a police officer including discourteous to passenger.' => 500.00,
            'Sec. 68: Transferring, lending or otherwise allowing any person to use his driver\'s license for the purpose of enabling such person to operate a motor vehicle.' => 500.00,
            'Sec. 69: Engaging, Employing or hiring any person to operate a motor vehicle other than a duly license professional driver.' => 500.00,
            'Sec. 70: Operating in a prohibited route.' => 500.00,
            'Sec. 71: Constructing structures, edifices or stand that may obstruct the free passage of pedestrians with the side walk.' => 500.00,
            'Sec. 72: Refusal to convey passenger or having agreed to convey the same, negligently, culpably or unreasonably failed to convey said passenger to his place or destination.' => 500.00,
            'Sec. 73: To demand and collect a fare more than the existing rate as authorized by law, rules and regulations.' => 500.00,
        ];

        // Merge database prices with fallback prices (database prices take precedence)
        $allPrices = array_merge($fallbackPrices, $violationPrices);

        $totalPrice = 0;
        foreach ($violations as $violation) {
            $totalPrice += $allPrices[$violation] ?? 500.00; // Default to 500.00 if violation not found
        }

        return $totalPrice > 0 ? $totalPrice : 500.00; // Minimum 500.00 peso
    }

    /**
     * Update court action status for a ticket
     */
    public function updateCourtActionStatus(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        
        $validated = $request->validate([
            'court_action_status' => 'required|in:Pending,Processing,Completed'
        ]);
        
        $ticket->court_action_status = $validated['court_action_status'];
        $ticket->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Court action status updated successfully'
        ]);
    }

    /**
     * Archive a ticket manually
     */
    public function archive($id)
    {
        try {
            $ticket = Ticket::findOrFail($id);
            
            // Only archive paid tickets
            if ($ticket->status !== 'Paid') {
                return redirect()->back()->with('error', 'Only paid tickets can be archived.');
            }
            
            $ticket->is_archived = true;
            $ticket->save();
            
            return redirect()->back()->with('success', 'Ticket archived successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to archive ticket: ' . $e->getMessage());
            return redirect()->back()->with('error', 'There was a problem archiving the ticket. Please try again.');
        }
    }

    /**
     * Unarchive a ticket
     */
    public function unarchive($id)
    {
        try {
            $ticket = Ticket::findOrFail($id);
            $ticket->is_archived = false;
            $ticket->save();
            
            return redirect()->back()->with('success', 'Ticket unarchived successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to unarchive ticket: ' . $e->getMessage());
            return redirect()->back()->with('error', 'There was a problem unarchiving the ticket. Please try again.');
        }
    }

    /**
     * Automatically check and archive tickets that have been paid for 10+ days
     * This endpoint can be called via AJAX to ensure tickets are archived when viewing paid tickets
     */
    public function autoArchiveTickets()
    {
        try {
            $tenDaysAgo = Carbon::now()->subDays(10);
            $archivedCount = Ticket::where('status', 'Paid')
                ->where('is_archived', false)
                ->whereNotNull('paid_at')
                ->where('paid_at', '<=', $tenDaysAgo)
                ->update(['is_archived' => true]);
            
            return response()->json([
                'success' => true,
                'message' => "Archived {$archivedCount} ticket(s) automatically.",
                'archived_count' => $archivedCount
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to auto-archive tickets: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to archive tickets automatically.'
            ], 500);
        }
    }

    /**
     * Display penalty recommendation page (DSS) - shows violators with multiple tickets
     */
    public function penaltyRecommendation()
    {
        // Recalculate DSS penalties for all existing unpaid tickets
        $this->recalculateAllDssPenalties();
        
        // Get all tickets (excluding archived)
        $allTickets = Ticket::where('is_archived', false)->get();
        
        // Group violators by DL number (primary) or by name combination (fallback)
        $violatorGroups = [];
        
        foreach ($allTickets as $ticket) {
            // Create a unique identifier for the violator
            $violatorKey = null;
            
            // Primary: Use DL number if available
            if (!empty($ticket->dl_number)) {
                $violatorKey = 'dl_' . strtolower(trim($ticket->dl_number));
            } else {
                // Fallback: Use name combination
                $nameParts = array_filter([
                    trim($ticket->driver_firstname ?? ''),
                    trim($ticket->driver_middlename ?? ''),
                    trim($ticket->driver_lastname ?? '')
                ]);
                if (!empty($nameParts)) {
                    $violatorKey = 'name_' . strtolower(implode('_', $nameParts));
                }
            }
            
            // Skip if we can't identify the violator
            if (!$violatorKey) {
                continue;
            }
            
            // Initialize violator group if not exists
            if (!isset($violatorGroups[$violatorKey])) {
                $violatorGroups[$violatorKey] = [
                    'identifier' => $violatorKey,
                    'dl_number' => $ticket->dl_number,
                    'driver_firstname' => $ticket->driver_firstname,
                    'driver_middlename' => $ticket->driver_middlename,
                    'driver_lastname' => $ticket->driver_lastname,
                    'driver_address' => $ticket->driver_address,
                    'driver_contact' => $ticket->driver_contact,
                    'tickets' => [],
                    'ticket_count' => 0,
                    'total_fine' => 0,
                    'unpaid_count' => 0,
                    'paid_count' => 0,
                    'dss_penalty_level' => null,
                    'dss_unpaid_violation_count' => 0,
                    'dss_total_penalty_increase' => 0,
                ];
            }
            
            // Add ticket to violator group
            $violatorGroups[$violatorKey]['tickets'][] = $ticket;
            $violatorGroups[$violatorKey]['ticket_count']++;
            $violatorGroups[$violatorKey]['total_fine'] += floatval($ticket->price ?? 0);
            
            if ($ticket->status === 'Unpaid' || empty($ticket->status)) {
                $violatorGroups[$violatorKey]['unpaid_count']++;
                
                // Track DSS information from unpaid tickets
                if ($ticket->unpaid_violation_count > $violatorGroups[$violatorKey]['dss_unpaid_violation_count']) {
                    $violatorGroups[$violatorKey]['dss_unpaid_violation_count'] = $ticket->unpaid_violation_count;
                }
                if ($ticket->dss_penalty_level) {
                    $violatorGroups[$violatorKey]['dss_penalty_level'] = $ticket->dss_penalty_level;
                }
                $violatorGroups[$violatorKey]['dss_total_penalty_increase'] += floatval($ticket->dss_penalty_fine_increase ?? 0);
            } else {
                $violatorGroups[$violatorKey]['paid_count']++;
            }
        }
        
        // Filter to only show violators with 2+ unpaid tickets (DSS applies only to unpaid)
        $violatorsWithMultipleTickets = array_filter($violatorGroups, function($group) {
            return $group['unpaid_count'] >= 2;
        });
        
        // Sort by ticket count (descending)
        usort($violatorsWithMultipleTickets, function($a, $b) {
            return $b['ticket_count'] - $a['ticket_count'];
        });
        
        return view('layout.Penalty', [
            'violators' => $violatorsWithMultipleTickets
        ]);
    }

    /**
     * Get violator identifier (DL number or name combination)
     */
    private function getViolatorIdentifier(Ticket $ticket): ?string
    {
        // Primary: Use DL number if available
        if (!empty($ticket->dl_number)) {
            return 'dl_' . strtolower(trim($ticket->dl_number));
        }
        
        // Fallback: Use name combination
        $nameParts = array_filter([
            trim($ticket->driver_firstname ?? ''),
            trim($ticket->driver_middlename ?? ''),
            trim($ticket->driver_lastname ?? '')
        ]);
        
        if (!empty($nameParts)) {
            return 'name_' . strtolower(implode('_', $nameParts));
        }
        
        return null;
    }

    /**
     * Count unpaid violations for a violator
     */
    private function countUnpaidViolations(string $violatorIdentifier, ?int $excludeTicketId = null): int
    {
        $query = Ticket::where('is_archived', false)
            ->where(function($q) {
                $q->where('status', 'Unpaid')
                  ->orWhereNull('status');
            });

        // Identify by DL number or name
        if (str_starts_with($violatorIdentifier, 'dl_')) {
            $dlNumber = substr($violatorIdentifier, 3);
            $query->where('dl_number', $dlNumber);
        } else {
            // Name-based identification
            $nameParts = explode('_', substr($violatorIdentifier, 5));
            if (count($nameParts) >= 2) {
                $query->where('driver_firstname', $nameParts[0])
                      ->where('driver_lastname', end($nameParts));
                if (count($nameParts) === 3) {
                    $query->where('driver_middlename', $nameParts[1]);
                }
            }
        }

        if ($excludeTicketId) {
            $query->where('id', '!=', $excludeTicketId);
        }

        return $query->count();
    }

    /**
     * Apply DSS penalties based on unpaid violation count
     */
    private function applyDssPenalties(Ticket $ticket): void
    {
        try {
            $violatorIdentifier = $this->getViolatorIdentifier($ticket);
            
            if (!$violatorIdentifier) {
                Log::warning('DSS: Cannot identify violator for ticket #' . ($ticket->citation_number ?? 'N/A'));
                return;
            }

            // Count unpaid violations (excluding current ticket)
            $unpaidCount = $this->countUnpaidViolations($violatorIdentifier, $ticket->id);
            
            // Add current ticket to count (it's unpaid)
            $totalUnpaidCount = $unpaidCount + 1;

            // Calculate total pending fine
            $totalPendingFine = Ticket::where('is_archived', false)
                ->where(function($q) {
                    $q->where('status', 'Unpaid')
                      ->orWhereNull('status');
                })
                ->where(function($query) use ($violatorIdentifier) {
                    if (str_starts_with($violatorIdentifier, 'dl_')) {
                        $dlNumber = substr($violatorIdentifier, 3);
                        $query->where('dl_number', $dlNumber);
                    } else {
                        $nameParts = explode('_', substr($violatorIdentifier, 5));
                        if (count($nameParts) >= 2) {
                            $query->where('driver_firstname', $nameParts[0])
                                  ->where('driver_lastname', end($nameParts));
                            if (count($nameParts) === 3) {
                                $query->where('driver_middlename', $nameParts[1]);
                            }
                        }
                    }
                })
                ->sum('price');

            // Add current ticket price
            $totalPendingFine += floatval($ticket->price ?? 0);

            // Determine penalty level and apply penalties
            $penaltyLevel = null;
            $fineIncrease = 0;
            $notes = [];

            if ($totalUnpaidCount >= 2) {
                // 2nd violation: Warning + increased fine (20% increase)
                if ($totalUnpaidCount == 2) {
                    $penaltyLevel = 'warning';
                    $fineIncrease = $ticket->price * 0.20; // 20% increase
                    $notes[] = '2nd violation: Warning issued. Fine increased by 20%.';
                }
                // 3rd violation: Temporary suspension
                elseif ($totalUnpaidCount == 3) {
                    $penaltyLevel = 'suspension_temp';
                    $fineIncrease = $ticket->price * 0.30; // 30% increase
                    $notes[] = '3rd violation: Temporary DL suspension. Must pay all pending tickets before suspension lifted.';
                }
                // 4th violation: Extended suspension + legal action
                elseif ($totalUnpaidCount == 4) {
                    $penaltyLevel = 'suspension_extended';
                    $fineIncrease = $ticket->price * 0.50; // 50% increase
                    $notes[] = '4th violation: Extended DL suspension. Legal action initiated.';
                }
                // 5th+ violation: Permanent ban + legal proceedings
                else {
                    $penaltyLevel = 'permanent_ban';
                    $fineIncrease = $ticket->price * 1.00; // 100% increase (double fine)
                    $notes[] = "{$totalUnpaidCount}th violation: Permanent DL ban. Legal proceedings initiated.";
                }

                // Update ticket with DSS information
                $ticket->unpaid_violation_count = $totalUnpaidCount;
                $ticket->dss_penalty_level = $penaltyLevel;
                $ticket->dss_penalty_applied_at = Carbon::now();
                $ticket->dss_penalty_fine_increase = $fineIncrease;
                $ticket->dss_notes = implode(' ', $notes);
                
                // Update ticket price with penalty increase
                $ticket->price = floatval($ticket->price) + $fineIncrease;
                $ticket->save();

                // Send DSS penalty SMS notification
                try {
                    $smsSent = $this->smsService->sendDssPenaltyNotification(
                        $ticket,
                        $totalUnpaidCount,
                        $penaltyLevel,
                        $totalPendingFine + $fineIncrease
                    );
                    
                    if ($smsSent) {
                        $ticket->dss_sms_sent = true;
                        $ticket->save();
                        Log::info("DSS: Penalty SMS sent for ticket #{$ticket->citation_number} - {$penaltyLevel} (Count: {$totalUnpaidCount})");
                    } else {
                        Log::warning("DSS: Failed to send penalty SMS for ticket #{$ticket->citation_number}");
                    }
                } catch (\Exception $smsException) {
                    Log::error("DSS: SMS exception for ticket #{$ticket->citation_number}: " . $smsException->getMessage());
                }

                // Update all other unpaid tickets for this violator with the new count
                $this->updateViolatorUnpaidCount($violatorIdentifier, $totalUnpaidCount);
            } else {
                // First violation - no penalty, but track count
                $ticket->unpaid_violation_count = 1;
                $ticket->save();
            }

        } catch (\Exception $e) {
            Log::error('DSS: Error applying penalties for ticket #' . ($ticket->citation_number ?? 'N/A') . ': ' . $e->getMessage());
        }
    }

    /**
     * Update unpaid violation count for all tickets of a violator
     */
    private function updateViolatorUnpaidCount(string $violatorIdentifier, int $count): void
    {
        $query = Ticket::where('is_archived', false)
            ->where(function($q) {
                $q->where('status', 'Unpaid')
                  ->orWhereNull('status');
            });

        if (str_starts_with($violatorIdentifier, 'dl_')) {
            $dlNumber = substr($violatorIdentifier, 3);
            $query->where('dl_number', $dlNumber);
        } else {
            $nameParts = explode('_', substr($violatorIdentifier, 5));
            if (count($nameParts) >= 2) {
                $query->where('driver_firstname', $nameParts[0])
                      ->where('driver_lastname', end($nameParts));
                if (count($nameParts) === 3) {
                    $query->where('driver_middlename', $nameParts[1]);
                }
            }
        }

        $query->update(['unpaid_violation_count' => $count]);
    }

    /**
     * Handle payment and reset violation count if paid on time
     * This should be called when a ticket is marked as paid
     */
    public function handlePaymentReset(Ticket $ticket): void
    {
        try {
            $violatorIdentifier = $this->getViolatorIdentifier($ticket);
            
            if (!$violatorIdentifier) {
                return;
            }

            // Check if this was the first violation and paid on time
            // "On time" means paid before any new violations were issued
            $firstViolation = Ticket::where('is_archived', false)
                ->where(function($q) use ($violatorIdentifier) {
                    if (str_starts_with($violatorIdentifier, 'dl_')) {
                        $dlNumber = substr($violatorIdentifier, 3);
                        $q->where('dl_number', $dlNumber);
                    } else {
                        $nameParts = explode('_', substr($violatorIdentifier, 5));
                        if (count($nameParts) >= 2) {
                            $q->where('driver_firstname', $nameParts[0])
                              ->where('driver_lastname', end($nameParts));
                            if (count($nameParts) === 3) {
                                $q->where('driver_middlename', $nameParts[1]);
                            }
                        }
                    }
                })
                ->orderBy('created_at', 'asc')
                ->first();

            // If this is the first violation and it's being paid, reset counts
            if ($firstViolation && $firstViolation->id === $ticket->id) {
                // Check if there are any other unpaid tickets
                $otherUnpaidCount = $this->countUnpaidViolations($violatorIdentifier, $ticket->id);
                
                if ($otherUnpaidCount == 0) {
                    // No other unpaid tickets - reset all DSS fields for this violator
                    $this->resetViolatorDssStatus($violatorIdentifier);
                    Log::info("DSS: Reset violation count for violator (paid first violation on time) - Ticket #{$ticket->citation_number}");
                }
            } else {
                // Not the first violation - just update counts for remaining unpaid tickets
                $remainingUnpaidCount = $this->countUnpaidViolations($violatorIdentifier, $ticket->id);
                $this->updateViolatorUnpaidCount($violatorIdentifier, $remainingUnpaidCount);
            }

        } catch (\Exception $e) {
            Log::error('DSS: Error handling payment reset for ticket #' . ($ticket->citation_number ?? 'N/A') . ': ' . $e->getMessage());
        }
    }

    /**
     * Reset DSS status for a violator (when first violation is paid on time)
     */
    private function resetViolatorDssStatus(string $violatorIdentifier): void
    {
        $query = Ticket::where('is_archived', false);

        if (str_starts_with($violatorIdentifier, 'dl_')) {
            $dlNumber = substr($violatorIdentifier, 3);
            $query->where('dl_number', $dlNumber);
        } else {
            $nameParts = explode('_', substr($violatorIdentifier, 5));
            if (count($nameParts) >= 2) {
                $query->where('driver_firstname', $nameParts[0])
                      ->where('driver_lastname', end($nameParts));
                if (count($nameParts) === 3) {
                    $query->where('driver_middlename', $nameParts[1]);
                }
            }
        }

        $query->update([
            'unpaid_violation_count' => 0,
            'dss_penalty_level' => null,
            'dss_penalty_applied_at' => null,
            'dss_sms_sent' => false,
            'dss_penalty_fine_increase' => 0,
            'dss_notes' => null,
        ]);
    }

    /**
     * Recalculate DSS penalties for all existing unpaid tickets
     * This ensures existing violators get penalties applied retroactively
     */
    private function recalculateAllDssPenalties(): void
    {
        try {
            // Get all unpaid tickets
            $unpaidTickets = Ticket::where('is_archived', false)
                ->where(function($q) {
                    $q->where('status', 'Unpaid')
                      ->orWhereNull('status');
                })
                ->get();

            // Group by violator identifier
            $violatorTickets = [];
            
            foreach ($unpaidTickets as $ticket) {
                $violatorIdentifier = $this->getViolatorIdentifier($ticket);
                if (!$violatorIdentifier) {
                    continue;
                }
                
                if (!isset($violatorTickets[$violatorIdentifier])) {
                    $violatorTickets[$violatorIdentifier] = [];
                }
                
                $violatorTickets[$violatorIdentifier][] = $ticket;
            }

            // Process each violator
            foreach ($violatorTickets as $violatorIdentifier => $tickets) {
                $unpaidCount = count($tickets);
                
                // Only process if violator has 2+ unpaid tickets
                if ($unpaidCount < 2) {
                    continue;
                }

                // Sort tickets by creation date to determine which ticket gets which penalty
                usort($tickets, function($a, $b) {
                    $dateA = $a->issued_date ?? $a->created_at;
                    $dateB = $b->issued_date ?? $b->created_at;
                    return $dateA <=> $dateB;
                });

                // Calculate total pending fine (base prices only, before penalties)
                $totalPendingFine = 0;
                foreach ($tickets as $ticket) {
                    // Get base price (subtract any existing penalty increase)
                    $basePrice = floatval($ticket->price ?? 0) - floatval($ticket->dss_penalty_fine_increase ?? 0);
                    $totalPendingFine += $basePrice;
                }

                // Determine penalty level based on unpaid count
                $penaltyLevel = null;
                if ($unpaidCount >= 5) {
                    $penaltyLevel = 'permanent_ban';
                } elseif ($unpaidCount >= 4) {
                    $penaltyLevel = 'suspension_extended';
                } elseif ($unpaidCount >= 3) {
                    $penaltyLevel = 'suspension_temp';
                } elseif ($unpaidCount >= 2) {
                    $penaltyLevel = 'warning';
                }

                // Apply penalties based on total unpaid count
                // The penalty applies to the LATEST ticket (most recent unpaid ticket)
                // Previous tickets keep their base prices but get updated count
                $latestTicket = $tickets[count($tickets) - 1]; // Most recent ticket
                
                foreach ($tickets as $index => $ticket) {
                    // Get base price (remove any existing penalty to get original price)
                    $basePrice = floatval($ticket->price ?? 0) - floatval($ticket->dss_penalty_fine_increase ?? 0);
                    if ($basePrice <= 0) {
                        $basePrice = floatval($ticket->price ?? 0);
                    }
                    
                    $fineIncrease = 0;
                    $notes = [];
                    $ticketPenaltyLevel = null;

                    // Only apply penalty to the latest ticket based on total unpaid count
                    if ($ticket->id === $latestTicket->id && $unpaidCount >= 2) {
                        if ($unpaidCount == 2) {
                            $ticketPenaltyLevel = 'warning';
                            $fineIncrease = $basePrice * 0.20; // 20% increase
                            $notes[] = '2nd violation: Warning issued. Fine increased by 20%.';
                        } elseif ($unpaidCount == 3) {
                            $ticketPenaltyLevel = 'suspension_temp';
                            $fineIncrease = $basePrice * 0.30; // 30% increase
                            $notes[] = '3rd violation: Temporary DL suspension. Must pay all pending tickets before suspension lifted.';
                        } elseif ($unpaidCount == 4) {
                            $ticketPenaltyLevel = 'suspension_extended';
                            $fineIncrease = $basePrice * 0.50; // 50% increase
                            $notes[] = '4th violation: Extended DL suspension. Legal action initiated.';
                        } else {
                            $ticketPenaltyLevel = 'permanent_ban';
                            $fineIncrease = $basePrice * 1.00; // 100% increase (double fine)
                            $notes[] = "{$unpaidCount}th violation: Permanent DL ban. Legal proceedings initiated.";
                        }

                        // Update ticket with DSS information
                        $ticket->unpaid_violation_count = $unpaidCount;
                        $ticket->dss_penalty_level = $ticketPenaltyLevel;
                        $ticket->dss_penalty_applied_at = Carbon::now();
                        $ticket->dss_penalty_fine_increase = $fineIncrease;
                        $ticket->dss_notes = implode(' ', $notes);
                        
                        // Update ticket price with penalty increase
                        $ticket->price = $basePrice + $fineIncrease;
                        $ticket->save();

                        // Send SMS only if not already sent
                        if (!$ticket->dss_sms_sent) {
                            try {
                                $totalFineWithPenalties = $totalPendingFine + $fineIncrease;
                                
                                $smsSent = $this->smsService->sendDssPenaltyNotification(
                                    $ticket,
                                    $unpaidCount,
                                    $penaltyLevel,
                                    $totalFineWithPenalties
                                );
                                
                                if ($smsSent) {
                                    $ticket->dss_sms_sent = true;
                                    $ticket->save();
                                    Log::info("DSS: Penalty SMS sent for ticket #{$ticket->citation_number} - {$penaltyLevel} (Count: {$unpaidCount})");
                                }
                            } catch (\Exception $smsException) {
                                Log::error("DSS: SMS exception for ticket #{$ticket->citation_number}: " . $smsException->getMessage());
                            }
                        }
                    } else {
                        // For other tickets, just update the count (no penalty increase)
                        $ticket->unpaid_violation_count = $unpaidCount;
                        // Reset penalty fields if this ticket previously had penalties but shouldn't now
                        if ($index < count($tickets) - 1) {
                            // Keep existing penalty if it was already applied, otherwise clear it
                            if (!$ticket->dss_penalty_level) {
                                $ticket->dss_penalty_fine_increase = 0;
                            }
                        }
                        $ticket->save();
                    }
                }
            }

            Log::info('DSS: Recalculated penalties for all unpaid tickets');
        } catch (\Exception $e) {
            Log::error('DSS: Error recalculating penalties: ' . $e->getMessage());
        }
    }
}