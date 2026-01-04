{{-- resources/views/violator-portal.blade.php --}}
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">

  <title>TOMECO Violator Portal</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
  
  {{-- CSRF Token for AJAX requests --}}
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <style>
    :root{
      --nav-h: 58px;           /* keep in sync with your navbar */
      --tomeco-red:#962e2e;
    }

    html, body { height:100%; }
    body{
      margin:0;
      font-family: 'Nunito', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
      /* soft overlay + image bg */
      background:
        linear-gradient(rgba(255,255,255,.75), rgba(255,255,255,.75)),
        url('{{ asset('assets/bg.jpg') }}') no-repeat center/cover;
      display:flex;
      flex-direction:column;
    }

    /* Navbar-aware hero wrapper */
    .main-hero{
      /* reserve space below fixed navbar */
      padding-top: var(--nav-h);

      /* fill the visible viewport minus the navbar */
      min-height: calc(100vh  - var(--nav-h));
      min-height: calc(100svh - var(--nav-h)); /* mobile-safe */

      /* center the inner hero */
      display:flex;
      align-items:center;
      justify-content:center;
    }

    .container { 
      width:100%; 
      max-width:1100px; 
      margin:0 auto; 
      padding:0 16px; 
    }
    
    @media (max-width:640px){
      .container{
        padding:0 12px;
      }
    }

    .hero{
      width:100%;
      display:flex;
      flex-direction:column;
      align-items:center;
      text-align:center;
      padding:32px 16px;
    }

    .hero h2{
      margin:0 0 16px;
      font-size:1.15rem; font-weight:700; letter-spacing:.5px; color:#111827;
    }

    .hero img{
      width:170px; height:170px; object-fit:contain; margin:10px 0 14px;
      filter: drop-shadow(0 4px 10px rgba(0,0,0,.15));
    }

    .hero h1{
      margin:10px 0 0;
      font-size:1.6rem; font-weight:900; color:#111827;
    }

    .search-form{
      width:100%;
      max-width:500px;
      margin:26px 0 40px;
      position:relative;
      box-sizing:border-box;
    }

    .search-input-wrapper{
      position:relative;
      width:100%;
      box-sizing:border-box;
      max-width:100%;
    }

    .search-input{
      width:100%;
      padding:12px 120px 12px 16px;
      border:2px solid #e5e7eb;
      border-radius:10px;
      font-size:1rem;
      transition:border-color .2s ease;
      box-sizing:border-box;
      -webkit-appearance:none;
      appearance:none;
      min-height:44px;
    }

    .search-input:focus{
      outline:none;
      border-color:#dc2626;
    }

    .search-btn{
      position:absolute;
      right:4px;
      top:50%;
      transform:translateY(-50%);
      background:#dc2626; 
      color:#fff; 
      font-weight:700;
      padding:10px 24px; 
      border-radius:8px;
      border:none;
      cursor:pointer;
      transition:opacity .2s ease, transform .08s ease;
      font-size:0.95rem;
      height:calc(100% - 8px);
    }
    .search-btn:hover{ opacity:.95; }
    .search-btn:active{ transform:translateY(-50%) translateY(1px); }

    .error-message{
      color:#dc2626;
      font-size:0.9rem;
      margin-top:8px;
    }

    .success-message{
      color:#059669;
      font-size:0.9rem;
      margin-top:8px;
    }

    /* Modal Styles */
    .modal-overlay{
      position:fixed;
      top:0;
      left:0;
      right:0;
      bottom:0;
      background:rgba(0,0,0,.6);
      display:flex;
      align-items:center;
      justify-content:center;
      z-index:1000;
      padding:20px;
      animation:fadeIn .2s ease;
      overflow-y:auto;
    }

    @keyframes fadeIn{
      from{ opacity:0; }
      to{ opacity:1; }
    }

    @keyframes slideUp{
      from{ 
        opacity:0;
        transform:translateY(20px);
      }
      to{ 
        opacity:1;
        transform:translateY(0);
      }
    }

    .ticket-details{
      width:100%;
      max-width:900px;
      max-height:90vh;
      background:#fff;
      border-radius:12px;
      padding:0;
      box-shadow:0 10px 25px rgba(0,0,0,.2);
      text-align:left;
      overflow:hidden;
      display:flex;
      flex-direction:column;
      animation:slideUp .3s ease;
      margin:auto;
    }

    .ticket-details-header{
      background:#dc2626;
      color:#fff;
      padding:20px 24px;
      display:flex;
      justify-content:space-between;
      align-items:center;
      flex-shrink:0;
    }

    .ticket-details-header h3{
      margin:0;
      font-size:1.5rem;
      font-weight:700;
      color:#fff;
      flex:1;
    }

    .ticket-details-actions{
      display:flex;
      gap:8px;
      align-items:center;
    }

    .action-btn{
      background:rgba(255,255,255,.15);
      border:none;
      color:#fff;
      font-size:0.9rem;
      cursor:pointer;
      padding:8px 16px;
      border-radius:6px;
      transition:background .2s ease;
      display:flex;
      align-items:center;
      gap:6px;
      font-weight:600;
    }

    .action-btn:hover{
      background:rgba(255,255,255,.25);
    }

    .action-btn svg{
      width:16px;
      height:16px;
    }

    .gcash-btn{
      background:rgba(0,207,53,.2) !important; /* GCash green color */
    }

    .gcash-btn:hover{
      background:rgba(0,207,53,.3) !important;
    }

    .close-modal-btn{
      background:transparent;
      border:none;
      color:#fff;
      font-size:1.5rem;
      cursor:pointer;
      padding:0;
      width:32px;
      height:32px;
      display:flex;
      align-items:center;
      justify-content:center;
      border-radius:6px;
      transition:background .2s ease;
    }

    .close-modal-btn:hover{
      background:rgba(255,255,255,.1);
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
        background: white;
        padding: 20px;
      }
      .ticket-details-header, .action-btn, .close-modal-btn {
        display: none !important;
      }
    }

    .ticket-details-content{
      padding:24px;
      overflow-y:auto;
      overflow-x:hidden;
      flex:1;
      min-height:0;
      -webkit-overflow-scrolling:touch;
    }


    .ticket-section{
      margin-bottom:24px;
    }

    .ticket-section h4{
      margin:0 0 12px;
      font-size:1.1rem;
      font-weight:600;
      color:#dc2626;
    }

    .ticket-row{
      display:grid;
      grid-template-columns:1fr;
      gap:12px;
      margin-bottom:12px;
    }
    
    /* Ensure fields don't overflow on mobile */
    .ticket-field[style*="grid-column"]{
      grid-column:1 / -1;
    }

    .ticket-field{
      display:flex;
      flex-direction:column;
      min-width:0;
      word-wrap:break-word;
    }

    .ticket-label{
      font-size:0.85rem;
      font-weight:600;
      color:#6b7280;
      margin-bottom:4px;
    }

    .ticket-value{
      font-size:1rem;
      color:#111827;
      padding:8px 12px;
      background:#f9fafb;
      border-radius:6px;
      word-wrap:break-word;
      overflow-wrap:break-word;
    }

    .ticket-image{
      max-width:100%;
      height:auto;
      border-radius:8px;
      margin-top:12px;
    }

    @media (min-width:640px){
      .hero h2{ font-size:1.25rem; }
      .hero h1{ font-size:1.9rem; }
      .hero img{ width:190px; height:190px; }
      .ticket-row{
        grid-template-columns:1fr 1fr;
      }
    }

    @media (max-width:640px){
      /* Main hero adjustments */
      .main-hero{
        padding-top: var(--nav-h);
        min-height: calc(100vh - var(--nav-h));
        min-height: calc(100svh - var(--nav-h));
      }
      
      .hero{
        padding:24px 12px;
      }
      
      .hero h2{
        font-size:0.95rem;
        margin-bottom:12px;
        padding:0 8px;
      }
      
      .hero img{
        width:140px;
        height:140px;
        margin:8px 0 10px;
      }
      
      .hero h1{
        font-size:1.3rem;
        margin:8px 0 0;
      }
      
      /* Search form adjustments */
      .search-form{
        max-width:100%;
        margin:20px 0 30px;
        padding:0 8px;
        box-sizing:border-box;
      }
      
      .search-input-wrapper{
        width:100%;
        box-sizing:border-box;
      }
      
      .search-input{
        padding:10px 85px 10px 12px;
        font-size:16px;
        border-radius:8px;
        width:100%;
        box-sizing:border-box;
        -webkit-appearance:none;
        appearance:none;
        min-height:44px;
        max-width:100%;
      }
      
      .search-input::placeholder{
        font-size:14px;
        opacity:0.7;
      }
      
      .search-btn{
        padding:8px 14px;
        font-size:0.8rem;
        right:3px;
        height:calc(100% - 6px);
        border-radius:6px;
        min-width:70px;
        box-sizing:border-box;
      }
      
      /* Modal adjustments for mobile */
      .modal-overlay{
        padding:8px;
        align-items:flex-start;
        padding-top:10px;
        padding-bottom:10px;
        -webkit-overflow-scrolling:touch;
      }
      
      .ticket-details{
        max-width:100%;
        width:100%;
        max-height:calc(100vh - 20px);
        max-height:calc(100svh - 20px);
        border-radius:8px;
        margin:auto;
        display:flex;
        flex-direction:column;
        box-shadow:0 4px 20px rgba(0,0,0,.3);
      }
      
      .ticket-details-header{
        padding:12px 12px;
        flex-wrap:wrap;
        border-radius:8px 8px 0 0;
        flex-shrink:0;
        position:sticky;
        top:0;
        z-index:10;
      }
      
      .ticket-details-header h3{
        font-size:1rem;
        margin-bottom:8px;
        width:100%;
        order:1;
        line-height:1.3;
      }
      
      .ticket-details-actions{
        width:100%;
        justify-content:space-between;
        order:2;
        gap:4px;
        flex-wrap:nowrap;
        overflow-x:auto;
        -webkit-overflow-scrolling:touch;
        scrollbar-width:none;
        -ms-overflow-style:none;
      }
      
      .ticket-details-actions::-webkit-scrollbar{
        display:none;
      }
      
      .action-btn{
        padding:7px 8px;
        font-size:0.7rem;
        flex:1 1 auto;
        min-width:60px;
        justify-content:center;
        white-space:nowrap;
        flex-shrink:0;
      }
      
      .action-btn svg{
        width:11px;
        height:11px;
        flex-shrink:0;
      }
      
      .close-modal-btn{
        width:32px;
        height:32px;
        font-size:1.4rem;
        order:3;
        margin-left:auto;
        flex-shrink:0;
        position:absolute;
        top:8px;
        right:8px;
      }
      
      .ticket-details-content{
        padding:14px 10px;
        overflow-y:auto;
        overflow-x:hidden;
        -webkit-overflow-scrolling:touch;
        flex:1;
        min-height:0;
      }
      
      .ticket-section{
        margin-bottom:16px;
        break-inside:avoid;
      }
      
      .ticket-section h4{
        font-size:0.95rem;
        margin-bottom:8px;
        line-height:1.3;
        word-wrap:break-word;
      }
      
      .ticket-row{
        gap:8px;
        margin-bottom:8px;
        grid-template-columns:1fr !important;
      }
      
      .ticket-field{
        width:100%;
        min-width:0;
        grid-column:1 !important;
      }
      
      .ticket-label{
        font-size:0.75rem;
        margin-bottom:3px;
        word-wrap:break-word;
        line-height:1.3;
      }
      
      .ticket-value{
        font-size:0.85rem;
        padding:8px 10px;
        word-wrap:break-word;
        overflow-wrap:break-word;
        line-height:1.4;
        min-height:auto;
      }
      
      .ticket-image{
        margin-top:8px;
        border-radius:6px;
        max-width:100%;
        height:auto;
        display:block;
      }
    }
    
    /* Extra small devices */
    @media (max-width:480px){
      .hero h2{
        font-size:0.85rem;
        line-height:1.4;
      }
      
      .hero img{
        width:120px;
        height:120px;
      }
      
      .hero h1{
        font-size:1.15rem;
      }
      
      .search-input{
        padding:9px 80px 9px 10px;
        font-size:16px;
        min-height:44px;
      }
      
      .search-input::placeholder{
        font-size:13px;
      }
      
      .search-btn{
        padding:7px 12px;
        font-size:0.75rem;
        min-width:65px;
        right:2px;
      }
      
      .ticket-details-header{
        padding:10px 10px;
      }
      
      .ticket-details-header h3{
        font-size:0.9rem;
        margin-bottom:6px;
      }
      
      .action-btn{
        padding:6px 6px;
        font-size:0.65rem;
        min-width:55px;
      }
      
      .action-btn svg{
        width:10px;
        height:10px;
      }
      
      .close-modal-btn{
        width:30px;
        height:30px;
        font-size:1.3rem;
        top:6px;
        right:6px;
      }
      
      .ticket-details-content{
        padding:12px 8px;
      }
      
      .ticket-section{
        margin-bottom:14px;
      }
      
      .ticket-section h4{
        font-size:0.9rem;
      }
      
      .ticket-label{
        font-size:0.7rem;
      }
      
      .ticket-value{
        font-size:0.8rem;
        padding:6px 8px;
      }
    }
  </style>
</head>
<body>

  {{-- Use your existing fixed navbar --}}
  @include('navbar.navbar')

  {{-- NEW: wrap content in a navbar-aware main --}}
  <main class="main-hero">
    <div class="container">
      <div class="hero">
        {{-- Full meaning on top --}}
        <h2>Traffic Operations Management, Enforcement &amp; Control Office</h2>

        {{-- Centered logo --}}
        <img src="{{ asset('assets/Logo.png') }}" alt="TOMECO Logo">

        {{-- Bold portal text at the bottom --}}
        <h1>VIOLATOR PORTAL</h1>

        {{-- Search form --}}
        <form class="search-form" action="{{ route('violator.portal.search') }}" method="GET">
          <div class="search-input-wrapper">
            <input 
              type="text" 
              name="citation_number" 
              class="search-input" 
              placeholder="Enter Citation Ticket Number"
              value="{{ request('citation_number') }}"
              required
            >
            <button type="submit" class="search-btn">Search</button>
          </div>
        </form>

        {{-- Display error/success messages --}}
        @if(session('error'))
          <div class="error-message">{{ session('error') }}</div>
        @endif

        @if(session('success'))
          <div class="success-message">{{ session('success') }}</div>
        @endif

        {{-- Display ticket details modal if found --}}
        @if(isset($ticket))
          <div class="modal-overlay" id="ticketModal" onclick="if(event.target === this) closeModal()">
            <div class="ticket-details print-ticket">
              <div class="ticket-details-header">
                <h3>Ticket Details</h3>
                <div class="ticket-details-actions">
                  <button class="action-btn gcash-btn" onclick="initiateGCashPayment()" title="Pay via GCash">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Pay via GCash
                  </button>
                  <button class="action-btn" onclick="exportToPDF(event)" title="Export to PDF">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    PDF
                  </button>
                  <button class="close-modal-btn" onclick="closeModal()" aria-label="Close">&times;</button>
                </div>
              </div>
              <div class="ticket-details-content">

            {{-- Citation Information --}}
            <div class="ticket-section">
              <h4>Citation Information</h4>
              <div class="ticket-row">
                <div class="ticket-field">
                  <span class="ticket-label">Citation Number</span>
                  <div class="ticket-value">{{ $ticket->citation_number ?? 'N/A' }}</div>
                </div>
                <div class="ticket-field">
                  <span class="ticket-label">Status</span>
                  <div class="ticket-value">
                    @if($ticket->status === 'Paid')
                      <span style="color: #10b981; font-weight: 600;">Paid</span>
                    @else
                      <span style="color: #ef4444; font-weight: 600;">Unpaid</span>
                    @endif
                  </div>
                </div>
                <div class="ticket-field">
                  <span class="ticket-label">Issued Date</span>
                  <div class="ticket-value">{{ $ticket->issued_date ? $ticket->issued_date->format('M d, Y') : 'N/A' }}</div>
                </div>
                <div class="ticket-field">
                  <span class="ticket-label">Issued Time</span>
                  <div class="ticket-value">{{ $ticket->issued_time ?? 'N/A' }}</div>
                </div>
                <div class="ticket-field">
                  <span class="ticket-label">Issued By</span>
                  <div class="ticket-value">{{ $ticket->issued_by ?? 'N/A' }}</div>
                </div>
              </div>
            </div>

            {{-- Driver Information --}}
            <div class="ticket-section">
              <h4>Driver Information</h4>
              <div class="ticket-row">
                <div class="ticket-field">
                  <span class="ticket-label">Name</span>
                  <div class="ticket-value">
                    {{ trim(($ticket->driver_firstname ?? '') . ' ' . ($ticket->driver_middlename ?? '') . ' ' . ($ticket->driver_lastname ?? '')) ?: 'N/A' }}
                  </div>
                </div>
                <div class="ticket-field">
                  <span class="ticket-label">License Number</span>
                  <div class="ticket-value">{{ $ticket->dl_number ?? 'N/A' }}</div>
                </div>
                <div class="ticket-field">
                  <span class="ticket-label">Address</span>
                  <div class="ticket-value">{{ $ticket->driver_address ?? 'N/A' }}</div>
                </div>
                <div class="ticket-field">
                  <span class="ticket-label">Contact</span>
                  <div class="ticket-value">{{ $ticket->driver_contact ?? 'N/A' }}</div>
                </div>
              </div>
            </div>

            {{-- Vehicle Information --}}
            <div class="ticket-section">
              <h4>Vehicle Information</h4>
              <div class="ticket-row">
                <div class="ticket-field">
                  <span class="ticket-label">Plate Number</span>
                  <div class="ticket-value">{{ $ticket->plate_number ?? 'N/A' }}</div>
                </div>
                <div class="ticket-field">
                  <span class="ticket-label">CR Number</span>
                  <div class="ticket-value">{{ $ticket->cr_number ?? 'N/A' }}</div>
                </div>
                <div class="ticket-field">
                  <span class="ticket-label">Vehicle</span>
                  <div class="ticket-value">
                    {{ trim(($ticket->vehicle_year ?? '') . ' ' . ($ticket->vehicle_make ?? '') . ' ' . ($ticket->vehicle_model ?? '')) ?: 'N/A' }}
                  </div>
                </div>
                <div class="ticket-field">
                  <span class="ticket-label">Owner Name</span>
                  <div class="ticket-value">{{ $ticket->owner_name ?? 'N/A' }}</div>
                </div>
              </div>
            </div>

            {{-- Violation Information --}}
            <div class="ticket-section">
              <h4>Violation Information</h4>
              <div class="ticket-row">
                <div class="ticket-field" style="grid-column: 1 / -1;">
                  <span class="ticket-label">Violations</span>
                  <div class="ticket-value">
                    @if($ticket->violations && is_array($ticket->violations))
                      {{ implode(', ', $ticket->violations) }}
                      @if($ticket->violations_others_text)
                        , {{ $ticket->violations_others_text }}
                      @endif
                    @elseif($ticket->violations_others_text)
                      {{ $ticket->violations_others_text }}
                    @else
                      N/A
                    @endif
                  </div>
                </div>
                <div class="ticket-field">
                  <span class="ticket-label">Place</span>
                  <div class="ticket-value">{{ $ticket->place ?? 'N/A' }}</div>
                </div>
                <div class="ticket-field">
                  <span class="ticket-label">Apprehending Officer</span>
                  <div class="ticket-value">{{ $ticket->apprehending_officer ?? 'N/A' }}</div>
                </div>
              </div>
            </div>

            {{-- Court Information --}}
            @if($ticket->court_date || $ticket->court_time)
            <div class="ticket-section">
              <h4>Court Information</h4>
              <div class="ticket-row">
                <div class="ticket-field">
                  <span class="ticket-label">Court Date</span>
                  <div class="ticket-value">{{ $ticket->court_date ? $ticket->court_date->format('M d, Y') : 'N/A' }}</div>
                </div>
                <div class="ticket-field">
                  <span class="ticket-label">Court Time</span>
                  <div class="ticket-value">{{ $ticket->court_time ?? 'N/A' }}</div>
                </div>
              </div>
            </div>
            @endif

            {{-- Fine Information --}}
            <div class="ticket-section" style="background: #fef3c7; border: 2px solid #fbbf24; border-radius: 8px; padding: 12px;">
              <h4 style="margin-top: 0; color: #92400e;">Fine Amount</h4>
              <div class="ticket-row">
                @php
                  $basePrice = floatval($ticket->price ?? 0) - floatval($ticket->dss_penalty_fine_increase ?? 0);
                  $penaltyIncrease = floatval($ticket->dss_penalty_fine_increase ?? 0);
                  $totalPrice = floatval($ticket->price ?? 0);
                @endphp
                <div class="ticket-field" style="grid-column: 1 / -1;">
                  <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0;">
                    <span class="ticket-label">Base Fine:</span>
                    <div class="ticket-value" style="font-weight: 600;">₱{{ number_format($basePrice, 2) }}</div>
                  </div>
                  @if($penaltyIncrease > 0)
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-top: 1px solid #fbbf24; margin-top: 8px;">
                      <span class="ticket-label" style="color: #dc2626;">
                        DSS Penalty ({{ $ticket->unpaid_violation_count ?? 0 }} unpaid violation(s)):
                      </span>
                      <div class="ticket-value" style="font-weight: 600; color: #dc2626;">+₱{{ number_format($penaltyIncrease, 2) }}</div>
                    </div>
                    @if($ticket->dss_notes)
                      <div style="margin-top: 8px; padding: 8px; background: #fee2e2; border-radius: 4px; font-size: 12px; color: #991b1b;">
                        <strong>Note:</strong> {{ $ticket->dss_notes }}
                      </div>
                    @endif
                  @endif
                  <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-top: 2px solid #fbbf24; margin-top: 8px;">
                    <span class="ticket-label" style="font-size: 16px; font-weight: 700; color: #92400e;">Total Amount to Pay:</span>
                    <div class="ticket-value" style="font-size: 20px; font-weight: 700; color: #dc2626;">₱{{ number_format($totalPrice, 2) }}</div>
                  </div>
                </div>
              </div>
            </div>

            {{-- Additional Information --}}
            @if($ticket->incident_notes || $ticket->remarks)
            <div class="ticket-section">
              <h4>Additional Information</h4>
              <div class="ticket-row">
                @if($ticket->incident_notes)
                <div class="ticket-field" style="grid-column: 1 / -1;">
                  <span class="ticket-label">Incident Notes</span>
                  <div class="ticket-value">{{ $ticket->incident_notes }}</div>
                </div>
                @endif
                @if($ticket->remarks)
                <div class="ticket-field" style="grid-column: 1 / -1;">
                  <span class="ticket-label">Remarks</span>
                  <div class="ticket-value">{{ $ticket->remarks }}</div>
                </div>
                @endif
              </div>
            </div>
            @endif

            {{-- Ticket Image --}}
            @if(isset($ticket->image_url))
            <div class="ticket-section">
              <h4>Ticket Image</h4>
              <img src="{{ $ticket->image_url }}" alt="Ticket Image" class="ticket-image">
            </div>
            @endif
              </div>
            </div>
          </div>
        @endif
      </div>
    </div>
  </main>

  <!-- html2canvas library (required for PDF export) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
  <!-- jsPDF library (required for PDF export) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <!-- html2pdf library for PDF export -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" onload="console.log('html2pdf library loaded')" onerror="console.error('Failed to load html2pdf library')"></script>

  <script>
    // Prevent body scroll when modal is open
    @if(isset($ticket))
      document.body.style.overflow = 'hidden';
    @endif

    function closeModal() {
      const modal = document.getElementById('ticketModal');
      if (modal) {
        document.body.style.overflow = ''; // Restore body scroll
        modal.style.animation = 'fadeOut 0.2s ease';
        setTimeout(() => {
          modal.remove();
          // Redirect to clean URL without query params
          window.location.href = '{{ route("violator.portal") }}';
        }, 200);
      }
    }

    // Helper function to generate ticket HTML in official format
    function generateTicketHTML(ticket) {
      const violations = ticket.violations || [];
      const hasViolation = (violationName) => violations.includes(violationName);
      
      const issuedDate = ticket.issued_date ? new Date(ticket.issued_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : '';
      const issuedDateTime = ticket.issued_date && ticket.issued_time ? `${issuedDate} ${ticket.issued_time}` : '';
      const signatureUrl = ticket.signature_url || ticket.signature || (ticket.image_url ? ticket.image_url : null);
      
      // Handle driver signature URL
      const driverSignatureUrl = ticket.driver_signature_url || ticket.driver_signature || null;
      
      const logoUrl = '{{ asset("assets/Logo.png") }}';
      
      return `
        <div class="print-ticket" style="width: 4.25in; margin: 0 auto; padding: 15px; font-family: Arial, sans-serif; font-size: 9px; line-height: 1.3;">
          <!-- Logo and Header - Side by Side, Centered -->
          <div style="display: flex; align-items: center; justify-content: center; gap: 15px; margin-bottom: 10px;">
            <img src="${logoUrl}" alt="TOMECO Logo" style="height: 60px; width: auto; object-fit: contain; flex-shrink: 0;">
            <div style="text-align: center; flex: 1;">
              <div style="font-size: 8px; line-height: 1.2;">
                <div><strong>Republic of the Philippines</strong></div>
                <div><strong>City of Tacloban</strong></div>
                <div><strong>CITY MAYOR'S OFFICE</strong></div>
                <div style="margin-top: 3px;"><strong>TOMECO</strong></div>
                <div style="font-size: 7px;">(Traffic Operations, Management, Enforcement and Control Office)</div>
              </div>
            </div>
          </div>

          <!-- Title -->
          <div style="text-align: center; margin: 12px 0; border: 2px solid #000; padding: 8px;">
            <h2 style="margin: 0; font-size: 11px; font-weight: bold; text-transform: uppercase;">TRAFFIC VIOLATION RECEIPT/CITATION TICKET</h2>
          </div>

          <!-- Citation Number -->
          <div style="margin-bottom: 10px;">
            <div style="margin-bottom: 10px;">
              <strong>Citation Ticket #:</strong> 
              <span style="font-size: 12px; font-weight: bold; color: #d00;">${ticket.citation_number || ''}</span>
            </div>
          </div>

          <!-- Driver and Vehicle Information Table -->
          <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 8px;">
            <tr>
              <td style="width: 50%; vertical-align: top; padding-right: 15px;">
                <div style="margin-bottom: 8px;"><strong>Driver's Name:</strong></div>
                <div style="margin-bottom: 3px;">(Last Name) ${ticket.driver_lastname || '________________'}</div>
                <div style="margin-bottom: 3px;">(First Name) ${ticket.driver_firstname || '________________'}</div>
                <div style="margin-bottom: 8px;">(Middle Name) ${ticket.driver_middlename || '________________'}</div>
                <div style="margin-bottom: 8px;"><strong>Address:</strong> ${ticket.driver_address || '________________________________'}</div>
                <div style="margin-bottom: 8px;"><strong>D/L Permit #:</strong> ${ticket.dl_number || '________________'}</div>
                <div style="margin-bottom: 5px;">
                  <span style="margin-right: 10px;">[${ticket.dl_type === 'Prof' ? 'X' : ' '}] Prof</span>
                  <span style="margin-right: 10px;">[${ticket.dl_type === 'N/P' ? 'X' : ' '}] N/P</span>
                  <span style="margin-right: 10px;">[${ticket.dl_type === 'S/P' ? 'X' : ' '}] S/P</span>
                  <span>[${ticket.dl_type && ticket.dl_type !== 'Prof' && ticket.dl_type !== 'N/P' && ticket.dl_type !== 'S/P' ? 'X' : ' '}] Others</span>
                </div>
              </td>
              <td style="width: 50%; vertical-align: top; padding-left: 15px;">
                <div style="margin-bottom: 8px;"><strong>PLT #:</strong> ${ticket.plate_number || '________________'}</div>
                <div style="margin-bottom: 8px;"><strong>Make:</strong> ${ticket.vehicle_make || '________________'}</div>
                <div style="margin-bottom: 8px;"><strong>Owner:</strong> ${ticket.owner_name || (ticket.driver_firstname + ' ' + ticket.driver_lastname) || '________________'}</div>
                <div style="margin-bottom: 8px;"><strong>Address:</strong> ${ticket.owner_address || ticket.driver_address || '________________________________'}</div>
                <div style="margin-bottom: 8px;"><strong>CR #:</strong> ${ticket.cr_number || '________________'}</div>
                <div style="margin-bottom: 8px;"><strong>Model:</strong> ${ticket.vehicle_model || '________________'}</div>
                <div style="margin-bottom: 8px;"><strong>Type:</strong> ${ticket.vehicle_type || '________________'}</div>
                <div style="margin-bottom: 8px;"><strong>OR #:</strong> ${ticket.or_number || '________________'}</div>
                <div style="margin-bottom: 8px;"><strong>Year:</strong> ${ticket.vehicle_year || '____'}</div>
              </td>
            </tr>
          </table>

          <!-- Violations Section -->
          <div style="margin-bottom: 10px; border: 1px solid #000; padding: 6px;">
            <div style="margin-bottom: 5px; font-weight: bold; font-size: 8px;">VIOLATION(S)</div>
            <div style="font-size: 7px; margin-bottom: 6px; line-height: 1.3;">
              You are hereby cited/charged for committing the violation(s) hereunder: (Rule IX, CO# 2000-01) as amended and other related City Ordinances.
            </div>
            <div style="font-size: 7px; line-height: 1.5;">
              ${violations.length > 0 ? violations.map(v => {
                // Skip "Other" violation if there's violations_others_text, we'll handle it separately
                if (v === 'Other' && ticket.violations_others_text) {
                  return '';
                }
                return '<div>• ' + v + '</div>';
              }).filter(v => v !== '').join('') : ''}
              ${ticket.violations_others_text ? '<div>• Others: ' + ticket.violations_others_text + '</div>' : ''}
              ${violations.length === 0 && !ticket.violations_others_text ? '<div>No violations specified</div>' : ''}
            </div>
          </div>

          <!-- Incident Details -->
          <div style="margin-bottom: 10px; font-size: 8px;">
            <div style="margin-bottom: 3px;"><strong>Place:</strong> ${ticket.place || '________________'}</div>
            <div style="margin-bottom: 3px;">
              <strong>Accident:</strong> 
              <span style="margin-left: 8px;">[${ticket.accident === true || ticket.accident === 'Yes' ? 'X' : ' '}] Yes</span>
              <span style="margin-left: 8px;">[${ticket.accident === false || ticket.accident === 'No' || !ticket.accident ? 'X' : ' '}] No</span>
            </div>
            <div style="margin-bottom: 3px;"><strong>Date & Time:</strong> ${issuedDateTime || '________________'}</div>
          </div>

          <!-- Instructions and Driver's Promise -->
          <div style="margin-bottom: 10px; border: 1px solid #000; padding: 6px; font-size: 7px; line-height: 1.4;">
            <div style="margin-bottom: 8px;">
              Hereby, the driver is ORDERED to appear at TOMECO/City Fiscal's Office/or to the Municipal Trial Court in Cities within 72 hours (3 days).
            </div>
            <div style="margin-bottom: 8px; font-weight: bold; text-align: center;">
              THIS SERVE AS TRAFFIC VIOLATION RECEIPT/CITATION TICKET.
            </div>
            <div style="margin-bottom: 8px;">
              I HEREBY PROMISE to appear at TOMECO/City Fiscal's Office/Municipal Trial Court in Cities within 72 hours (3 days) to answer the above hereincharge(s). That failure on my part is a waiver to any preliminary investigation, if any, and to whatever criminal action that may be taken against me.
            </div>
            <div style="margin-top: 15px;">
              <span style="margin-right: 20px;">[${ticket.admitted_or_protest === 'Admitted' || ticket.admitted_or_protest === 'ADMITTED' ? 'X' : ' '}] ADMITTED</span>
              <span>[${ticket.admitted_or_protest === 'Under Protest' || ticket.admitted_or_protest === 'UNDER PROTEST' ? 'X' : ' '}] UNDER PROTEST</span>
            </div>
            <div style="margin-top: 30px; text-align: center;">
              ${driverSignatureUrl ? `
                <div style="margin: 6px 0; text-align: center;">
                  <img src="${driverSignatureUrl}" alt="Driver Signature" style="max-width: 180px; max-height: 70px; display: block; margin: 0 auto;">
                </div>
              ` : ''}
              <div style="border-top: 1px solid #000; width: 300px; margin: 0 auto; padding-top: 5px; text-align: center;">
                <strong>(Signature of Driver)</strong>
              </div>
            </div>
          </div>

          <!-- Apprehension Report -->
          <div style="margin-top: 15px; border: 1px solid #000; padding: 6px; font-size: 7px;">
            <div style="font-weight: bold; margin-bottom: 4px;">APPREHENSION REPORT</div>
            <div style="margin-bottom: 10px; line-height: 1.3;">
              Apprehending TOMECO law enforcer/Deputized Agent are required to Submit apprehension report to TOMECO within 24 hours, otherwise deputation order shall be revoked
            </div>
            <div style="margin-bottom: 20px; text-align: center;">
              <strong>Apprehending Officer:</strong>
              ${signatureUrl ? `
                <div style="margin: 6px 0;">
                  <img src="${signatureUrl}" alt="Signature" style="max-width: 180px; max-height: 70px; margin: 0 auto;">
                </div>
                <div style="margin-top: 3px; font-size: 7px;">
                  ${ticket.apprehending_officer ? ticket.apprehending_officer.toUpperCase() : '________________'}
                </div>
                <div style="margin-top: 3px; font-size: 6px;">
                  <strong>(Signature over printed Name/Unit)</strong>
                </div>
              ` : `
                <div style="border-top: 1px solid #000; width: 300px; margin: 10px auto 0; padding-top: 5px;">
                  ${ticket.apprehending_officer ? ticket.apprehending_officer.toUpperCase() : '________________'}
                </div>
                <div style="margin-top: 5px; font-size: 8px;">
                  <strong>(Signature over printed Name/Unit)</strong>
                </div>
              `}
            </div>
            <div>
              <strong>TOMECO D/ID No.</strong> ${ticket.tomeco_did || '________________'}
            </div>
          </div>
        </div>
      `;
    }


    function exportToPDF(event) {
      @if(isset($ticket))
      const ticket = @json($ticket);
      const pdfButton = event ? event.target.closest('.action-btn') : document.querySelector('.action-btn[onclick*="exportToPDF"]');
      let originalText = '';
      
      // Show loading message
      if (pdfButton) {
        originalText = pdfButton.innerHTML;
        pdfButton.innerHTML = '<svg class="animate-spin" style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Exporting...';
        pdfButton.disabled = true;
      }
      
      // PDF format: Half width - 4.25in width, auto height, 0.25in margins
      const pdfWidthIn = 4.25; // 4.25 inches width (half of letter size)
      const pdfMarginIn = 0.25; // 0.25 inch margins
      const pdfWidthMm = pdfWidthIn * 25.4; // Convert to mm
      const pdfMarginMm = pdfMarginIn * 25.4; // Convert to mm
      const contentWidthMm = pdfWidthMm - (pdfMarginMm * 2); // Content width after margins
      
      try {
        // Generate ticket HTML in official format
        const printContent = generateTicketHTML(ticket);
        
        // Create a temporary container matching print format exactly
        const tempDiv = document.createElement('div');
        tempDiv.id = 'pdf-temp-container';
        tempDiv.style.position = 'fixed';
        tempDiv.style.left = '0';
        tempDiv.style.top = '0';
        tempDiv.style.width = (pdfWidthIn * 96) + 'px'; // 4.25in at 96 DPI = 408px
        tempDiv.style.display = 'block';
        tempDiv.style.backgroundColor = '#ffffff';
        tempDiv.style.padding = '0';
        tempDiv.style.margin = '0';
        tempDiv.style.overflow = 'visible';
        tempDiv.style.zIndex = '999999';
        tempDiv.style.opacity = '0.01';
        tempDiv.style.pointerEvents = 'none';
        tempDiv.innerHTML = printContent;
        document.body.appendChild(tempDiv);
        
        const ticketElement = tempDiv.querySelector('.print-ticket');
        
        if (!ticketElement) {
          document.body.removeChild(tempDiv);
          if (pdfButton) {
            pdfButton.innerHTML = originalText;
            pdfButton.disabled = false;
          }
          alert('Could not find ticket element');
          return;
        }
        
        // Style to match print format exactly
        ticketElement.style.width = '4.25in';
        ticketElement.style.margin = '0 auto';
        ticketElement.style.boxSizing = 'border-box';
        ticketElement.style.fontFamily = 'Arial, sans-serif';
        ticketElement.style.fontSize = '9px';
        ticketElement.style.color = '#000';
        
        // Overall timeout
        const overallTimeout = setTimeout(() => {
          const container = document.getElementById('pdf-temp-container');
          if (container && container.parentNode) {
            document.body.removeChild(container);
          }
          if (pdfButton) {
            pdfButton.innerHTML = originalText;
            pdfButton.disabled = false;
          }
          alert('PDF generation timed out. Please try again.');
        }, 20000);
        
        // Wait a moment for rendering, then capture
        setTimeout(() => {
          // Check if html2pdf library is loaded with retry mechanism
          let attempts = 0;
          const maxAttempts = 20;
          
          const tryGenerate = () => {
            attempts++;
            
            // Check if html2pdf is available
            if (typeof html2pdf === 'undefined') {
              if (attempts < maxAttempts) {
                setTimeout(tryGenerate, 200);
                return;
              } else {
                clearTimeout(overallTimeout);
                const container = document.getElementById('pdf-temp-container');
                if (container && container.parentNode) {
                  document.body.removeChild(container);
                }
                if (pdfButton) {
                  pdfButton.innerHTML = originalText;
                  pdfButton.disabled = false;
                }
                alert('PDF library is still loading. Please wait a moment and try again, or refresh the page.');
                return;
              }
            }
            
            console.log('html2pdf library found, starting PDF generation...');
            
            // Access html2canvas - try multiple methods
            let html2canvasFn = null;
            
            // Method 1: Direct from window (if loaded separately)
            if (typeof window.html2canvas !== 'undefined') {
              html2canvasFn = window.html2canvas;
            }
            // Method 2: From html2pdf bundle
            else if (html2pdf && html2pdf().html2canvas) {
              html2canvasFn = html2pdf().html2canvas;
            }
            // Method 3: Global html2canvas
            else if (typeof html2canvas !== 'undefined') {
              html2canvasFn = html2canvas;
            }
            
            if (!html2canvasFn) {
              clearTimeout(overallTimeout);
              const container = document.getElementById('pdf-temp-container');
              if (container && container.parentNode) {
                document.body.removeChild(container);
              }
              if (pdfButton) {
                pdfButton.innerHTML = originalText;
                pdfButton.disabled = false;
              }
              console.error('html2canvas not found. Available:', {
                windowHtml2canvas: typeof window.html2canvas,
                html2pdfHtml2canvas: html2pdf && html2pdf().html2canvas ? 'yes' : 'no',
                globalHtml2canvas: typeof html2canvas
              });
              alert('html2canvas library not loaded. Please refresh the page and ensure the CDN is accessible.');
              return;
            }
            
            // Capture element as canvas
            html2canvasFn(ticketElement, {
              scale: 2,
              useCORS: true,
              allowTaint: true,
              backgroundColor: '#ffffff',
              logging: false,
              width: 408, // 4.25in at 96 DPI = 408px
              windowWidth: 408
            }).then((canvas) => {
              console.log('Canvas created:', canvas.width, 'x', canvas.height);
              
              // Calculate PDF height based on content (auto height like print)
              const mmToPx = 3.779527559; // 1mm = 3.779527559px at 96 DPI
              const canvasHeight = canvas.height;
              const contentHeightMm = canvasHeight / mmToPx;
              const pdfHeightMm = contentHeightMm + (pdfMarginMm * 2);
              
              console.log('PDF dimensions:', pdfWidthMm, 'mm x', pdfHeightMm.toFixed(2), 'mm');
              
              // Convert canvas to image
              const imgData = canvas.toDataURL('image/jpeg', 0.95);
              
              // Get jsPDF - try multiple methods
              let jsPDF = null;
              
              // Method 1: From window.jspdf (if loaded separately)
              if (window.jspdf && window.jspdf.jsPDF) {
                jsPDF = window.jspdf.jsPDF;
              }
              // Method 2: Direct window.jsPDF
              else if (window.jsPDF) {
                jsPDF = window.jsPDF;
              }
              // Method 3: From html2pdf bundle
              else if (html2pdf && html2pdf().jsPDF) {
                jsPDF = html2pdf().jsPDF;
              }
              
              if (!jsPDF) {
                clearTimeout(overallTimeout);
                const container = document.getElementById('pdf-temp-container');
                if (container && container.parentNode) {
                  document.body.removeChild(container);
                }
                if (pdfButton) {
                  pdfButton.innerHTML = originalText;
                  pdfButton.disabled = false;
                }
                console.error('jsPDF not found. Available:', {
                  windowJspdf: window.jspdf ? 'yes' : 'no',
                  windowJsPDF: typeof window.jsPDF,
                  html2pdfJsPDF: html2pdf && html2pdf().jsPDF ? 'yes' : 'no'
                });
                alert('jsPDF library not loaded. Please refresh the page and ensure the CDN is accessible.');
                return;
              }
              
              // Create PDF with calculated dimensions (4in width, calculated height)
              const pdf = new jsPDF({
                unit: 'mm',
                format: [pdfWidthMm, pdfHeightMm],
                orientation: 'portrait',
                compress: true
              });
              
              // Calculate image dimensions to fit content area (with margins)
              const imgWidthMm = contentWidthMm;
              const imgHeightMm = contentHeightMm;
              
              // Add image with margins (0.25in = 6.35mm)
              pdf.addImage(imgData, 'JPEG', pdfMarginMm, pdfMarginMm, imgWidthMm, imgHeightMm, undefined, 'FAST');
              
              // Save PDF
              pdf.save('ticket-{{ $ticket->citation_number ?? "ticket" }}.pdf');
              
              clearTimeout(overallTimeout);
              console.log('PDF exported successfully!');
              
              // Clean up
              const container = document.getElementById('pdf-temp-container');
              if (container && container.parentNode) {
                document.body.removeChild(container);
              }
              
              if (pdfButton) {
                pdfButton.innerHTML = originalText;
                pdfButton.disabled = false;
              }
            }).catch((error) => {
              clearTimeout(overallTimeout);
              console.error('PDF generation error:', error);
              console.error('Error details:', error);
              
              // Clean up
              const container = document.getElementById('pdf-temp-container');
              if (container && container.parentNode) {
                document.body.removeChild(container);
              }
              
              if (pdfButton) {
                pdfButton.innerHTML = originalText;
                pdfButton.disabled = false;
              }
              
              alert('Error generating PDF: ' + (error.message || 'Please try again.'));
            });
          };
          
          // Start trying
          tryGenerate();
        }, 500);
      } catch (error) {
        console.error('PDF export error:', error);
        alert('Error generating PDF: ' + (error.message || 'Please try again.'));
        
        const container = document.getElementById('pdf-temp-container');
        if (container && container.parentNode) {
          document.body.removeChild(container);
        }
        
        if (pdfButton) {
          pdfButton.innerHTML = originalText;
          pdfButton.disabled = false;
        }
      }
      @else
      alert('Ticket data not available');
      @endif
    }

    // GCash Payment Function
    function initiateGCashPayment() {
      @if(isset($ticket))
      const ticket = @json($ticket);
      const citationNumber = ticket.citation_number;
      
      if (!citationNumber) {
        alert('Citation number is required for payment.');
        return;
      }
      
      // Show loading
      const gcashBtn = document.querySelector('.gcash-btn');
      if (gcashBtn) {
        const originalText = gcashBtn.innerHTML;
        gcashBtn.innerHTML = '<svg class="animate-spin" style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Processing...';
        gcashBtn.disabled = true;
        
        // Create form to submit payment request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("violator.payment.initiate") }}';
        form.style.display = 'none';
        
        // Add CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                         document.querySelector('input[name="_token"]')?.value;
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
        
        // Add citation number
        const citationInput = document.createElement('input');
        citationInput.type = 'hidden';
        citationInput.name = 'citation_number';
        citationInput.value = citationNumber;
        form.appendChild(citationInput);
        
        // Add ticket ID if available
        if (ticket.id) {
          const ticketIdInput = document.createElement('input');
          ticketIdInput.type = 'hidden';
          ticketIdInput.name = 'ticket_id';
          ticketIdInput.value = ticket.id;
          form.appendChild(ticketIdInput);
        }
        
        document.body.appendChild(form);
        form.submit();
      }
      @else
      alert('Ticket data not available');
      @endif
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        const modal = document.getElementById('ticketModal');
        if (modal) {
          closeModal();
        }
      }
    });

    // Add fadeOut animation
    const style = document.createElement('style');
    style.textContent = `
      @keyframes fadeOut {
        from { opacity: 1; }
        to { opacity: 0; }
      }
      @keyframes spin {
        to { transform: rotate(360deg); }
      }
      .animate-spin {
        animation: spin 1s linear infinite;
      }
    `;
    document.head.appendChild(style);
  </script>

</body>
</html>

