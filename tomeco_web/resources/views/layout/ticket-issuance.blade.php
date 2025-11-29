@extends('layout.app')

@section('content')

{{-- 
  FIX: This <style> block is copied directly from your working 
  'accounts.blade.php' file to ensure the modal is centered 
  and scrolls correctly.
--}}
<style>
    .page-wrap{max-width:1200px;margin:24px auto;padding:0 12px}
    .toolbar{display:flex;gap:12px;align-items:center;justify-content:space-between;margin-bottom:16px}
    .btn{display:inline-flex;align-items:center;gap:8px;border:0;border-radius:8px;padding:10px 14px;cursor:pointer}
    .btn-primary{background:#FFCC3F;color:#111}
    .btn-primary:hover{background:#e5a400}
    .btn-danger{background:#e53935;color:#fff}
    .btn-light{background:#f3f4f6;color:#111}
    .btn-info{background:#3b82f6;color:#fff}
    .btn-sm{padding:6px 10px;font-size:12px}
    .table-wrap{overflow:auto;border:1px solid #e5e7eb;border-radius:10px}
    table{width:100%;border-collapse:collapse;font-size:14px}
    thead th{position:sticky;top:0;background:#fafafa;border-bottom:1px solid #e5e7eb;text-align:left;padding:10px;white-space:nowrap}
    tbody td{border-top:1px solid #f0f0f0;padding:10px;vertical-align:middle}
    .table-empty{padding:18px;color:#6b7280}
    .alert{padding:10px 12px;border-radius:8px;margin-bottom:12px}
    .alert-success{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}
    .alert-danger{background:#fef2f2;color:#7f1d1d;border:1px solid #fecaca}

    /* Modal - perfectly centered */
    .modal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.45);
        z-index: 1050;
        justify-content: center;
        align-items: center;
        padding: 30px;
    }

    .modal.open {
        display: flex;
    }

    /* Center modal with scroll if content exceeds viewport */
    .modal-card {
        background: #fff;
        /* FIX: Changed from 640px to 1100px to match modal-xl */
        width: min(1100px, 95%);
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        max-height: 90vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        position: relative;
        transform: translateY(10px);
        animation: slideDown 0.25s ease forwards;
    }

    /* Smooth appear animation */
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Scroll inside modal if content too long */
    .modal-body {
        padding: 16px;
        overflow-y: auto;
        flex: 1 1 auto;
    }

    /* Head styling */
    .modal-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 16px;
        border-bottom: 1px solid #eee;
        background: #fafafa;
        color: #111;
        font-weight: 600;
    }
    
    .grid{display:grid;gap:12px}
    .grid-2{grid-template-columns:repeat(2,minmax(0,1fr))}
    label{font-weight:600;font-size:13px}
    input[type="text"],input[type="date"],input[type="time"],input[type="password"],input[type="file"],select,textarea{width:100%;border:1px solid #d1d5db;border-radius:8px;padding:10px 12px}
    textarea{min-height: 80px;}
    input[type="file"]{padding:8px}
    
    /* Spinner for loading */
    .spinner-border{display:inline-block;width:2rem;height:2rem;vertical-align:text-bottom;border:0.25em solid currentColor;border-right-color:transparent;border-radius:50%;animation:spinner-border 0.75s linear infinite}
    @keyframes spinner-border{to{transform:rotate(360deg)}}
    .visually-hidden{position:absolute!important;width:1px!important;height:1px!important;padding:0!important;margin:-1px!important;overflow:hidden!important;clip:rect(0,0,0,0)!important;white-space:nowrap!important;border:0!important}

    /* Mobile fix */
    @media (max-width: 640px) {
        .modal {
            align-items: flex-start;
            padding-top: 60px;
        }
    }

    /* Print styles */
    @media print {
        body * {
            visibility: hidden;
        }
        .print-ticket, .print-ticket * {
            visibility: visible;
        }
        .print-ticket {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 20px;
            background: white;
        }
        .print-ticket .no-print {
            display: none !important;
        }
        .print-ticket h1, .print-ticket h2, .print-ticket h3, .print-ticket h4, .print-ticket h5, .print-ticket h6 {
            color: #000 !important;
            page-break-after: avoid;
        }
        .print-ticket .section {
            page-break-inside: avoid;
            margin-bottom: 20px;
        }
        .print-ticket img {
            max-width: 100% !important;
            max-height: 300px !important;
        }
        .print-ticket .signature-img {
            max-height: 150px !important;
        }
    }
</style>

<div class="container-fluid mt-4 page-wrap">
    <div class="row mb-3 toolbar">
        <div class="col">
            <h2 style="margin:0;">Traffic Ticket Issuance</h2>
        </div>
        <div class="col text-end">
            {{-- FIX: Added id="openCreateModal" for the new JS --}}
            <a href="{{ route('admin.ticket-issuance') }}?create=true" class="btn btn-primary" id="openCreateModal" aria-haspopup="dialog" aria-controls="createModal">
              + Create New Ticket
            </a>
        </div>
    </div>

    {{-- Success Alert --}}
    @if (session('success'))
        <div id="flash-status" class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
        </div>
    @endif
    
    {{-- Error Alert --}}
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
        </div>
    @endif
    
    {{-- Display validation errors (for modal) --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Whoops!</strong> Please correct the errors in the form:
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Main content: Table of existing tickets --}}
    <div class="card table-wrap">
        <div class="card-body">
            <table>
                <thead>
                    <tr>
                        <th>Citation #</th>
                        <th>Driver Name</th>
                        <th>Plate #</th>
                        <th>Violations</th>
                        <th>Issued Date</th>
                        <th>Officer</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Loop through tickets passed from TicketController --}}
                    @forelse ($tickets as $ticket)
                        <tr>
                            <td>{{ $ticket->citation_number }}</td>
                            <td>{{ $ticket->driver_firstname }} {{ $ticket->driver_lastname }}</td>
                            <td>{{ $ticket->plate_number }}</td>
                            <td>
                                {{-- Display violations array --}}
                                {{ $ticket->violations ? implode(', ', $ticket->violations) : 'N/A' }}
                                @if($ticket->violations_others_text)
                                    (Other: {{ $ticket->violations_others_text }})
                                @endif
                            </td>
                            <td>{{ $ticket->issued_date ? $ticket->issued_date->format('M d, Y') : 'N/A' }}</td>
                            <td>{{ $ticket->apprehending_officer }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info view-ticket-btn" data-ticket-id="{{ $ticket->id }}" onclick="openViewModalById({{ $ticket->id }})">View</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center table-empty">No tickets found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div> 
{{-- End Main Content --}}


{{-- 
======================================================================
    MODAL FOR CREATING A NEW TICKET
    FIX: Rebuilt using the 'accounts.blade.php' HTML structure
======================================================================
--}}
<div class="modal" id="createModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="createModalTitle">
  <div class="modal-card" role="document">
    
    {{-- FIX: New Modal Head --}}
    <div class="modal-head">
      <div id="createModalTitle">New Traffic Ticket</div>
      {{-- FIX: Added id="closeCreateModal" for the new JS --}}
      <button class="btn btn-light" id="closeCreateModal" aria-label="Close create ticket dialog" style="padding: 6px 8px;">✖</button>
    </div>

    {{-- FIX: New Modal Body --}}
    <div class="modal-body">
      
      {{-- Form points to the 'tickets.store' route or 'tickets.update' for editing --}}
      <form method="POST" id="ticketForm" action="{{ route('admin.tickets.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="_method" id="formMethod" value="POST">
        <input type="hidden" name="ticket_id" id="ticketId" value="">

          <div class="container-fluid">
            
            {{-- Section 1: Issuance Details --}}
            <h6 class="text-primary">Issuance Details</h6>
            <div class="row mb-3 grid grid-2" style="grid-template-columns: repeat(4, 1fr); gap: 12px;">
              <div>
                <label class="form-label">Citation / Ticket #</label>
                <input type="text" name="citation_number" class="form-control" placeholder="e.g. 118753" value="{{ old('citation_number') }}">
              </div>
              <div>
                <label class="form-label">Date</label>
                <input type="date" name="issued_date" class="form-control" value="{{ old('issued_date') }}">
              </div>
              <div>
                <label class="form-label">Time</label>
                <input type="time" name="issued_time" class="form-control" value="{{ old('issued_time') }}">
              </div>
              <div>
                <label class="form-label">Issued By (Name)</label>
                <input type="text" name="issued_by" class="form-control" value="{{ old('issued_by') }}">
              </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-9">
                    <label class="form-label">Place of Violation</label>
                    <input type="text" name="place" class="form-control" placeholder="e.g. 123 Main St, Brgy. Poblacion" value="{{ old('place') }}">
                </div>
            </div>

            <hr style="margin: 16px 0;">

            {{-- Section 2: Driver Details --}}
            <h6 class="text-primary">Driver Details</h6>
            <div class="row mb-3 grid grid-2" style="grid-template-columns: repeat(3, 1fr); gap: 12px;">
              <div>
                <label class="form-label">Last Name</label>
                <input type="text" name="driver_lastname" class="form-control" value="{{ old('driver_lastname') }}" required>
              </div>
              <div>
                <label class="form-label">First Name</label>
                <input type="text" name="driver_firstname" class="form-control" value="{{ old('driver_firstname') }}" required>
              </div>
              <div>
                <label class="form-label">Middle Name</label>
                <input type="text" name="driver_middlename" class="form-control" value="{{ old('driver_middlename') }}">
              </div>
            </div>
            <div class="row mb-3 grid grid-2" style="grid-template-columns: 2fr 1fr 1fr; gap: 12px;">
              <div>
                <label class="form-label">Driver's Address</label>
                <input type="text" name="driver_address" class="form-control" value="{{ old('driver_address') }}">
              </div>
              <div>
                <label class="form-label">Driver's License #</label>
                <input type="text" name="dl_number" class="form-control" value="{{ old('dl_number') }}">
                <div style="margin-top: 8px;">
                  <label class="form-label" style="font-size: 13px; margin-bottom: 4px;">Driver's License Type:</label>
                  <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="dl_type" value="Prof" id="dl_prof" {{ old('dl_type') == 'Prof' ? 'checked' : '' }}>
                      <label class="form-check-label" for="dl_prof" style="font-size: 13px;">Prof</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="dl_type" value="N/P" id="dl_np" {{ old('dl_type') == 'N/P' ? 'checked' : '' }}>
                      <label class="form-check-label" for="dl_np" style="font-size: 13px;">N/P</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="dl_type" value="S/P" id="dl_sp" {{ old('dl_type') == 'S/P' ? 'checked' : '' }}>
                      <label class="form-check-label" for="dl_sp" style="font-size: 13px;">S/P</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="dl_type" value="Others" id="dl_others" {{ old('dl_type') == 'Others' ? 'checked' : '' }}>
                      <label class="form-check-label" for="dl_others" style="font-size: 13px;">Others</label>
                    </div>
                  </div>
                </div>
              </div>
              <div>
                <label class="form-label">Contact #</label>
                <input type="text" name="driver_contact" class="form-control" value="{{ old('driver_contact') }}">
              </div>
            </div>

            <hr style="margin: 16px 0;">

            {{-- Section 3: Vehicle & Owner Details --}}
            <h6 class="text-primary">Vehicle & Owner Details</h6>
            <div class="row mb-3 grid grid-2" style="grid-template-columns: repeat(5, 1fr); gap: 12px;">
              <div>
                <label class="form-label">Plate #</label>
                <input type="text" name="plate_number" class="form-control" value="{{ old('plate_number') }}">
              </div>
              <div>
                <label class="form-label">CR #</label>
                <input type="text" name="cr_number" class="form-control" value="{{ old('cr_number') }}">
              </div>
              <div>
                <label class="form-label">Year</label>
                <input type="text" name="vehicle_year" class="form-control" placeholder="e.g. 2023" maxlength="4" value="{{ old('vehicle_year') }}">
              </div>
              <div>
                <label class="form-label">Make</label>
                <input type="text" name="vehicle_make" class="form-control" placeholder="e.g. Toyota" value="{{ old('vehicle_make') }}">
              </div>
              <div>
                <label class="form-label">Model</label>
                <input type="text" name="vehicle_model" class="form-control" placeholder="e.g. Vios" value="{{ old('vehicle_model') }}">
              </div>
            </div>
            <div class="row mb-3 grid grid-2" style="grid-template-columns: repeat(2, 1fr); gap: 12px;">
              <div>
                <label class="form-label">Type</label>
                <input type="text" name="vehicle_type" class="form-control" value="{{ old('vehicle_type') }}">
              </div>
              <div>
                <label class="form-label">OR #</label>
                <input type="text" name="or_number" class="form-control" value="{{ old('or_number') }}">
              </div>
            </div>
             <div class="row mb-3 grid grid-2">
              <div>
                <label class="form-label">Owner's Name (if not driver)</label>
                <input type="text" name="owner_name" class="form-control" value="{{ old('owner_name') }}">
              </div>
              <div>
                <label class="form-label">Owner's Address</label>
                <input type="text" name="owner_address" class="form-control" value="{{ old('owner_address') }}">
              </div>
            </div>

            <hr style="margin: 16px 0;">

            {{-- Section 4: Violation Details --}}
            <h6 class="text-primary">Violation(s)</h6>
            <div class="row mb-3 grid grid-2">
                <div>
                    <label class="form-label">Violations:</label>
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="violations[]" value="Speeding" id="v1" {{ in_array('Speeding', old('violations', [])) ? 'checked' : '' }}>
                      <label class="form-check-label" for="v1">Speeding</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="violations[]" value="Illegal Parking" id="v2" {{ in_array('Illegal Parking', old('violations', [])) ? 'checked' : '' }}>
                      <label class="form-check-label" for="v2">Illegal Parking</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="violations[]" value="No Helmet" id="v3" {{ in_array('No Helmet', old('violations', [])) ? 'checked' : '' }}>
                      <label class="form-check-label" for="v3">No Helmet</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="violations[]" value="Disregarding Traffic Sign" id="v4" {{ in_array('Disregarding Traffic Sign', old('violations', [])) ? 'checked' : '' }}>
                      <label class="form-check-label" for="v4">Disregarding Traffic Sign</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="violations[]" value="Other" id="v_other" {{ in_array('Other', old('violations', [])) ? 'checked' : '' }}>
                      <label class="form-check-label" for="v_other">Other (Please specify below)</label>
                    </div>

                    <label class="form-label" style="margin-top: 12px;">Other Violation Text:</label>
                    <input type="text" name="violations_others_text" class="form-control" placeholder="Specify 'Other' violation..." value="{{ old('violations_others_text') }}">
                </div>
                <div>
                    <label class="form-label">Place of Violation</label>
                    <input type="text" name="place" class="form-control" value="{{ old('place') }}">
                    
                    <label class="form-label" style="margin-top: 12px;">Accident:</label>
                    <div style="display: flex; gap: 15px; margin-top: 4px;">
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="accident" value="1" id="accident_yes" {{ old('accident') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="accident_yes">Yes</label>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="accident" value="0" id="accident_no" {{ old('accident') == '0' || old('accident') === null ? 'checked' : '' }}>
                        <label class="form-check-label" for="accident_no">No</label>
                      </div>
                    </div>

                    <label class="form-label" style="margin-top: 12px;">Incident Notes / Details</label>
                    <textarea name="incident_notes" class="form-control" rows="4">{{ old('incident_notes') }}</textarea>

                    <label class="form-label" style="margin-top: 12px;">Remarks</label>
                    <textarea name="remarks" class="form-control" rows="3">{{ old('remarks') }}</textarea>
                </div>
            </div>

            <hr style="margin: 16px 0;">

            {{-- Section 4.5: Driver Promise and Signature --}}
            <h6 class="text-primary">Driver's Promise & Signature</h6>
            <div class="row mb-3">
              <div>
                <label class="form-label">Admitted / Under Protest:</label>
                <div style="display: flex; gap: 15px; margin-top: 4px;">
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="admitted_or_protest" value="Admitted" id="admitted" {{ old('admitted_or_protest') == 'Admitted' ? 'checked' : '' }}>
                    <label class="form-check-label" for="admitted">Admitted</label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="admitted_or_protest" value="Under Protest" id="under_protest" {{ old('admitted_or_protest') == 'Under Protest' ? 'checked' : '' }}>
                    <label class="form-check-label" for="under_protest">Under Protest</label>
                  </div>
                </div>
                <label class="form-label" style="margin-top: 12px;">Driver Signature</label>
                <div style="border: 2px solid #d1d5db; border-radius: 8px; background: #fff; padding: 12px;">
                  <canvas id="driverSignatureCanvas" width="600" height="200" style="border: 1px solid #e5e7eb; border-radius: 4px; cursor: crosshair; width: 100%; max-width: 600px; touch-action: none;"></canvas>
                  <div style="margin-top: 12px; display: flex; gap: 8px;">
                    <button type="button" class="btn btn-light" onclick="clearDriverSignature()">Clear</button>
                    <small class="text-muted" style="align-self: center; margin-left: auto;">Driver signs in the box above</small>
                  </div>
                </div>
                <input type="hidden" name="driver_signature" id="driverSignatureInput">
              </div>
            </div>

            <hr style="margin: 16px 0;">

            {{-- Section 5: Image Upload --}}
            <h6 class="text-primary">Evidence / Photo Upload</h6>
            <div class="row mb-3">
              <div>
                <label class="form-label">Upload Image or Take Photo</label>
                
                {{-- Toggle buttons for Upload/Camera --}}
                <div style="display: flex; gap: 8px; margin-bottom: 12px;">
                  <button type="button" id="uploadModeBtn" class="btn btn-primary" onclick="switchToUploadMode()" style="flex: 1;">Upload Photo</button>
                  <button type="button" id="cameraModeBtn" class="btn btn-light" onclick="switchToCameraMode()" style="flex: 1;">Take Photo</button>
                </div>
                
                {{-- Upload Mode --}}
                <div id="uploadMode" style="display: block;">
                  <input type="file" name="images[]" id="imageUpload" class="form-control" accept="image/*" multiple onchange="previewImages(this)">
                  <small class="text-muted">Accepted formats: JPG, PNG, GIF (Max size: 50MB per image). You can select multiple images.</small>
                </div>
                
                {{-- Camera Mode --}}
                <div id="cameraMode" style="display: none;">
                  <div style="border: 2px solid #d1d5db; border-radius: 8px; padding: 12px; background: #f9fafb;">
                    <video id="cameraVideo" autoplay playsinline style="width: 100%; max-width: 100%; border-radius: 4px; display: none;"></video>
                    <canvas id="cameraCanvas" style="display: none;"></canvas>
                    <div id="cameraControls" style="margin-top: 12px; display: none;">
                      <button type="button" class="btn btn-primary" onclick="capturePhoto()" style="width: 100%; margin-bottom: 8px;">Capture Photo</button>
                      <button type="button" class="btn btn-light" onclick="stopCamera()" style="width: 100%;">Stop Camera</button>
                      <small class="text-muted" style="display: block; text-align: center; margin-top: 8px;">You can capture multiple photos</small>
                    </div>
                    <button type="button" id="startCameraBtn" class="btn btn-primary" onclick="startCamera()" style="width: 100%;">Start Camera</button>
                  </div>
                </div>
                
                {{-- Loading Indicator --}}
                <div id="imageLoadingIndicator" style="display: none; margin-top: 12px; padding: 20px; text-align: center; background: #f9fafb; border: 2px dashed #d1d5db; border-radius: 8px;">
                  <div class="spinner-border" role="status" style="color: #3b82f6;">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                  <p style="margin-top: 12px; color: #6b7280; font-size: 14px; margin-bottom: 0;" id="imageLoadingText">Processing images...</p>
                </div>
                
                {{-- Image Preview --}}
                <div id="imagePreview" style="margin-top: 12px; display: none;">
                  <div id="existingImagesWrapper" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px;"></div>
                  <div id="newImagesWrapper" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px; margin-top: 12px;"></div>
                  <button type="button" onclick="removeAllImages()" class="btn btn-light" style="margin-top: 12px;">Remove All Images</button>
                </div>
                {{-- Hidden input to track existing images to keep --}}
                <input type="hidden" name="existing_images" id="existingImagesInput" value="">
              </div>
            </div>

            <hr style="margin: 16px 0;">

            {{-- Section 6: E-Signature --}}
            <h6 class="text-primary">E-Signature</h6>
            <div class="row mb-3">
              <div>
                <label class="form-label">Officer Signature</label>
                <div style="border: 2px solid #d1d5db; border-radius: 8px; background: #fff; padding: 12px;">
                  <canvas id="signatureCanvas" width="600" height="200" style="border: 1px solid #e5e7eb; border-radius: 4px; cursor: crosshair; width: 100%; max-width: 600px; touch-action: none;"></canvas>
                  <div style="margin-top: 12px; display: flex; gap: 8px;">
                    <button type="button" class="btn btn-light" onclick="clearSignature()">Clear</button>
                    <small class="text-muted" style="align-self: center; margin-left: auto;">Sign in the box above</small>
                  </div>
                </div>
                <input type="hidden" name="signature" id="signatureInput">
              </div>
            </div>

            <hr style="margin: 16px 0;">

            {{-- Section 7: Court & Officer Details --}}
            <h6 class="text-primary">Officer & Court Details</h6>
            <div class="row mb-3 grid grid-2" style="grid-template-columns: repeat(4, 1fr); gap: 12px;">
              <div>
                <label class="form-label">Court Date</label>
                <input type="date" name="court_date" class="form-control" value="{{ old('court_date') }}">
              </div>
              <div>
                <label class="form-label">Court Time</label>
                <input type="time" name="court_time" class="form-control" value="{{ old('court_time') }}">
              </div>
              <div>
                <label class="form-label">Apprehending Officer</label>
                <input type="text" name="apprehending_officer" class="form-control" value="{{ old('apprehending_officer') }}">
              </div>
              <div>
                <label class="form-label">TOMECO DID</label>
                <input type="text" name="tomeco_did" class="form-control" value="{{ old('tomeco_did') }}">
              </div>
            </div>

          </div> {{-- End .container-fluid --}}

        {{-- 
          FIX: Moved the footer buttons inside the modal-body
          and form, just like in 'accounts.blade.php'
        --}}
        <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:20px; border-top: 1px solid #eee; padding-top: 16px;">
            {{-- FIX: Added id="cancelCreate" for the new JS --}}
            <button type="button" class="btn btn-light" id="cancelCreate">Cancel</button>
            <button type="submit" class="btn btn-primary" id="submitBtn" onclick="saveSignature()">Save Ticket</button>
        </div>
      </form>
    </div> {{-- End .modal-body --}}
  </div> {{-- End .modal-card --}}
</div>
{{-- End Modal --}}


{{-- 
======================================================================
    MODAL FOR VIEWING TICKET DETAILS
======================================================================
--}}
<div class="modal" id="viewModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="viewModalTitle">
  <div class="modal-card" role="document">
    
    <div class="modal-head">
      <div id="viewModalTitle">Ticket Details</div>
      <button class="btn btn-light" id="closeViewModal" aria-label="Close view ticket dialog" style="padding: 6px 8px;">✖</button>
    </div>

    <div class="modal-body" id="viewModalBody">
      <div style="text-align: center; padding: 40px;">
        <div class="spinner-border" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p style="margin-top: 12px; color: #6b7280;">Loading ticket details...</p>
      </div>
    </div>
  </div>
</div>
{{-- End View Modal --}}


{{-- 
======================================================================
    MODAL FOR VIEWING IMAGE
======================================================================
--}}
<div class="modal" id="imageModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="imageModalTitle">
  <div class="modal-card" role="document" style="max-width: 90vw; width: auto;">
    <div class="modal-head">
      <div id="imageModalTitle">Evidence Photo</div>
      <button class="btn btn-light" id="closeImageModal" aria-label="Close image viewer" style="padding: 6px 8px;">✖</button>
    </div>
    <div class="modal-body" style="text-align: center; padding: 20px; background: #f9fafb;">
      <img id="imageModalImg" src="" alt="Evidence Photo" style="max-width: 100%; max-height: 80vh; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    </div>
  </div>
</div>
{{-- End Image Modal --}}


 
<script>
    // Flash fade
    (function(){
        var el = document.getElementById('flash-status');
        if(!el) return;
        setTimeout(function(){
            el.style.transition='opacity 400ms ease'; el.style.opacity='0';
            setTimeout(function(){ if(el && el.parentNode) el.parentNode.removeChild(el); }, 420);
        }, 2000);
    })();

    // Modal controls with better centering + accessibility
    const modal = document.getElementById('createModal');
    const openBtn = document.getElementById('openCreateModal');
    const closeBtn = document.getElementById('closeCreateModal');
    const cancelBtn = document.getElementById('cancelCreate');
    
    // FIX: Updated the selector to find the first input in *this* form
    const firstInputSelector = 'input[name="citation_number"]';

    function openModal(isEdit = false){
        // Hide loading indicator when opening modal
        const loadingIndicator = document.getElementById('imageLoadingIndicator');
        if (loadingIndicator) loadingIndicator.style.display = 'none';
        
        // Re-enable submit button if it was disabled
        const submitBtn = document.getElementById('submitBtn');
        if (submitBtn) {
            submitBtn.disabled = false;
        }
        
        if (!isEdit) {
            // Reset form for create mode
            document.getElementById('createModalTitle').textContent = 'New Traffic Ticket';
            document.getElementById('ticketForm').action = '{{ route("admin.tickets.store") }}';
            document.getElementById('formMethod').value = 'POST';
            document.getElementById('ticketId').value = '';
            document.getElementById('ticketForm').reset();
            document.getElementById('submitBtn').textContent = 'Save Ticket';
            // Clear image preview
            document.getElementById('imagePreview').style.display = 'none';
            document.getElementById('existingImagesWrapper').innerHTML = '';
            document.getElementById('newImagesWrapper').innerHTML = '';
            document.getElementById('imageUpload').value = '';
            existingImagesMeta = [];
            allSelectedFiles = []; // Clear all selected files when resetting form
            if (typeof updateExistingImagesInput === 'function') {
                updateExistingImagesInput();
            }
            // Stop camera if running
            if (typeof cameraStream !== 'undefined' && cameraStream) {
                stopCamera();
            }
            // Reset to upload mode
            if (typeof switchToUploadMode === 'function') {
                switchToUploadMode();
            }
            // Clear signatures
            clearSignature();
            clearDriverSignature();
        }
        
        modal.classList.add('open');
        modal.setAttribute('aria-hidden','false');
        document.body.style.overflow='hidden';
        // focus the first form control (small delay to ensure visible)
        setTimeout(()=>{
            const first = modal.querySelector(firstInputSelector);
            if(first) first.focus();
            // Re-initialize signature pad after modal opens
            initSignaturePad();
        }, 50);
        // save currently focused element to restore later
        openModal._previousActive = document.activeElement;
        // simple focus trap (tab cycling inside modal)
        document.addEventListener('keydown', handleKeydown);
    }

    function closeModal(){
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden','true');
        document.body.style.overflow='';
        // restore focus
        if(openModal._previousActive) openModal._previousActive.focus();
        document.removeEventListener('keydown', handleKeydown);
    }

    function handleKeydown(e){
        if(e.key === 'Escape') { closeModal(); return; }
        if(e.key === 'Tab'){
            // keep focus inside modal
            const focusable = Array.from(modal.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled])'))
                .filter(el => el.offsetParent !== null);
            if(focusable.length === 0) return;
            const first = focusable[0], last = focusable[focusable.length-1];
            if(e.shiftKey && document.activeElement === first){ e.preventDefault(); last.focus(); }
            else if(!e.shiftKey && document.activeElement === last){ e.preventDefault(); first.focus(); }
        }
    }

    openBtn?.addEventListener('click', (e) => {
        e.preventDefault(); // Stop the <a> tag from navigating
        openModal(false); // false = create mode
    });
    closeBtn?.addEventListener('click', closeModal);
    cancelBtn?.addEventListener('click', closeModal);
    modal?.addEventListener('click', (e)=>{ if(e.target === modal) closeModal(); });

    // Image Preview functionality for multiple images
    let existingImagesMeta = []; // [{ url, path }]
    let allSelectedFiles = []; // Track all files that have been selected (to maintain them across selections)

    function extractStoragePath(url) {
        if (!url) return '';
        const storageMatch = url.match(/\/storage\/(.+)$/);
        if (storageMatch && storageMatch[1]) {
            return storageMatch[1];
        }
        return url;
    }

    function updateExistingImagesInput() {
        const input = document.getElementById('existingImagesInput');
        if (!input) return;
        const imagePaths = existingImagesMeta.map(img => img.path || extractStoragePath(img.url));
        input.value = imagePaths.length ? JSON.stringify(imagePaths) : '';
    }

    function renderExistingImages() {
        const existingWrapper = document.getElementById('existingImagesWrapper');
        const preview = document.getElementById('imagePreview');
        if (!existingWrapper) return;

        existingWrapper.innerHTML = '';

        existingImagesMeta.forEach((imgMeta, index) => {
            const imageDiv = document.createElement('div');
            imageDiv.style.position = 'relative';
            imageDiv.style.border = '1px solid #d1d5db';
            imageDiv.style.borderRadius = '8px';
            imageDiv.style.overflow = 'hidden';
            imageDiv.style.background = '#fff';

            const img = document.createElement('img');
            img.src = imgMeta.url;
            img.alt = 'Existing Photo ' + (index + 1);
            img.style.width = '100%';
            img.style.height = '200px';
            img.style.objectFit = 'cover';
            img.style.display = 'block';
            img.onerror = function() {
                this.style.display = 'none';
                const errorMsg = document.createElement('p');
                errorMsg.style.cssText = 'color: #e53935; padding: 20px; text-align: center; margin: 0; font-size: 12px;';
                errorMsg.textContent = 'Image not found';
                imageDiv.appendChild(errorMsg);
            };

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-sm btn-danger';
            removeBtn.style.cssText = 'position: absolute; top: 8px; right: 8px; padding: 4px 8px; font-size: 12px;';
            removeBtn.innerHTML = '×';
            removeBtn.onclick = function() {
                removeExistingImage(index);
            };

            imageDiv.appendChild(img);
            imageDiv.appendChild(removeBtn);
            existingWrapper.appendChild(imageDiv);
        });

        if (existingImagesMeta.length) {
            preview.style.display = 'block';
        } else if (!document.getElementById('newImagesWrapper').children.length) {
            preview.style.display = 'none';
        }
    }

    function displayExistingImages(imageUrls = [], imagePaths = []) {
        existingImagesMeta = imageUrls.map((url, idx) => ({
            url,
            path: imagePaths[idx] || extractStoragePath(url)
        }));
        renderExistingImages();
        updateExistingImagesInput();
    }

    function removeExistingImage(index) {
        existingImagesMeta.splice(index, 1);
        renderExistingImages();
        updateExistingImagesInput();
        if (!existingImagesMeta.length && document.getElementById('imageUpload').files.length === 0) {
            document.getElementById('imagePreview').style.display = 'none';
        }
    }

    function previewImages(input) {
        const preview = document.getElementById('imagePreview');
        const newWrapper = document.getElementById('newImagesWrapper');
        const loadingIndicator = document.getElementById('imageLoadingIndicator');
        const loadingText = document.getElementById('imageLoadingText');
        if (!newWrapper) return;

        // Get newly selected files from the input
        const newlySelectedFiles = input.files ? Array.from(input.files) : [];
        
        // Add new files to our tracking array (avoid duplicates)
        newlySelectedFiles.forEach(newFile => {
            const fileKey = `${newFile.name}_${newFile.size}_${newFile.lastModified}`;
            const isDuplicate = allSelectedFiles.some(existingFile => 
                `${existingFile.name}_${existingFile.size}_${existingFile.lastModified}` === fileKey
            );
            if (!isDuplicate) {
                allSelectedFiles.push(newFile);
            }
        });

        // Update the file input to contain all selected files
        if (allSelectedFiles.length > 0) {
            const dt = new DataTransfer();
            allSelectedFiles.forEach(file => dt.items.add(file));
            input.files = dt.files;
        }

        // Filter to only process files that haven't been previewed yet
        const existingImageDivs = Array.from(newWrapper.children);
        const existingFileKeys = existingImageDivs.map(div => {
            const fileIndex = parseInt(div.dataset.fileIndex);
            if (fileIndex >= 0 && fileIndex < allSelectedFiles.length) {
                const file = allSelectedFiles[fileIndex];
                return `${file.name}_${file.size}_${file.lastModified}`;
            }
            return null;
        }).filter(key => key !== null);

        const filesToProcess = allSelectedFiles.filter((file, index) => {
            const fileKey = `${file.name}_${file.size}_${file.lastModified}`;
            return !existingFileKeys.includes(fileKey);
        });

        if (filesToProcess.length === 0) {
            // All files already previewed, just show existing preview
            if (loadingIndicator) loadingIndicator.style.display = 'none';
            preview.style.display = 'block';
            renderExistingImages();
            return;
        }

        // Show loading indicator
        if (loadingIndicator) {
            loadingIndicator.style.display = 'block';
            if (loadingText) loadingText.textContent = 'Processing images...';
        }
        preview.style.display = 'none';

        let processedCount = 0;
        const totalFiles = filesToProcess.length;
        
        filesToProcess.forEach((file, relativeIndex) => {
            // Find the index of this file in allSelectedFiles
            const fileIndex = allSelectedFiles.findIndex(f => 
                `${f.name}_${f.size}_${f.lastModified}` === `${file.name}_${file.size}_${file.lastModified}`
            );

            const reader = new FileReader();
            reader.onload = function(e) {
                const imageDiv = document.createElement('div');
                imageDiv.style.position = 'relative';
                imageDiv.style.border = '1px solid #d1d5db';
                imageDiv.style.borderRadius = '8px';
                imageDiv.style.overflow = 'hidden';
                imageDiv.style.background = '#fff';

                const img = document.createElement('img');
                img.src = e.target.result;
                img.alt = 'New Photo ' + (existingImageDivs.length + relativeIndex + 1);
                img.style.width = '100%';
                img.style.height = '200px';
                img.style.objectFit = 'cover';
                img.style.display = 'block';
                
                // Store the file index in allSelectedFiles for removal
                imageDiv.dataset.fileIndex = fileIndex;

                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'btn btn-sm btn-danger';
                removeBtn.style.cssText = 'position: absolute; top: 8px; right: 8px; padding: 4px 8px; font-size: 12px;';
                removeBtn.innerHTML = '×';
                removeBtn.onclick = function() {
                    removeImageAtIndex(fileIndex);
                };

                imageDiv.appendChild(img);
                imageDiv.appendChild(removeBtn);
                newWrapper.appendChild(imageDiv);
                
                processedCount++;
                // Update loading text
                if (loadingText && totalFiles > 1) {
                    loadingText.textContent = `Processing images... (${processedCount}/${totalFiles})`;
                }
                
                // Hide loading indicator when all files are processed
                if (processedCount === totalFiles) {
                    if (loadingIndicator) loadingIndicator.style.display = 'none';
                    preview.style.display = 'block';
                    renderExistingImages();
                }
            };
            reader.onerror = function() {
                processedCount++;
                if (loadingText) {
                    loadingText.textContent = `Error processing image ${relativeIndex + 1}...`;
                }
                if (processedCount === totalFiles) {
                    if (loadingIndicator) loadingIndicator.style.display = 'none';
                    preview.style.display = 'block';
                    renderExistingImages();
                }
            };
            reader.readAsDataURL(file);
        });
    }

    function removeImageAtIndex(index) {
        const input = document.getElementById('imageUpload');
        const newWrapper = document.getElementById('newImagesWrapper');
        
        // Remove from allSelectedFiles array
        if (index >= 0 && index < allSelectedFiles.length) {
            allSelectedFiles.splice(index, 1);
        }
        
        // Update the file input with remaining files
        const dt = new DataTransfer();
        allSelectedFiles.forEach(file => dt.items.add(file));
        input.files = dt.files;
        
        // Remove the corresponding image div from preview
        const imageDivs = Array.from(newWrapper.children);
        const divToRemove = imageDivs.find(div => parseInt(div.dataset.fileIndex) === index);
        if (divToRemove) {
            divToRemove.remove();
        }
        
        // Update indices for remaining divs (since we removed one, indices shift)
        imageDivs.forEach(div => {
            const currentIndex = parseInt(div.dataset.fileIndex);
            if (currentIndex > index) {
                div.dataset.fileIndex = currentIndex - 1;
            }
        });
        
        // Show/hide preview based on remaining images
        const preview = document.getElementById('imagePreview');
        if (allSelectedFiles.length === 0 && existingImagesMeta.length === 0) {
            preview.style.display = 'none';
        } else {
            preview.style.display = 'block';
            renderExistingImages();
        }
    }

    function removeAllImages() {
        const input = document.getElementById('imageUpload');
        const preview = document.getElementById('imagePreview');
        const newWrapper = document.getElementById('newImagesWrapper');
        const existingWrapper = document.getElementById('existingImagesWrapper');
        const cameraVideo = document.getElementById('cameraVideo');

        input.value = '';
        existingImagesMeta = [];
        allSelectedFiles = []; // Clear all selected files
        if (newWrapper) newWrapper.innerHTML = '';
        if (existingWrapper) existingWrapper.innerHTML = '';
        preview.style.display = 'none';
        updateExistingImagesInput();

        if (cameraVideo && cameraVideo.srcObject) {
            stopCamera();
        }
    }

    // Camera functionality
    let cameraStream = null;

    function switchToUploadMode() {
        document.getElementById('uploadMode').style.display = 'block';
        document.getElementById('cameraMode').style.display = 'none';
        document.getElementById('uploadModeBtn').classList.remove('btn-light');
        document.getElementById('uploadModeBtn').classList.add('btn-primary');
        document.getElementById('cameraModeBtn').classList.remove('btn-primary');
        document.getElementById('cameraModeBtn').classList.add('btn-light');
        
        // Hide loading indicator when switching modes
        const loadingIndicator = document.getElementById('imageLoadingIndicator');
        if (loadingIndicator) loadingIndicator.style.display = 'none';
        
        // Stop camera if running
        if (cameraStream) {
            stopCamera();
        }
    }

    function switchToCameraMode() {
        document.getElementById('uploadMode').style.display = 'none';
        document.getElementById('cameraMode').style.display = 'block';
        document.getElementById('cameraModeBtn').classList.remove('btn-light');
        document.getElementById('cameraModeBtn').classList.add('btn-primary');
        document.getElementById('uploadModeBtn').classList.remove('btn-primary');
        document.getElementById('uploadModeBtn').classList.add('btn-light');
        
        // Clear file input
        document.getElementById('imageUpload').value = '';
    }

    function startCamera() {
        const video = document.getElementById('cameraVideo');
        const startBtn = document.getElementById('startCameraBtn');
        const controls = document.getElementById('cameraControls');
        const loadingIndicator = document.getElementById('imageLoadingIndicator');
        const loadingText = document.getElementById('imageLoadingText');
        
        // Show loading indicator while initializing camera
        if (loadingIndicator) {
            loadingIndicator.style.display = 'block';
            if (loadingText) loadingText.textContent = 'Initializing camera...';
        }
        startBtn.disabled = true;
        startBtn.textContent = 'Starting camera...';
        
        navigator.mediaDevices.getUserMedia({ 
            video: { 
                facingMode: 'environment' // Use back camera on mobile if available
            } 
        })
        .then(function(stream) {
            cameraStream = stream;
            video.srcObject = stream;
            video.style.display = 'block';
            startBtn.style.display = 'none';
            controls.style.display = 'block';
            
            // Hide loading indicator when camera is ready
            if (loadingIndicator) loadingIndicator.style.display = 'none';
        })
        .catch(function(error) {
            console.error('Error accessing camera:', error);
            alert('Unable to access camera. Please check permissions or use upload instead.');
            
            // Hide loading indicator on error
            if (loadingIndicator) loadingIndicator.style.display = 'none';
            startBtn.disabled = false;
            startBtn.textContent = 'Start Camera';
            switchToUploadMode();
        });
    }

    function stopCamera() {
        const video = document.getElementById('cameraVideo');
        const startBtn = document.getElementById('startCameraBtn');
        const controls = document.getElementById('cameraControls');
        
        if (cameraStream) {
            cameraStream.getTracks().forEach(track => track.stop());
            cameraStream = null;
        }
        
        video.srcObject = null;
        video.style.display = 'none';
        startBtn.style.display = 'block';
        controls.style.display = 'none';
    }

    function capturePhoto() {
        const video = document.getElementById('cameraVideo');
        const canvas = document.getElementById('cameraCanvas');
        const imageUpload = document.getElementById('imageUpload');
        const loadingIndicator = document.getElementById('imageLoadingIndicator');
        const loadingText = document.getElementById('imageLoadingText');
        
        // Show loading indicator
        if (loadingIndicator) {
            loadingIndicator.style.display = 'block';
            if (loadingText) loadingText.textContent = 'Capturing photo...';
        }
        
        // Set canvas dimensions to match video
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        // Draw video frame to canvas
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        // Convert canvas to blob and create a File object
        canvas.toBlob(function(blob) {
            if (!blob) {
                // Hide loading indicator on error
                if (loadingIndicator) loadingIndicator.style.display = 'none';
                alert('Failed to capture photo. Please try again.');
                return;
            }
            
            // Create a File object from the blob
            const timestamp = new Date().getTime();
            const file = new File([blob], 'camera-photo-' + timestamp + '.jpg', { type: 'image/jpeg' });
            
            // Add the captured file to allSelectedFiles array
            allSelectedFiles.push(file);
            
            // Update the file input with all files
            const dataTransfer = new DataTransfer();
            allSelectedFiles.forEach(f => dataTransfer.items.add(f));
            imageUpload.files = dataTransfer.files;
            
            // Show preview with all images (this will handle the loading indicator)
            previewImages(imageUpload);
            
            // Don't stop camera - allow multiple captures
            // User can manually stop camera or switch modes
        }, 'image/jpeg', 0.95);
    }

    // Signature Pad functionality
    let signatureCanvas, signatureCtx, isDrawing = false;
    let lastX = 0, lastY = 0;

    function initSignaturePad() {
        signatureCanvas = document.getElementById('signatureCanvas');
        if (!signatureCanvas) return;
        
        signatureCtx = signatureCanvas.getContext('2d');
        signatureCtx.strokeStyle = '#000000';
        signatureCtx.lineWidth = 2;
        signatureCtx.lineCap = 'round';
        signatureCtx.lineJoin = 'round';

        // Mouse events
        signatureCanvas.addEventListener('mousedown', startDrawing);
        signatureCanvas.addEventListener('mousemove', draw);
        signatureCanvas.addEventListener('mouseup', stopDrawing);
        signatureCanvas.addEventListener('mouseout', stopDrawing);

        // Touch events for mobile
        signatureCanvas.addEventListener('touchstart', handleTouch);
        signatureCanvas.addEventListener('touchmove', handleTouch);
        signatureCanvas.addEventListener('touchend', stopDrawing);
    }

    function startDrawing(e) {
        isDrawing = true;
        const rect = signatureCanvas.getBoundingClientRect();
        lastX = (e.clientX || e.touches[0].clientX) - rect.left;
        lastY = (e.clientY || e.touches[0].clientY) - rect.top;
    }

    function draw(e) {
        if (!isDrawing) return;
        e.preventDefault();
        
        const rect = signatureCanvas.getBoundingClientRect();
        const currentX = (e.clientX || e.touches[0].clientX) - rect.left;
        const currentY = (e.clientY || e.touches[0].clientY) - rect.top;

        signatureCtx.beginPath();
        signatureCtx.moveTo(lastX, lastY);
        signatureCtx.lineTo(currentX, currentY);
        signatureCtx.stroke();

        lastX = currentX;
        lastY = currentY;
        
        // Save signature to hidden input
        saveSignature();
    }

    function handleTouch(e) {
        e.preventDefault();
        const touch = e.touches[0];
        const mouseEvent = new MouseEvent(e.type === 'touchstart' ? 'mousedown' : 
                                          e.type === 'touchmove' ? 'mousemove' : 'mouseup', {
            clientX: touch.clientX,
            clientY: touch.clientY
        });
        signatureCanvas.dispatchEvent(mouseEvent);
    }

    function stopDrawing() {
        if (isDrawing) {
            isDrawing = false;
            saveSignature();
        }
    }

    function clearSignature() {
        if (signatureCtx) {
            signatureCtx.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height);
            document.getElementById('signatureInput').value = '';
        }
    }

    function saveSignature() {
        if (signatureCanvas) {
            const dataURL = signatureCanvas.toDataURL('image/png');
            document.getElementById('signatureInput').value = dataURL;
        }
    }

    // Driver Signature Pad
    let driverSignatureCanvas = null;
    let driverSignatureCtx = null;
    let isDriverDrawing = false;

    function initDriverSignaturePad() {
        driverSignatureCanvas = document.getElementById('driverSignatureCanvas');
        if (!driverSignatureCanvas) return;
        
        driverSignatureCtx = driverSignatureCanvas.getContext('2d');
        driverSignatureCtx.strokeStyle = '#000';
        driverSignatureCtx.lineWidth = 2;
        driverSignatureCtx.lineCap = 'round';
        driverSignatureCtx.lineJoin = 'round';

        // Mouse events
        driverSignatureCanvas.addEventListener('mousedown', startDriverDrawing);
        driverSignatureCanvas.addEventListener('mousemove', drawDriver);
        driverSignatureCanvas.addEventListener('mouseup', stopDriverDrawing);
        driverSignatureCanvas.addEventListener('mouseout', stopDriverDrawing);

        // Touch events
        driverSignatureCanvas.addEventListener('touchstart', handleDriverTouch);
        driverSignatureCanvas.addEventListener('touchmove', handleDriverTouch);
        driverSignatureCanvas.addEventListener('touchend', stopDriverDrawing);
    }

    function startDriverDrawing(e) {
        isDriverDrawing = true;
        const rect = driverSignatureCanvas.getBoundingClientRect();
        const x = (e.clientX || e.touches[0].clientX) - rect.left;
        const y = (e.clientY || e.touches[0].clientY) - rect.top;
        driverSignatureCtx.beginPath();
        driverSignatureCtx.moveTo(x, y);
    }

    function drawDriver(e) {
        if (!isDriverDrawing) return;
        e.preventDefault();
        const rect = driverSignatureCanvas.getBoundingClientRect();
        const x = (e.clientX || e.touches[0].clientX) - rect.left;
        const y = (e.clientY || e.touches[0].clientY) - rect.top;
        driverSignatureCtx.lineTo(x, y);
        driverSignatureCtx.stroke();
        saveDriverSignature();
    }

    function handleDriverTouch(e) {
        e.preventDefault();
        const touch = e.touches[0] || e.changedTouches[0];
        const mouseEvent = new MouseEvent(e.type === 'touchstart' ? 'mousedown' : e.type === 'touchmove' ? 'mousemove' : 'mouseup', {
            clientX: touch.clientX,
            clientY: touch.clientY
        });
        driverSignatureCanvas.dispatchEvent(mouseEvent);
    }

    function stopDriverDrawing() {
        if (isDriverDrawing) {
            isDriverDrawing = false;
            saveDriverSignature();
        }
    }

    function clearDriverSignature() {
        if (driverSignatureCtx) {
            driverSignatureCtx.clearRect(0, 0, driverSignatureCanvas.width, driverSignatureCanvas.height);
            document.getElementById('driverSignatureInput').value = '';
        }
    }

    function saveDriverSignature() {
        if (driverSignatureCanvas) {
            const dataURL = driverSignatureCanvas.toDataURL('image/png');
            document.getElementById('driverSignatureInput').value = dataURL;
        }
    }

    // Initialize signature pad when page loads
    document.addEventListener('DOMContentLoaded', function() {
        initSignaturePad();
        initDriverSignaturePad();
    });

    // Handle "Other" violation checkbox and text field interaction
    document.addEventListener('DOMContentLoaded', function() {
        const otherCheckbox = document.querySelector('input[name="violations[]"][value="Other"]');
        const othersTextInput = document.querySelector('input[name="violations_others_text"]');
        const ticketForm = document.getElementById('ticketForm');

        // When user types in "Other" violation text, automatically check the "Other" checkbox
        if (othersTextInput) {
            othersTextInput.addEventListener('input', function() {
                if (this.value.trim() && otherCheckbox) {
                    otherCheckbox.checked = true;
                }
            });
        }

        // Before form submission, ensure "Other" is in violations array if checkbox is checked or text exists
        if (ticketForm) {
            ticketForm.addEventListener('submit', function(e) {
                const otherText = othersTextInput ? othersTextInput.value.trim() : '';
                const isOtherChecked = otherCheckbox ? otherCheckbox.checked : false;
                
                // If "Other" checkbox is checked or there's text, ensure "Other" is in the violations array
                if ((isOtherChecked || otherText) && otherCheckbox) {
                    otherCheckbox.checked = true;
                }
                
                // Show loading indicator during form submission
                const loadingIndicator = document.getElementById('imageLoadingIndicator');
                const loadingText = document.getElementById('imageLoadingText');
                const submitBtn = document.getElementById('submitBtn');
                
                if (loadingIndicator) {
                    loadingIndicator.style.display = 'block';
                    if (loadingText) loadingText.textContent = 'Uploading ticket and images...';
                }
                
                // Disable submit button to prevent double submission
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Uploading...';
                }
            });
        }
    });

    // View Modal functionality
    let viewModal, closeViewBtn, viewModalBody;
    let currentTicketId = null;
    let currentTicketData = null;

    // Initialize view modal elements when DOM is ready
    function initViewModal() {
        viewModal = document.getElementById('viewModal');
        closeViewBtn = document.getElementById('closeViewModal');
        viewModalBody = document.getElementById('viewModalBody');
        
        if (!viewModal || !viewModalBody) {
            console.error('View modal elements not found');
            return false;
        }
        
        if (closeViewBtn) {
            closeViewBtn.addEventListener('click', closeViewModal);
        }
        
        if (viewModal) {
            viewModal.addEventListener('click', (e) => {
                if (e.target === viewModal) closeViewModal();
            });
        }
        
        return true;
    }

    function openViewModal() {
        if (!viewModal) {
            if (!initViewModal()) {
                console.error('View modal element not found');
                return;
            }
        }
        viewModal.classList.add('open');
        viewModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeViewModal() {
        viewModal.classList.remove('open');
        viewModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        viewModalBody.innerHTML = '<div style="text-align: center; padding: 40px;"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div><p style="margin-top: 12px; color: #6b7280;">Loading ticket details...</p></div>';
        currentTicketId = null;
        currentTicketData = null;
    }

    // Initialize view modal on page load
    document.addEventListener('DOMContentLoaded', function() {
        initViewModal();
    });

    // Function to open view modal by ticket ID (called from onclick)
    async function openViewModalById(ticketId) {
        if (!ticketId) {
            console.error('No ticket ID provided');
            return;
        }
        
        // Ensure modal is initialized
        if (!viewModal || !viewModalBody) {
            if (!initViewModal()) {
                alert('View modal not available. Please refresh the page.');
                return;
            }
        }
        
        currentTicketId = ticketId;
        openViewModal();
        
        // Show loading state
        if (viewModalBody) {
            viewModalBody.innerHTML = '<div style="text-align: center; padding: 40px;"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div><p style="margin-top: 12px; color: #6b7280;">Loading ticket details...</p></div>';
        }
        
        try {
            const response = await fetch(`/admin/tickets/${ticketId}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`Failed to fetch ticket: ${response.status} ${errorText}`);
            }
            
            const ticket = await response.json();
            currentTicketData = ticket;
            displayTicketDetails(ticket);
        } catch (error) {
            console.error('Error fetching ticket:', error);
            if (viewModalBody) {
                viewModalBody.innerHTML = `
                    <div style="text-align: center; padding: 40px;">
                        <p style="color: #e53935;">Error loading ticket details: ${error.message}</p>
                        <button type="button" class="btn btn-light" onclick="closeViewModal()" style="margin-top: 12px;">Close</button>
                    </div>
                `;
            }
        }
    }

    // Handle View button clicks - Use event delegation for dynamically loaded content (fallback)
    document.addEventListener('click', async function(e) {
        const viewBtn = e.target.closest('.view-ticket-btn');
        if (viewBtn && !viewBtn.onclick) {
            e.preventDefault();
            e.stopPropagation();
            
            const ticketId = viewBtn.getAttribute('data-ticket-id');
            if (ticketId) {
                openViewModalById(ticketId);
            }
        }
    });

    function displayTicketDetails(ticket) {
        const violations = ticket.violations ? ticket.violations.join(', ') : 'N/A';
        const violationsDisplay = violations + (ticket.violations_others_text ? ` (Other: ${ticket.violations_others_text})` : '');
        
        const issuedDate = ticket.issued_date ? new Date(ticket.issued_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A';
        const courtDate = ticket.court_date ? new Date(ticket.court_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A';
        
        viewModalBody.innerHTML = `
            <div class="container-fluid">
                <h6 class="text-primary">Issuance Details</h6>
                <div class="row mb-3" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px;">
                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #6b7280;">Citation / Ticket #</label>
                        <p style="margin: 4px 0 0 0; font-size: 14px;">${ticket.citation_number || 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #6b7280;">Date</label>
                        <p style="margin: 4px 0 0 0; font-size: 14px;">${issuedDate}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #6b7280;">Time</label>
                        <p style="margin: 4px 0 0 0; font-size: 14px;">${ticket.issued_time || 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #6b7280;">Issued By</label>
                        <p style="margin: 4px 0 0 0; font-size: 14px;">${ticket.issued_by || 'N/A'}</p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #6b7280;">Place of Violation</label>
                        <p style="margin: 4px 0 0 0; font-size: 14px;">${ticket.place || 'N/A'}</p>
                    </div>
                </div>

                <hr style="margin: 16px 0;">

                <h6 class="text-primary">Driver Details</h6>
                <div class="row mb-3" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;">
                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #6b7280;">Last Name</label>
                        <p style="margin: 4px 0 0 0; font-size: 14px;">${ticket.driver_lastname || 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #6b7280;">First Name</label>
                        <p style="margin: 4px 0 0 0; font-size: 14px;">${ticket.driver_firstname || 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #6b7280;">Middle Name</label>
                        <p style="margin: 4px 0 0 0; font-size: 14px;">${ticket.driver_middlename || 'N/A'}</p>
                    </div>
                </div>
                <div class="row mb-3" style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #6b7280;">Driver's Address</label>
                        <p style="margin: 4px 0 0 0; font-size: 14px;">${ticket.driver_address || 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #6b7280;">Driver's License #</label>
                        <p style="margin: 4px 0 0 0; font-size: 14px;">${ticket.dl_number || 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #6b7280;">Contact #</label>
                        <p style="margin: 4px 0 0 0; font-size: 14px;">${ticket.driver_contact || 'N/A'}</p>
                    </div>
                </div>

                <hr style="margin: 16px 0;">

                <h6 class="text-primary">Vehicle & Owner Details</h6>
                <div class="row mb-3" style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px;">
                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #6b7280;">Plate #</label>
                        <p style="margin: 4px 0 0 0; font-size: 14px;">${ticket.plate_number || 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #6b7280;">CR #</label>
                        <p style="margin: 4px 0 0 0; font-size: 14px;">${ticket.cr_number || 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #6b7280;">Year</label>
                        <p style="margin: 4px 0 0 0; font-size: 14px;">${ticket.vehicle_year || 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #6b7280;">Make</label>
                        <p style="margin: 4px 0 0 0; font-size: 14px;">${ticket.vehicle_make || 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #6b7280;">Model</label>
                        <p style="margin: 4px 0 0 0; font-size: 14px;">${ticket.vehicle_model || 'N/A'}</p>
                    </div>
                </div>
                <div class="row mb-3" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #6b7280;">Owner's Name</label>
                        <p style="margin: 4px 0 0 0; font-size: 14px;">${ticket.owner_name || 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #6b7280;">Owner's Address</label>
                        <p style="margin: 4px 0 0 0; font-size: 14px;">${ticket.owner_address || 'N/A'}</p>
                    </div>
                </div>

                <hr style="margin: 16px 0;">

                <h6 class="text-primary">Violation(s)</h6>
                <div class="row mb-3" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #6b7280;">Violations</label>
                        <p style="margin: 4px 0 0 0; font-size: 14px;">${violationsDisplay}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #6b7280;">Incident Notes</label>
                        <p style="margin: 4px 0 0 0; font-size: 14px; white-space: pre-wrap;">${ticket.incident_notes || 'N/A'}</p>
                    </div>
                </div>
                <div class="row mb-3">
                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #6b7280;">Remarks</label>
                        <p style="margin: 4px 0 0 0; font-size: 14px; white-space: pre-wrap;">${ticket.remarks || 'N/A'}</p>
                    </div>
                </div>

                <hr style="margin: 16px 0;">

                ${(ticket.image_urls && ticket.image_urls.length > 0) || ticket.image_url || ticket.image ? `
                <h6 class="text-primary">Evidence / Photo</h6>
                <div class="row mb-3">
                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #6b7280;">Uploaded Images</label>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px; margin-top: 8px;">
                            ${(() => {
                                // Handle multiple images (new format)
                                if (ticket.image_urls && Array.isArray(ticket.image_urls) && ticket.image_urls.length > 0) {
                                    return ticket.image_urls.map((url, idx) => `
                                        <div style="border: 2px solid #d1d5db; border-radius: 8px; background: #fff; padding: 8px; position: relative;">
                                            <img src="${url}" alt="Evidence Photo ${idx + 1}" style="width: 100%; height: 200px; object-fit: cover; border-radius: 4px; display: block;" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='block';">
                                            <p style="color: #e53935; padding: 20px; text-align: center; display: none; margin: 0; font-size: 12px;">Image ${idx + 1} not found</p>
                                        </div>
                                    `).join('');
                                }
                                // Fallback for single image (old format)
                                else if (ticket.image_url || ticket.image) {
                                    const imgUrl = ticket.image_url || (ticket.image ? '/storage/' + ticket.image : '');
                                    return `
                                        <div style="border: 2px solid #d1d5db; border-radius: 8px; background: #fff; padding: 8px;">
                                            <img src="${imgUrl}" alt="Evidence Photo" style="width: 100%; height: 200px; object-fit: cover; border-radius: 4px; display: block;" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='block';">
                                            <p style="color: #e53935; padding: 20px; text-align: center; display: none; margin: 0; font-size: 12px;">Image not found</p>
                                        </div>
                                    `;
                                }
                                return '';
                            })()}
                        </div>
                    </div>
                </div>
                <hr style="margin: 16px 0;">
                ` : ''}

                <h6 class="text-primary">Officer & Court Details</h6>
                <div class="row mb-3" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px;">
                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #6b7280;">Court Date</label>
                        <p style="margin: 4px 0 0 0; font-size: 14px;">${courtDate}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #6b7280;">Court Time</label>
                        <p style="margin: 4px 0 0 0; font-size: 14px;">${ticket.court_time || 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #6b7280;">Apprehending Officer</label>
                        <p style="margin: 4px 0 0 0; font-size: 14px;">${ticket.apprehending_officer || 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #6b7280;">TOMECO DID</label>
                        <p style="margin: 4px 0 0 0; font-size: 14px;">${ticket.tomeco_did || 'N/A'}</p>
                    </div>
                </div>

                ${ticket.signature ? `
                <hr style="margin: 16px 0;">
                <h6 class="text-primary">E-Signature</h6>
                <div class="row mb-3">
                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #6b7280;">Officer Signature</label>
                        <div style="border: 2px solid #d1d5db; border-radius: 8px; background: #fff; padding: 12px; margin-top: 8px;">
                            <img src="${ticket.signature}" alt="Signature" style="max-width: 100%; max-height: 200px; border: 1px solid #e5e7eb; border-radius: 4px;">
                        </div>
                    </div>
                </div>
                ` : ''}
            </div>
            <div style="display:flex;justify-content:space-between;gap:8px;margin-top:20px; border-top: 1px solid #eee; padding-top: 16px;">
                <div>
                    <button type="button" class="btn btn-danger" onclick="confirmDeleteTicket(${ticket.id})">Delete</button>
                </div>
                <div style="display:flex;gap:8px;">
                    <button type="button" class="btn btn-light" onclick="printTicket(${ticket.id})" style="display: inline-flex; align-items: center; gap: 6px;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"></path>
                            <path d="M6 14h12v8H6z"></path>
                        </svg>
                        Print
                    </button>
                    <button type="button" class="btn btn-light" onclick="closeViewModal()">Close</button>
                    <button type="button" class="btn btn-primary" onclick="openEditModal(${ticket.id})">Edit</button>
                </div>
            </div>
        `;
    }

    // Edit Modal functionality
    function openEditModal(ticketId) {
        if (!ticketId && !currentTicketId) return;
        const id = ticketId || currentTicketId;
        
        // Close view modal
        closeViewModal();
        
        // Fetch ticket data and populate form
        fetch(`/admin/tickets/${id}`)
            .then(response => response.json())
            .then(ticket => {
                // Update modal title
                document.getElementById('createModalTitle').textContent = 'Edit Traffic Ticket';
                
                // Update form action and method
                const form = document.getElementById('ticketForm');
                form.action = `/admin/tickets/${id}`;
                document.getElementById('formMethod').value = 'PUT';
                document.getElementById('ticketId').value = id;
                
                // Helper function to format time (remove seconds if present)
                function formatTime(timeStr) {
                    if (!timeStr) return '';
                    // If time has seconds (HH:MM:SS), remove them
                    if (timeStr.length > 5) {
                        return timeStr.substring(0, 5);
                    }
                    return timeStr;
                }
                
                // Populate form fields
                document.querySelector('input[name="citation_number"]').value = ticket.citation_number || '';
                document.querySelector('input[name="issued_date"]').value = ticket.issued_date ? ticket.issued_date.split('T')[0] : '';
                document.querySelector('input[name="issued_time"]').value = formatTime(ticket.issued_time);
                document.querySelector('input[name="issued_by"]').value = ticket.issued_by || '';
                document.querySelector('input[name="place"]').value = ticket.place || '';
                
                document.querySelector('input[name="driver_lastname"]').value = ticket.driver_lastname || '';
                document.querySelector('input[name="driver_firstname"]').value = ticket.driver_firstname || '';
                document.querySelector('input[name="driver_middlename"]').value = ticket.driver_middlename || '';
                document.querySelector('input[name="driver_address"]').value = ticket.driver_address || '';
                document.querySelector('input[name="dl_number"]').value = ticket.dl_number || '';
                document.querySelector('input[name="driver_contact"]').value = ticket.driver_contact || '';
                
                document.querySelector('input[name="plate_number"]').value = ticket.plate_number || '';
                document.querySelector('input[name="cr_number"]').value = ticket.cr_number || '';
                document.querySelector('input[name="vehicle_year"]').value = ticket.vehicle_year || '';
                document.querySelector('input[name="vehicle_make"]').value = ticket.vehicle_make || '';
                document.querySelector('input[name="vehicle_model"]').value = ticket.vehicle_model || '';
                document.querySelector('input[name="vehicle_type"]').value = ticket.vehicle_type || '';
                document.querySelector('input[name="or_number"]').value = ticket.or_number || '';
                document.querySelector('input[name="owner_name"]').value = ticket.owner_name || '';
                document.querySelector('input[name="owner_address"]').value = ticket.owner_address || '';
                
                // Handle driver license type radio buttons
                if (ticket.dl_type) {
                    const dlTypeRadio = document.querySelector(`input[name="dl_type"][value="${ticket.dl_type}"]`);
                    if (dlTypeRadio) {
                        dlTypeRadio.checked = true;
                    }
                }
                
                // Handle accident radio buttons
                if (ticket.accident !== null && ticket.accident !== undefined) {
                    const accidentRadio = document.querySelector(`input[name="accident"][value="${ticket.accident ? '1' : '0'}"]`);
                    if (accidentRadio) {
                        accidentRadio.checked = true;
                    }
                }
                
                // Handle admitted/under protest radio buttons
                if (ticket.admitted_or_protest) {
                    const admittedRadio = document.querySelector(`input[name="admitted_or_protest"][value="${ticket.admitted_or_protest}"]`);
                    if (admittedRadio) {
                        admittedRadio.checked = true;
                    }
                }
                
                // Handle violations checkboxes
                const violations = ticket.violations || [];
                document.querySelectorAll('input[name="violations[]"]').forEach(checkbox => {
                    checkbox.checked = violations.includes(checkbox.value);
                });
                
                // If there's violations_others_text, ensure "Other" checkbox is checked
                const otherCheckbox = document.querySelector('input[name="violations[]"][value="Other"]');
                const othersText = ticket.violations_others_text || '';
                if (othersText && otherCheckbox) {
                    otherCheckbox.checked = true;
                }
                
                document.querySelector('input[name="violations_others_text"]').value = othersText;
                
                document.querySelector('textarea[name="incident_notes"]').value = ticket.incident_notes || '';
                document.querySelector('textarea[name="remarks"]').value = ticket.remarks || '';
                
                document.querySelector('input[name="court_date"]').value = ticket.court_date ? ticket.court_date.split('T')[0] : '';
                document.querySelector('input[name="court_time"]').value = formatTime(ticket.court_time);
                document.querySelector('input[name="apprehending_officer"]').value = ticket.apprehending_officer || '';
                document.querySelector('input[name="tomeco_did"]').value = ticket.tomeco_did || '';
                
                // Load officer signature if exists
                if (ticket.signature) {
                    const signatureInput = document.getElementById('signatureInput');
                    signatureInput.value = ticket.signature;
                    // Draw signature on canvas
                    const img = new Image();
                    img.onload = function() {
                        if (signatureCanvas && signatureCtx) {
                            signatureCtx.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height);
                            signatureCtx.drawImage(img, 0, 0, signatureCanvas.width, signatureCanvas.height);
                        }
                    };
                    img.src = ticket.signature;
                } else {
                    clearSignature();
                }
                
                // Load driver signature if exists
                if (ticket.driver_signature) {
                    const driverSignatureInput = document.getElementById('driverSignatureInput');
                    driverSignatureInput.value = ticket.driver_signature;
                    // Draw driver signature on canvas
                    const driverImg = new Image();
                    driverImg.onload = function() {
                        if (driverSignatureCanvas && driverSignatureCtx) {
                            driverSignatureCtx.clearRect(0, 0, driverSignatureCanvas.width, driverSignatureCanvas.height);
                            driverSignatureCtx.drawImage(driverImg, 0, 0, driverSignatureCanvas.width, driverSignatureCanvas.height);
                        }
                    };
                    driverImg.src = ticket.driver_signature;
                } else {
                    clearDriverSignature();
                }
                
                // Load existing images if they exist
                if (ticket.image_urls && Array.isArray(ticket.image_urls) && ticket.image_urls.length > 0) {
                    displayExistingImages(ticket.image_urls, ticket.images || []);
                } else if (ticket.image_url || ticket.image) {
                    const imgUrl = ticket.image_url || (ticket.image ? '/storage/' + ticket.image : '');
                    const imgPath = ticket.image || extractStoragePath(imgUrl);
                    displayExistingImages([imgUrl], [imgPath]);
                } else {
                    // Clear image preview if no images
                    document.getElementById('imagePreview').style.display = 'none';
                    document.getElementById('existingImagesWrapper').innerHTML = '';
                    document.getElementById('newImagesWrapper').innerHTML = '';
                    existingImagesMeta = [];
                }
                updateExistingImagesInput();
                
                // Update submit button text
                document.getElementById('submitBtn').textContent = 'Update Ticket';
                
                // Open create modal (which is now in edit mode)
                openModal(true);
            })
            .catch(error => {
                console.error('Error fetching ticket:', error);
                alert('Error loading ticket data. Please try again.');
            });
    }

    // Image Modal functionality
    const imageModal = document.getElementById('imageModal');
    const closeImageBtn = document.getElementById('closeImageModal');
    const imageModalImg = document.getElementById('imageModalImg');

    function openImageModal(imageUrl) {
        if (imageModal && imageModalImg) {
            imageModalImg.src = imageUrl;
            imageModal.classList.add('open');
            imageModal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeImageModal() {
        if (imageModal) {
            imageModal.classList.remove('open');
            imageModal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            if (imageModalImg) {
                imageModalImg.src = '';
            }
        }
    }

    closeImageBtn?.addEventListener('click', closeImageModal);
    imageModal?.addEventListener('click', (e) => {
        if (e.target === imageModal) closeImageModal();
    });

    // Close image modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && imageModal && imageModal.classList.contains('open')) {
            closeImageModal();
        }
    });

    // Print Ticket functionality
    function printTicket(ticketId) {
        const id = ticketId || currentTicketId;
        if (!id) return;
        
        // Redirect to print page
        window.location.href = `/admin/tickets/${id}/print`;
    }

    // Delete functionality
    function confirmDeleteTicket(ticketId) {
        const id = ticketId || currentTicketId;
        if (!id) return;
        
        if (confirm('Are you sure you want to delete this ticket? This action cannot be undone.')) {
            // Create a form to submit DELETE request
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/tickets/${id}`;
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                             document.querySelector('input[name="_token"]')?.value;
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);
            
            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = '_token';
            tokenInput.value = csrfToken;
            form.appendChild(tokenInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    }

    // If there are validation errors from server and page reloaded,
    // automatically open modal so users can see the errors.
    @if ($errors->any())
        setTimeout(()=>{ openModal(); }, 80);
    @endif
</script>
@endsection