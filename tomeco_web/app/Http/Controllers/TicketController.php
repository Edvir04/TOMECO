<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Services\IprogSmsService;
use Illuminate\Support\Facades\Log; // Useful for debugging
use Illuminate\Support\Facades\Storage;

class TicketController extends Controller
{
    protected IprogSmsService $smsService;

    public function __construct(IprogSmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function index()
    {
        // 👇 2. Get all the tickets from the database
        $tickets = Ticket::all(); // Or Ticket::latest()->get()

        // 👇 3. Pass the $tickets variable to your view
        // The view name 'layout.ticket-issuance' is based on your route name.
        return view('layout.ticket-issuance', [
            'tickets' => $tickets
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
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:51200', // 50MB max per image
            'images' => 'nullable|array',
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

            // 3. Create the ticket
            $ticket = Ticket::create($validatedData);

            // Send SMS notification (best effort)
            $this->smsService->sendTicketCreatedNotification($ticket);

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
}