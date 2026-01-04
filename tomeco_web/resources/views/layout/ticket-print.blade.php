<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Traffic Violation Receipt/Citation Ticket - {{ $ticket->citation_number ?? 'N/A' }}</title>
    <style>
        @page {
            size: 4in auto;
            margin: 0.25in;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 15px;
            color: #000;
            font-size: 9px;
            background: #fff;
        }
        .print-ticket {
            width: 4in;
            margin: 0 auto;
            padding: 15px;
            line-height: 1.3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        u {
            text-decoration: underline;
            text-decoration-thickness: 1px;
        }
        @media print {
            @page {
                size: 4in auto;
                margin: 0.25in;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .print-ticket {
                page-break-inside: avoid;
                width: 4in;
            }
            .no-print {
                display: none !important;
            }
        }
        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            padding: 10px 20px;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
        }
        .back-button:hover {
            background: #2563eb;
        }
    </style>
    <script>
        // Auto-print when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</head>
<body>
    <button class="back-button no-print" onclick="window.history.back()">← Back</button>
    
    <div class="print-ticket">
        @php
            $violations = $ticket->violations ?? [];
            $hasViolation = function($name) use ($violations) {
                return in_array($name, $violations);
            };
            $issuedDate = $ticket->issued_date ? $ticket->issued_date->format('F d, Y') : '';
            $issuedDateTime = $ticket->issued_date && $ticket->issued_time ? $issuedDate . ' ' . substr($ticket->issued_time, 0, 5) : '';
        @endphp

        <!-- Header -->
        <div style="text-align: right; margin-bottom: 10px;">
            <div style="font-size: 8px; line-height: 1.2;">
                <div><strong>Republic of the Philippines</strong></div>
                <div><strong>City of Tacloban</strong></div>
                <div><strong>CITY MAYOR'S OFFICE</strong></div>
                <div style="margin-top: 3px;"><strong>TOMECO</strong></div>
                <div style="font-size: 7px;">(Traffic Operations, Management, Enforcement and Control Office)</div>
            </div>
        </div>

        <!-- Title -->
        <div style="text-align: center; margin: 12px 0; border: 2px solid #000; padding: 6px;">
            <h2 style="margin: 0; font-size: 10px; font-weight: bold; text-transform: uppercase;">TRAFFIC VIOLATION RECEIPT/CITATION TICKET</h2>
        </div>

        <!-- To and Citation Number -->
        <div style="margin-bottom: 10px;">
            <div style="margin-bottom: 5px; font-size: 8px;"><strong>To:</strong> {{ $ticket->driver_firstname ?? '' }} {{ $ticket->driver_middlename ?? '' }} {{ $ticket->driver_lastname ?? '' }}</div>
            <div style="margin-bottom: 10px;">
                <strong>Citation Ticket #:</strong> 
                <span style="font-size: 12px; font-weight: bold; color: #d00;">{{ $ticket->citation_number ?? '' }}</span>
            </div>
        </div>

        <!-- Driver and Vehicle Information Table -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 8px;">
            <tr>
                <td style="width: 50%; vertical-align: top; padding-right: 15px;">
                    <div style="margin-bottom: 8px;"><strong>Driver's Name:</strong></div>
                    <div style="margin-bottom: 3px;">(Last Name) <u>{{ $ticket->driver_lastname ?? '________________' }}</u></div>
                    <div style="margin-bottom: 3px;">(First Name) <u>{{ $ticket->driver_firstname ?? '________________' }}</u></div>
                    <div style="margin-bottom: 8px;">(Middle Name) <u>{{ $ticket->driver_middlename ?? '________________' }}</u></div>
                    <div style="margin-bottom: 8px;"><strong>Address:</strong> <u>{{ $ticket->driver_address ?? '________________________________' }}</u></div>
                    <div style="margin-bottom: 8px;"><strong>D/L Permit #:</strong> <u>{{ $ticket->dl_number ?? '________________' }}</u></div>
                    <div style="margin-bottom: 5px;">
                        <span style="margin-right: 10px;">[{{ $ticket->dl_type === 'Prof' ? 'X' : ' ' }}] Prof</span>
                        <span style="margin-right: 10px;">[{{ $ticket->dl_type === 'N/P' ? 'X' : ' ' }}] N/P</span>
                        <span style="margin-right: 10px;">[{{ $ticket->dl_type === 'S/P' ? 'X' : ' ' }}] S/P</span>
                        <span>[{{ $ticket->dl_type === 'Others' ? 'X' : ' ' }}] Others</span>
                    </div>
                </td>
                <td style="width: 50%; vertical-align: top; padding-left: 15px;">
                    <div style="margin-bottom: 8px;"><strong>PLT #:</strong> <u>{{ $ticket->plate_number ?? '________________' }}</u></div>
                    <div style="margin-bottom: 8px;"><strong>Make:</strong> <u>{{ $ticket->vehicle_make ?? '________________' }}</u></div>
                    <div style="margin-bottom: 8px;"><strong>Owner:</strong> <u>{{ $ticket->owner_name ?? ($ticket->driver_firstname . ' ' . $ticket->driver_lastname) ?? '________________' }}</u></div>
                    <div style="margin-bottom: 8px;"><strong>Address:</strong> <u>{{ $ticket->owner_address ?? $ticket->driver_address ?? '________________________________' }}</u></div>
                    <div style="margin-bottom: 8px;"><strong>CR #:</strong> <u>{{ $ticket->cr_number ?? '________________' }}</u></div>
                    <div style="margin-bottom: 8px;"><strong>Model:</strong> <u>{{ $ticket->vehicle_model ?? '________________' }}</u></div>
                    <div style="margin-bottom: 8px;"><strong>Type:</strong> <u>{{ $ticket->vehicle_type ?? '________________' }}</u></div>
                    <div style="margin-bottom: 8px;"><strong>OR #:</strong> <u>{{ $ticket->or_number ?? '________________' }}</u></div>
                    <div style="margin-bottom: 8px;"><strong>Year:</strong> <u>{{ $ticket->vehicle_year ?? '____' }}</u></div>
                </td>
            </tr>
        </table>

        <!-- Violations Section -->
        <div style="margin-bottom: 10px; border: 1px solid #000; padding: 6px;">
            <div style="margin-bottom: 5px; font-weight: bold; font-size: 8px;">VIOLATION(S)</div>
            <div style="font-size: 7px; margin-bottom: 6px; line-height: 1.3;">
                You are hereby cited/charged for committing the violation(s) marked "x" hereunder: (Rule IX, CO# 2000-01) as amended and other related City Ordinances.
            </div>
            <div style="font-size: 7px; line-height: 1.5;">
                <div>[ ] Driving without D/L</div>
                <div>[ ] Unregistered Vehicle</div>
                <div>[{{ $hasViolation('Illegal Parking') ? 'X' : ' ' }}] Illegal Parking</div>
                <div>[{{ $hasViolation('Disregarding Traffic Sign') ? 'X' : ' ' }}] Disregarding Traffic Sign: <u>{{ $ticket->violations_others_text ?? '________________' }}</u></div>
                <div>[ ] Obstruction</div>
                <div>[ ] Truck Ban</div>
                <div>[ ] Operating Along National Highway</div>
                <div>[{{ $hasViolation('No Helmet') ? 'X' : ' ' }}] No Helmet</div>
                <div>[ ] Defective Head Light.</div>
                <div>[{{ $hasViolation('Other') || $ticket->violations_others_text ? 'X' : ' ' }}] Others: <u>{{ $ticket->violations_others_text ?? '________________' }}</u></div>
                <div>[ ] Violation to CO # 2007-10-31 "The Anti-Littering Ordinance"</div>
                <div>[ ] Violation to CO # 2009-10-160 "The Anti-Smoking Ordinance."</div>
                <div>[ ] Violation to CO # 2007-10-66 "The anti-urinating and Defecating Ordinance."</div>
                @if($hasViolation('Speeding'))
                <div>[X] Speeding</div>
                @endif
            </div>
        </div>

        <!-- Incident Details -->
        <div style="margin-bottom: 10px; font-size: 8px;">
            <div style="margin-bottom: 3px;"><strong>Place:</strong> <u>{{ $ticket->place ?? '________________' }}</u></div>
            <div style="margin-bottom: 3px;">
                <strong>Accident:</strong> 
                <span style="margin-left: 8px;">[{{ $ticket->accident === true || $ticket->accident === 1 ? 'X' : ' ' }}] Yes</span>
                <span style="margin-left: 8px;">[{{ $ticket->accident === false || $ticket->accident === 0 || $ticket->accident === null ? 'X' : ' ' }}] No</span>
            </div>
            <div style="margin-bottom: 3px;"><strong>Date & Time:</strong> <u>{{ $issuedDateTime ?? '________________' }}</u></div>
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
                <span style="margin-right: 20px;">[{{ $ticket->admitted_or_protest === 'Admitted' ? 'X' : ' ' }}] ADMITTED</span>
                <span>[{{ $ticket->admitted_or_protest === 'Under Protest' ? 'X' : ' ' }}] UNDER PROTEST</span>
            </div>
            <div style="margin-top: 20px; text-align: center;">
                @if($ticket->driver_signature)
                    <div style="margin-bottom: 5px;">
                        <img src="{{ $ticket->driver_signature }}" alt="Driver Signature" style="max-width: 200px; max-height: 80px;">
                    </div>
                @endif
                <div style="border-top: 1px solid #000; width: 300px; margin: {{ $ticket->driver_signature ? '5px' : '30px' }} auto 0; padding-top: 5px;">
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
                @if($ticket->signature)
                    <div style="margin: 6px 0;">
                        <img src="{{ $ticket->signature }}" alt="Signature" style="max-width: 180px; max-height: 70px;">
                    </div>
                    <div style="margin-top: 3px; font-size: 7px;">
                        <u>{{ $ticket->apprehending_officer ? strtoupper($ticket->apprehending_officer) : '________________' }}</u>
                    </div>
                    <div style="margin-top: 3px; font-size: 6px;">
                        <strong>(Signature over printed Name/Unit)</strong>
                    </div>
                @else
                    <div style="border-top: 1px solid #000; width: 300px; margin: 10px auto 0; padding-top: 5px;">
                        <u>{{ $ticket->apprehending_officer ? strtoupper($ticket->apprehending_officer) : '________________' }}</u>
                    </div>
                    <div style="margin-top: 5px; font-size: 8px;">
                        <strong>(Signature over printed Name/Unit)</strong>
                    </div>
                @endif
            </div>
            <div>
                <strong>TOMECO D/ID No.</strong> <u>{{ $ticket->tomeco_did ?? '________________' }}</u>
            </div>
            <div style="margin-top: 10px; text-align: center; font-weight: bold; font-size: 9px;">
                <strong>Amount Due: ₱{{ number_format($ticket->price ?? 1.00, 2) }}</strong>
            </div>
        </div>
    </div>
</body>
</html>

