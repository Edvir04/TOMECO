@extends('layout.app')

@section('title', 'Violations')

@section('withSidebar')
@endsection

@section('content')
<style>
    .page-wrap{max-width:1200px;margin:24px auto;padding:0 12px}
    .toolbar{display:flex;gap:12px;align-items:center;justify-content:space-between;margin-bottom:16px}
    .btn{display:inline-flex;align-items:center;gap:8px;border:0;border-radius:8px;padding:10px 14px;cursor:pointer}
    .btn-primary{background:#FFCC3F;color:#111}
    .btn-primary:hover{background:#e5a400}
    .btn-danger{background:#e53935;color:#fff}
    .btn-light{background:#f3f4f6;color:#111}
    .btn-info{background:#3b82f6;color:#fff}
    .btn-warning{background:#f59e0b;color:#fff}
    .btn-sm{padding:6px 10px;font-size:12px}
    .table-wrap{overflow:auto;border:1px solid #e5e7eb;border-radius:10px}
    table{width:100%;border-collapse:collapse;font-size:14px}
    thead th{position:sticky;top:0;background:#fafafa;border-bottom:1px solid #e5e7eb;text-align:left;padding:10px;white-space:nowrap}
    tbody td{border-top:1px solid #f0f0f0;padding:10px;vertical-align:middle}
    .badge{display:inline-block;padding:2px 8px;border-radius:999px;font-size:12px;border:1px solid #e5e7eb;background:#fff}
    .badge-active{background:#16a34a;color:#fff;border-color:#16a34a}
    .badge-inactive{background:#6b7280;color:#fff;border-color:#6b7280}
    .table-empty{padding:18px;color:#6b7280}
    .actions{display:flex;gap:8px;flex-wrap:wrap}
    /* Modal - perfectly centered */
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

    /* Center modal with scroll if content exceeds viewport */
    .modal-card {
        background: #fff;
        width: min(640px, 95%);
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
    input[type="text"],input[type="number"],input[type="checkbox"],textarea{width:100%;border:1px solid #d1d5db;border-radius:8px;padding:10px 12px}
    textarea{min-height: 100px;resize:vertical}
    input[type="checkbox"]{width:auto;margin-right:8px}
    .alert{padding:10px 12px;border-radius:8px;margin-bottom:12px}
    .alert-success{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}
    .alert-danger{background:#fef2f2;color:#7f1d1d;border:1px solid #fecaca}
    .helper{font-size:12px;color:#6b7280}
    .checkbox-group{display:flex;align-items:center;gap:8px}
    .price-display{font-weight:600;color:#16a34a}
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

    /* Page-specific pagination spacing */
    nav[role="navigation"] .flex-fill,
    nav[role="navigation"] .flex-sm-fill {
        column-gap: 26px;
    }
    nav[role="navigation"] .pagination {
        margin-bottom: 0;
    }
    nav[role="navigation"] .small.text-muted {
        margin: 0;
    }
    nav[role="navigation"] .flex-fill:first-child,
    nav[role="navigation"] .flex-sm-fill:first-child {
        flex: 0 0 auto;
        justify-content: flex-start;
        padding-left: 12px;
        margin-right: 6px;
    }
</style>

<div class="page-wrap">
    <div class="toolbar">
        <h2 style="margin:0;">Violations</h2>
        <div style="display: flex; gap: 12px; align-items: center;">
            <input type="text" id="violationsSearch" placeholder="Search violations..." style="padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px; width: 300px; font-size: 14px;">
            @if(auth('superadmin')->check())
                <button class="btn btn-primary" id="openCreateModal" aria-haspopup="dialog" aria-controls="createModal">
                    ➕ Add Violation
                </button>
            @endif
        </div>
    </div>

    @if (session('status'))
        <div id="flash-status" class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul style="margin:0 0 0 18px;">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <div class="helper">Fix the errors and try again.</div>
        </div>
    @endif

    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Violation Name</th>
                <th>Price</th>
                <th>Status</th>
                <th>Created</th>
                <th style="width:200px;">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($violations as $violation)
                <tr>
                    <td>#{{ $violation->id }}</td>
                    <td style="max-width:400px;">{{ $violation->name }}</td>
                    <td>
                        <span class="price-display">₱{{ number_format($violation->price, 2) }}</span>
                    </td>
                    <td>
                        @if($violation->is_active)
                            <span class="badge badge-active">Active</span>
                        @else
                            <span class="badge badge-inactive">Inactive</span>
                        @endif
                    </td>
                    <td>
                        @if(!empty($violation->created_at))
                            {{ \Carbon\Carbon::parse($violation->created_at)->format('Y-m-d H:i') }}
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        <div class="actions">
                            <button type="button" class="btn btn-sm btn-info view-violation-btn" data-violation-id="{{ $violation->id }}">View</button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="table-empty">No violations found.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div style="display:flex;justify-content:flex-end;margin-top:12px;">
        {{ $violations->links() }}
    </div>
</div>

{{-- CREATE/EDIT VIOLATION MODAL --}}
<div class="modal" id="createModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="createModalTitle">
    <div class="modal-card" role="document">
        <div class="modal-head">
            <div id="createModalTitle">Add Violation</div>
            <button class="btn btn-light" id="closeCreateModal" aria-label="Close modal" style="padding: 6px 8px;">✖</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="{{ route('admin.violations.store') }}" id="createViolationForm">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="">
                <input type="hidden" name="violation_id" id="violationId" value="">

                <div class="grid">
                    <div>
                        <label>Violation Name <span style="color:#e53935;">*</span></label>
                        <textarea name="name" id="violationName" required placeholder="Enter violation description...">{{ old('name') }}</textarea>
                        <div class="helper">Enter the full description of the violation.</div>
                    </div>

                    <div class="grid grid-2">
                        <div>
                            <label>Price (₱) <span style="color:#e53935;">*</span></label>
                            <input type="number" name="price" id="violationPrice" value="{{ old('price', '500.00') }}" step="0.01" min="0" max="999999.99" required>
                            <div class="helper">Fine amount in pesos.</div>
                        </div>
                        <div>
                            <label>Status</label>
                            <div class="checkbox-group">
                                <input type="checkbox" name="is_active" id="violationIsActive" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label for="violationIsActive" style="font-weight:normal;margin:0;">Active</label>
                            </div>
                            <div class="helper">Uncheck to disable this violation.</div>
                        </div>
                    </div>
                </div>

                <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:14px">
                    <button type="button" class="btn btn-light" id="cancelCreate">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">Add Violation</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- VIEW VIOLATION MODAL --}}
<div class="modal" id="viewModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="viewModalTitle">
    <div class="modal-card" role="document">
        <div class="modal-head">
            <div id="viewModalTitle">Violation Details</div>
            <button class="btn btn-light" id="closeViewModal" aria-label="Close view modal" style="padding: 6px 8px;">✖</button>
        </div>
        <div class="modal-body" id="viewModalBody">
            <div style="text-align: center; padding: 40px;">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p style="margin-top: 12px; color: #6b7280;">Loading violation details...</p>
            </div>
        </div>
    </div>
</div>

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

    // Modal controls
    const modal = document.getElementById('createModal');
    const openBtn = document.getElementById('openCreateModal');
    const closeBtn = document.getElementById('closeCreateModal');
    const cancelBtn = document.getElementById('cancelCreate');
    const form = document.getElementById('createViolationForm');
    const formMethod = document.getElementById('formMethod');
    const violationId = document.getElementById('violationId');
    const submitBtn = document.getElementById('submitBtn');
    const modalTitle = document.getElementById('createModalTitle');

    function openModal(){
        modal.classList.add('open');
        modal.setAttribute('aria-hidden','false');
        document.body.style.overflow='hidden';
        setTimeout(()=>{
            const first = modal.querySelector('textarea[name="name"], input[name="price"]');
            if(first) first.focus();
        }, 50);
        openModal._previousActive = document.activeElement;
        document.addEventListener('keydown', handleKeydown);
    }

    function closeModal(){
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden','true');
        document.body.style.overflow='';
        if(openModal._previousActive) openModal._previousActive.focus();
        document.removeEventListener('keydown', handleKeydown);
        // Reset form
        form.reset();
        form.action = '{{ route("admin.violations.store") }}';
        formMethod.value = '';
        violationId.value = '';
        modalTitle.textContent = 'Add Violation';
        submitBtn.textContent = 'Add Violation';
        document.getElementById('violationIsActive').checked = true;
        document.getElementById('violationPrice').value = '500.00';
    }

    function handleKeydown(e){
        if(e.key === 'Escape') { closeModal(); return; }
        if(e.key === 'Tab'){
            const focusable = Array.from(modal.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled])'))
                .filter(el => el.offsetParent !== null);
            if(focusable.length === 0) return;
            const first = focusable[0], last = focusable[focusable.length-1];
            if(e.shiftKey && document.activeElement === first){ e.preventDefault(); last.focus(); }
            else if(!e.shiftKey && document.activeElement === last){ e.preventDefault(); first.focus(); }
        }
    }

    openBtn?.addEventListener('click', openModal);
    closeBtn?.addEventListener('click', closeModal);
    cancelBtn?.addEventListener('click', closeModal);
    modal?.addEventListener('click', (e)=>{ if(e.target === modal) closeModal(); });

    // If there are validation errors from server and page reloaded,
    // automatically open modal so users can see the errors.
    @if ($errors->any())
        setTimeout(()=>{ openModal(); }, 80);
    @endif

    // View Modal functionality
    const viewModal = document.getElementById('viewModal');
    const closeViewBtn = document.getElementById('closeViewModal');
    const viewModalBody = document.getElementById('viewModalBody');

    function openViewModal() {
        viewModal.classList.add('open');
        viewModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeViewModal() {
        viewModal.classList.remove('open');
        viewModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        viewModalBody.innerHTML = '<div style="text-align: center; padding: 40px;"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div><p style="margin-top: 12px; color: #6b7280;">Loading violation details...</p></div>';
        // Restore original modal header
        const modalHead = document.querySelector('#viewModal .modal-head');
        if (modalHead) {
            modalHead.innerHTML = `
                <div id="viewModalTitle">Violation Details</div>
                <button class="btn btn-light" id="closeViewModal" aria-label="Close view modal" style="padding: 6px 8px;">✖</button>
            `;
            // Re-attach close button event
            setTimeout(() => {
                const closeBtn = document.getElementById('closeViewModal');
                if (closeBtn) {
                    closeBtn.addEventListener('click', closeViewModal);
                }
            }, 10);
        }
    }
    
    // Function to open edit modal from view modal
    function openEditViolationModal(id) {
        if (!id) {
            console.error('Missing violation ID');
            return;
        }
        
        // Close view modal first
        closeViewModal();
        
        // Fetch violation data and populate form
        fetch(`/admin/violations/${id}`)
            .then(response => response.json())
            .then(violation => {
                // Update modal title
                modalTitle.textContent = 'Edit Violation';
                
                // Update form action and method
                form.action = `/admin/violations/${id}`;
                formMethod.value = 'PUT';
                violationId.value = id;
                
                // Populate form fields
                document.getElementById('violationName').value = violation.name || '';
                document.getElementById('violationPrice').value = violation.price || '500.00';
                document.getElementById('violationIsActive').checked = violation.is_active ? true : false;
                
                // Update submit button text
                submitBtn.textContent = 'Update Violation';
                
                // Open modal
                openModal();
            })
            .catch(error => {
                console.error('Error fetching violation:', error);
                alert('Error loading violation data. Please try again.');
            });
    }
    
    // Function to confirm delete from view modal
    function confirmDeleteViolation(id) {
        if (!id) return;
        
        if (confirm('Are you sure you want to delete this violation? This action cannot be undone.')) {
            // Create a form to submit DELETE request
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/violations/${id}`;
            
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

    closeViewBtn?.addEventListener('click', closeViewModal);
    viewModal?.addEventListener('click', (e) => {
        if (e.target === viewModal) closeViewModal();
    });
    
    // Search functionality
    const violationsSearch = document.getElementById('violationsSearch');
    const violationsTable = document.querySelector('.table-wrap table');
    
    if (violationsSearch && violationsTable) {
        violationsSearch.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase().trim();
            const rows = violationsTable.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // Handle View button clicks
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('view-violation-btn')) {
            const btn = e.target;
            const violationId = btn.getAttribute('data-violation-id');
            
            if (!violationId) {
                console.error('Missing violation ID');
                return;
            }
            
            openViewModal();
            
            fetch(`/admin/violations/${violationId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to fetch violation');
                    }
                    return response.json();
                })
                .then(violation => {
                    displayViolationDetails(violation);
                })
                .catch(error => {
                    console.error('Error fetching violation:', error);
                    viewModalBody.innerHTML = `
                        <div style="text-align: center; padding: 40px;">
                            <p style="color: #e53935;">Error loading violation details. Please try again.</p>
                            <button type="button" class="btn btn-light" onclick="closeViewModal()" style="margin-top: 12px;">Close</button>
                        </div>
                    `;
                });
        }
    });

    function displayViolationDetails(violation) {
        const statusClass = violation.is_active ? 'badge-active' : 'badge-inactive';
        const statusText = violation.is_active ? 'Active' : 'Inactive';
        const createdDate = violation.created_at ? new Date(violation.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'N/A';
        const updatedDate = violation.updated_at ? new Date(violation.updated_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'N/A';
        
        // Check if user is superadmin (from PHP)
        const isSuperadmin = @json(auth('superadmin')->check());
        
        // Update modal header to include Edit and Delete buttons (only for superadmin)
        const modalHead = document.querySelector('#viewModal .modal-head');
        if (modalHead) {
            if (isSuperadmin) {
                modalHead.innerHTML = `
                    <div id="viewModalTitle">Violation Details</div>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <button type="button" class="btn btn-sm btn-danger" onclick="confirmDeleteViolation(${violation.id})" style="padding: 6px 12px;">Delete</button>
                        <button type="button" class="btn btn-sm btn-primary" onclick="openEditViolationModal(${violation.id})" style="padding: 6px 12px;">Edit</button>
                        <button class="btn btn-light" id="closeViewModal" aria-label="Close view modal" style="padding: 6px 8px;">✖</button>
                    </div>
                `;
            } else {
                modalHead.innerHTML = `
                    <div id="viewModalTitle">Violation Details</div>
                    <button class="btn btn-light" id="closeViewModal" aria-label="Close view modal" style="padding: 6px 8px;">✖</button>
                `;
            }
            // Re-attach close button event after a brief delay
            setTimeout(() => {
                const closeBtn = document.getElementById('closeViewModal');
                if (closeBtn) {
                    closeBtn.addEventListener('click', closeViewModal);
                }
            }, 10);
        }
        
        viewModalBody.innerHTML = `
            <div style="display: grid; gap: 16px;">
                <div>
                    <label style="font-weight: 600; font-size: 13px; color: #6b7280; display: block; margin-bottom: 4px;">Violation Name</label>
                    <p style="margin: 0; font-size: 14px; line-height: 1.6;">${violation.name || 'N/A'}</p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #6b7280; display: block; margin-bottom: 4px;">Price</label>
                        <p style="margin: 0; font-size: 14px; font-weight: 600; color: #16a34a;">₱${parseFloat(violation.price).toFixed(2)}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #6b7280; display: block; margin-bottom: 4px;">Status</label>
                        <span class="badge ${statusClass}">${statusText}</span>
                    </div>
                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #6b7280; display: block; margin-bottom: 4px;">Created At</label>
                        <p style="margin: 0; font-size: 14px;">${createdDate}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #6b7280; display: block; margin-bottom: 4px;">Updated At</label>
                        <p style="margin: 0; font-size: 14px;">${updatedDate}</p>
                    </div>
                </div>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:20px; border-top: 1px solid #eee; padding-top: 16px;">
                <button type="button" class="btn btn-light" onclick="closeViewModal()">Close</button>
            </div>
        `;
    }

</script>
@endsection

