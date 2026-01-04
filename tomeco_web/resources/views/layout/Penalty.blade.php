@extends('layout.app')

@section('title', 'Penalty Recommendation — TOMECO DSS')

@section('content')
<style>
    .page-wrap{max-width:1400px;margin:24px auto;padding:0 12px}
    .toolbar{display:flex;gap:12px;align-items:center;justify-content:space-between;margin-bottom:16px}
    .btn{display:inline-flex;align-items:center;gap:8px;border:0;border-radius:8px;padding:10px 14px;cursor:pointer;font-size:14px}
    .btn-primary{background:#FFCC3F;color:#111}
    .btn-primary:hover{background:#e5a400}
    .btn-info{background:#3b82f6;color:#fff}
    .btn-info:hover{background:#2563eb}
    .btn-light{background:#f3f4f6;color:#111}
    .btn-sm{padding:6px 10px;font-size:12px}
    .table-wrap{overflow:auto;border:1px solid #e5e7eb;border-radius:10px;background:#fff}
    table{width:100%;border-collapse:collapse;font-size:14px}
    thead th{position:sticky;top:0;background:#fafafa;border-bottom:2px solid #e5e7eb;text-align:left;padding:12px;white-space:nowrap;font-weight:600;color:#374151}
    tbody td{border-top:1px solid #f0f0f0;padding:12px;vertical-align:middle}
    tbody tr:hover{background:#f9fafb}
    .badge{display:inline-block;padding:4px 10px;border-radius:6px;font-size:12px;font-weight:500}
    .badge-danger{background:#fee2e2;color:#991b1b;border:1px solid #fecaca}
    .badge-success{background:#d1fae5;color:#065f46;border:1px solid #a7f3d0}
    .badge-warning{background:#fef3c7;color:#92400e;border:1px solid #fde68a}
    .badge-info{background:#dbeafe;color:#1e40af;border:1px solid #93c5fd}
    .muted{color:#6b7280;font-size:12px}
    .text-right{text-align:right}
    .font-bold{font-weight:600}
    .table-empty{padding:40px;text-align:center;color:#6b7280}
    .dss-header{background:linear-gradient(135deg, #8B0000, #C00000);color:#fff;padding:20px;border-radius:10px;margin-bottom:20px}
    .dss-header h1{margin:0 0 8px;font-size:24px;font-weight:700}
    .dss-header p{margin:0;opacity:0.9;font-size:14px}
    
    /* Modal styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.45);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        padding: 30px;
        overflow-y: auto;
    }
    .modal.open {
        display: flex;
    }
    .modal-card {
        background: #fff;
        width: min(900px, 95%);
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        max-height: 90vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        position: relative;
    }
    .modal-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        border-bottom: 1px solid #eee;
        background: #fafafa;
        color: #111;
        font-weight: 600;
    }
    .modal-body {
        padding: 20px;
        overflow-y: auto;
        flex: 1 1 auto;
    }
    .ticket-item{
        border:1px solid #e5e7eb;
        border-radius:8px;
        padding:12px;
        margin-bottom:12px;
        background:#f9fafb
    }
    .ticket-item-header{
        display:flex;
        justify-content:space-between;
        align-items:center;
        margin-bottom:8px
    }
    .ticket-item-body{
        display:grid;
        grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));
        gap:8px;
        font-size:13px
    }
    .ticket-item-label{
        color:#6b7280;
        font-size:12px;
        margin-bottom:2px
    }
    .ticket-item-value{
        font-weight:500;
        color:#111
    }
    @media (max-width: 768px) {
        .table-wrap{overflow-x:auto}
        thead th, tbody td{font-size:12px;padding:8px}
    }
</style>

<div class="page-wrap">
    <div class="dss-header">
        <h1>Penalty Recommendation System (DSS)</h1>
        <p>Decision Support System for TOMECO - Repeat Offender Penalty Management</p>
        <div style="margin-top:12px;font-size:13px;opacity:0.95;">
            <strong>DSS Rules:</strong> 2nd=Warning+Fine Increase | 3rd=Temp Suspension | 4th=Extended Suspension+Legal Action | 5th+=Permanent Ban+Legal Proceedings
        </div>
    </div>

    <div class="toolbar">
        <h2 style="margin:0;font-size:20px;font-weight:600;">Violators with Multiple Tickets</h2>
        <div style="display:flex;gap:12px;align-items:center;">
            <span class="muted">Total: {{ count($violators) }} violator(s)</span>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success" style="padding:12px;background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;border-radius:8px;margin-bottom:16px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>#</th>
                <th>Driver Name</th>
                <th>DL Number</th>
                <th>Contact</th>
                <th>Address</th>
                <th class="text-right">Unpaid Count</th>
                <th class="text-right">Total Fine</th>
                <th class="text-right">DSS Penalty</th>
                <th class="text-right">Status</th>
                <th style="width:120px;">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($violators as $index => $violator)
                @php
                    $fullName = trim(implode(' ', array_filter([
                        $violator['driver_firstname'] ?? '',
                        $violator['driver_middlename'] ?? '',
                        $violator['driver_lastname'] ?? ''
                    ]))) ?: 'Unknown';
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <div style="font-weight:600;">{{ $fullName }}</div>
                        @php
                            $unpaidCount = $violator['dss_unpaid_violation_count'] ?? $violator['unpaid_count'];
                        @endphp
                        @if($unpaidCount >= 5)
                            <span class="badge badge-danger" style="margin-top:4px;display:inline-block;">Permanent Ban</span>
                        @elseif($unpaidCount >= 4)
                            <span class="badge badge-danger" style="margin-top:4px;display:inline-block;">Legal Action</span>
                        @elseif($unpaidCount >= 3)
                            <span class="badge badge-warning" style="margin-top:4px;display:inline-block;">Suspended</span>
                        @elseif($unpaidCount >= 2)
                            <span class="badge badge-warning" style="margin-top:4px;display:inline-block;">Warning</span>
                        @endif
                    </td>
                    <td>{{ $violator['dl_number'] ?: '—' }}</td>
                    <td>{{ $violator['driver_contact'] ?: '—' }}</td>
                    <td style="max-width:250px;">{{ $violator['driver_address'] ?: '—' }}</td>
                    <td class="text-right font-bold">
                        <span class="badge badge-danger">{{ $violator['unpaid_count'] }}</span>
                    </td>
                    <td class="text-right font-bold">
                        ₱{{ number_format($violator['total_fine'] + ($violator['dss_total_penalty_increase'] ?? 0), 2) }}
                        @if(($violator['dss_total_penalty_increase'] ?? 0) > 0)
                            <div class="muted" style="font-size:11px;font-weight:normal;">
                                (+₱{{ number_format($violator['dss_total_penalty_increase'], 2) }} penalty)
                            </div>
                        @endif
                    </td>
                    <td class="text-right">
                        @php
                            $penaltyLevel = $violator['dss_penalty_level'] ?? null;
                            $unpaidCount = $violator['dss_unpaid_violation_count'] ?? $violator['unpaid_count'];
                        @endphp
                        @if($penaltyLevel)
                            @if($penaltyLevel === 'warning')
                                <span class="badge badge-warning">Warning (2nd)</span>
                            @elseif($penaltyLevel === 'suspension_temp')
                                <span class="badge badge-danger">Temp Suspension (3rd)</span>
                            @elseif($penaltyLevel === 'suspension_extended')
                                <span class="badge badge-danger">Extended Suspension (4th)</span>
                            @elseif($penaltyLevel === 'permanent_ban')
                                <span class="badge badge-danger">Permanent Ban (5th+)</span>
                            @endif
                        @elseif($unpaidCount >= 2)
                            <span class="badge badge-warning">Pending DSS</span>
                        @else
                            <span class="muted">—</span>
                        @endif
                    </td>
                    <td class="text-right">
                        @php
                            $unpaidCount = $violator['dss_unpaid_violation_count'] ?? $violator['unpaid_count'];
                        @endphp
                        @if($unpaidCount >= 5)
                            <span class="badge badge-danger">Permanent Ban</span>
                        @elseif($unpaidCount >= 4)
                            <span class="badge badge-danger">Legal Action</span>
                        @elseif($unpaidCount >= 3)
                            <span class="badge badge-warning">Suspended</span>
                        @elseif($unpaidCount >= 2)
                            <span class="badge badge-warning">Warning</span>
                        @else
                            <span class="badge badge-info">Active</span>
                        @endif
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-info view-tickets-btn" 
                                data-violator-key="{{ $violator['identifier'] }}"
                                data-violator-name="{{ $fullName }}">
                            View Tickets
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10">
                        <div class="table-empty">
                            <p style="font-size:16px;margin-bottom:8px;">No violators found with multiple unpaid tickets.</p>
                            <p class="muted">Only violators with 2 or more unpaid violation tickets are displayed here.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- VIEW TICKETS MODAL --}}
<div class="modal" id="viewTicketsModal" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="modal-card">
        <div class="modal-head">
            <div id="viewTicketsModalTitle">Violator Tickets</div>
            <button class="btn btn-light" id="closeViewTicketsModal" aria-label="Close dialog" style="padding: 6px 8px;">✖</button>
        </div>
        <div class="modal-body" id="viewTicketsModalBody">
            <div style="text-align: center; padding: 40px;">
                <div class="spinner-border" role="status" style="display:inline-block;width:2rem;height:2rem;border:0.25em solid currentColor;border-right-color:transparent;border-radius:50%;animation:spinner-border 0.75s linear infinite"></div>
                <p style="margin-top: 12px; color: #6b7280;">Loading tickets...</p>
            </div>
        </div>
    </div>
</div>

<script>
    // Store violators data for JavaScript access
    const violatorsData = @json($violators);
    
    // Modal controls
    const viewTicketsModal = document.getElementById('viewTicketsModal');
    const closeViewTicketsBtn = document.getElementById('closeViewTicketsModal');
    const viewTicketsModalBody = document.getElementById('viewTicketsModalBody');
    const viewTicketsModalTitle = document.getElementById('viewTicketsModalTitle');

    function openViewTicketsModal() {
        viewTicketsModal.classList.add('open');
        viewTicketsModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeViewTicketsModal() {
        viewTicketsModal.classList.remove('open');
        viewTicketsModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    closeViewTicketsBtn?.addEventListener('click', closeViewTicketsModal);
    viewTicketsModal?.addEventListener('click', (e) => {
        if (e.target === viewTicketsModal) closeViewTicketsModal();
    });

    // Handle View Tickets button clicks
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('view-tickets-btn')) {
            const btn = e.target;
            const violatorKey = btn.getAttribute('data-violator-key');
            const violatorName = btn.getAttribute('data-violator-name');
            
            if (!violatorKey) {
                console.error('Missing violator key');
                return;
            }
            
            // Find violator data
            const violator = violatorsData.find(v => v.identifier === violatorKey);
            
            if (!violator) {
                viewTicketsModalBody.innerHTML = `
                    <div style="text-align: center; padding: 40px;">
                        <p style="color: #e53935;">Violator data not found.</p>
                        <button type="button" class="btn btn-light" onclick="closeViewTicketsModal()" style="margin-top: 12px;">Close</button>
                    </div>
                `;
                openViewTicketsModal();
                return;
            }
            
            viewTicketsModalTitle.textContent = `Tickets for ${violatorName}`;
            displayTickets(violator);
            openViewTicketsModal();
        }
    });

    function displayTickets(violator) {
        const tickets = violator.tickets || [];
        
        if (tickets.length === 0) {
            viewTicketsModalBody.innerHTML = `
                <div style="text-align: center; padding: 40px;">
                    <p style="color: #6b7280;">No tickets found for this violator.</p>
                    <button type="button" class="btn btn-light" onclick="closeViewTicketsModal()" style="margin-top: 12px;">Close</button>
                </div>
            `;
            return;
        }
        
        const unpaidCount = violator.dss_unpaid_violation_count || violator.unpaid_count;
        const penaltyLevel = violator.dss_penalty_level || null;
        const penaltyIncrease = violator.dss_total_penalty_increase || 0;
        const totalFine = parseFloat(violator.total_fine || 0) + parseFloat(penaltyIncrease);
        
        let penaltyBadge = '';
        if (penaltyLevel) {
            if (penaltyLevel === 'warning') {
                penaltyBadge = '<span class="badge badge-warning">Warning (2nd Violation)</span>';
            } else if (penaltyLevel === 'suspension_temp') {
                penaltyBadge = '<span class="badge badge-danger">Temporary Suspension (3rd Violation)</span>';
            } else if (penaltyLevel === 'suspension_extended') {
                penaltyBadge = '<span class="badge badge-danger">Extended Suspension (4th Violation)</span>';
            } else if (penaltyLevel === 'permanent_ban') {
                penaltyBadge = '<span class="badge badge-danger">Permanent Ban (5th+ Violation)</span>';
            }
        }
        
        let ticketsHtml = `
            <div style="margin-bottom: 16px; padding: 12px; background: #f9fafb; border-radius: 8px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
                    <div>
                        <div class="ticket-item-label">Unpaid Violations</div>
                        <div class="ticket-item-value" style="font-size: 18px; font-weight: 700;"><span class="badge badge-danger">${unpaidCount}</span></div>
                    </div>
                    <div>
                        <div class="ticket-item-label">Total Fine Amount</div>
                        <div class="ticket-item-value" style="font-size: 18px; font-weight: 700; color: #dc2626;">₱${totalFine.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>
                        ${penaltyIncrease > 0 ? `<div style="font-size: 11px; color: #6b7280; margin-top: 2px;">(+₱${penaltyIncrease.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} DSS penalty)</div>` : ''}
                    </div>
                    <div>
                        <div class="ticket-item-label">DSS Penalty Status</div>
                        <div class="ticket-item-value">${penaltyBadge || '<span class="badge badge-info">No Penalty</span>'}</div>
                    </div>
                    <div>
                        <div class="ticket-item-label">Total Tickets</div>
                        <div class="ticket-item-value">${violator.ticket_count || 0}</div>
                    </div>
                </div>
            </div>
            <h3 style="margin: 0 0 16px; font-size: 16px; font-weight: 600;">Ticket Details</h3>
        `;
        
        tickets.forEach((ticket, index) => {
            const status = ticket.status || 'Unpaid';
            const statusBadge = status === 'Paid' 
                ? '<span class="badge badge-success">Paid</span>' 
                : '<span class="badge badge-danger">Unpaid</span>';
            
            const issuedDate = ticket.issued_date 
                ? new Date(ticket.issued_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
                : 'N/A';
            
            const violations = Array.isArray(ticket.violations) ? ticket.violations : [];
            const violationsList = violations.length > 0 
                ? violations.join(', ') 
                : 'N/A';
            
            const penaltyIncrease = parseFloat(ticket.dss_penalty_fine_increase || 0);
            const basePrice = parseFloat(ticket.price || 0) - penaltyIncrease;
            const finalPrice = parseFloat(ticket.price || 0);
            
            ticketsHtml += `
                <div class="ticket-item">
                    <div class="ticket-item-header">
                        <div>
                            <strong style="font-size: 14px;">Ticket #${index + 1}</strong>
                            <span style="margin-left: 8px;">${statusBadge}</span>
                            ${ticket.dss_penalty_level ? `<span style="margin-left: 8px;" class="badge badge-warning">DSS: ${ticket.dss_penalty_level}</span>` : ''}
                        </div>
                        <div style="font-size: 13px; color: #6b7280;">
                            Citation: ${ticket.citation_number || 'N/A'}
                        </div>
                    </div>
                    <div class="ticket-item-body">
                        <div>
                            <div class="ticket-item-label">Issued Date</div>
                            <div class="ticket-item-value">${issuedDate}</div>
                        </div>
                        <div>
                            <div class="ticket-item-label">Fine Amount</div>
                            <div class="ticket-item-value">
                                ₱${finalPrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                                ${penaltyIncrease > 0 ? `<div style="font-size: 11px; color: #dc2626;">Base: ₱${basePrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} + Penalty: ₱${penaltyIncrease.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>` : ''}
                            </div>
                        </div>
                        <div>
                            <div class="ticket-item-label">Issued By</div>
                            <div class="ticket-item-value">${ticket.issued_by || 'N/A'}</div>
                        </div>
                        <div>
                            <div class="ticket-item-label">Place</div>
                            <div class="ticket-item-value">${ticket.place || 'N/A'}</div>
                        </div>
                        ${ticket.dss_notes ? `
                        <div style="grid-column: 1 / -1;">
                            <div class="ticket-item-label">DSS Notes</div>
                            <div class="ticket-item-value" style="font-size: 12px; color: #dc2626; font-weight: 600;">${ticket.dss_notes}</div>
                        </div>
                        ` : ''}
                        <div style="grid-column: 1 / -1;">
                            <div class="ticket-item-label">Violations</div>
                            <div class="ticket-item-value" style="font-size: 12px;">${violationsList}</div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        ticketsHtml += `
            <div style="display: flex; justify-content: flex-end; margin-top: 20px; border-top: 1px solid #eee; padding-top: 16px;">
                <button type="button" class="btn btn-light" onclick="closeViewTicketsModal()">Close</button>
            </div>
        `;
        
        viewTicketsModalBody.innerHTML = ticketsHtml;
    }

    // Spinner animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes spinner-border {
            to { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);
</script>
@endsection

