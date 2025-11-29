@extends('layout.app')

@section('title', 'Accounts')

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
    .btn-sm{padding:6px 10px;font-size:12px}
    .table-wrap{overflow:auto;border:1px solid #e5e7eb;border-radius:10px}
    table{width:100%;border-collapse:collapse;font-size:14px}
    thead th{position:sticky;top:0;background:#fafafa;border-bottom:1px solid #e5e7eb;text-align:left;padding:10px;white-space:nowrap}
    tbody td{border-top:1px solid #f0f0f0;padding:10px;vertical-align:middle}
    .badge{display:inline-block;padding:2px 8px;border-radius:999px;font-size:12px;border:1px solid #e5e7eb;background:#fff}
    .role-superadmin{background:#111;color:#fff;border-color:#111}
    .role-admin{background:#2563eb;color:#fff;border-color:#2563eb}
    .role-enforcer{background:#16a34a;color:#fff;border-color:#16a34a}
    .avatar{width:36px;height:36px;border-radius:999px;object-fit:cover;border:1px solid #eee}
    .cell-flex{display:flex;align-items:center;gap:10px}
    .muted{color:#6b7280;font-size:12px}
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
    input[type="text"],input[type="date"],input[type="password"],input[type="file"],select{width:100%;border:1px solid #d1d5db;border-radius:8px;padding:10px 12px}
    input[type="file"]{padding:8px}
    .alert{padding:10px 12px;border-radius:8px;margin-bottom:12px}
    .alert-success{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}
    .alert-danger{background:#fef2f2;color:#7f1d1d;border:1px solid #fecaca}
    .helper{font-size:12px;color:#6b7280}
    .table-empty{padding:18px;color:#6b7280}
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
</style>

<div class="page-wrap">
    <div class="toolbar">
        <h2 style="margin:0;">Accounts</h2>
        <button class="btn btn-primary" id="openCreateModal" aria-haspopup="dialog" aria-controls="createModal">
            ➕ Create Account
        </button>
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
                <th>Role</th>
                <th>Account</th>
                <th>Username</th>
                <th>ID Number</th>
                <th>Gender</th>
                <th>DOB</th>
                <th>Contact</th>
                <th>Address</th>
                <th>Created</th>
                <th style="width:180px;">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($accounts as $a)
                <tr>
                    <td>
                        @php
                            $roleClass = match($a['role']) {
                                'superadmin' => 'role-superadmin',
                                'admin' => 'role-admin',
                                default => 'role-enforcer'
                            };
                        @endphp
                        <span class="badge {{ $roleClass }}">{{ ucfirst($a['role']) }}</span>
                    </td>
                    <td>
                        <div class="cell-flex">
                            @php
                                $profilePictureUrl = $a['profile_picture'] ?? '';
                                if ($profilePictureUrl && !str_starts_with($profilePictureUrl, 'http') && !str_starts_with($profilePictureUrl, '/')) {
                                    $profilePictureUrl = '/storage/' . $profilePictureUrl;
                                }
                            @endphp
                            @if(!empty($profilePictureUrl))
                                <img src="{{ $profilePictureUrl }}" class="avatar" alt="avatar" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='grid';">
                                <div class="avatar" style="display:none;place-items:center;background:linear-gradient(135deg, #8B0000, #C00000);color:#fff;font-weight:700;">
                                    {{ strtoupper(substr($a['fullname'],0,1)) }}
                                </div>
                            @else
                                <div class="avatar" style="display:grid;place-items:center;background:linear-gradient(135deg, #8B0000, #C00000);color:#fff;font-weight:700;">
                                    {{ strtoupper(substr($a['fullname'],0,1)) }}
                                </div>
                            @endif
                            <div>
                                <div style="font-weight:600">{{ $a['fullname'] }}</div>
                                <div class="muted">#{{ $a['id'] }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $a['username'] }}</td>
                    <td>{{ $a['id_number'] ?: '—' }}</td>
                    <td>{{ ucfirst($a['gender']) }}</td>
                    <td>{{ $a['dob'] ?: '—' }}</td>
                    <td>{{ $a['contact_number'] }}</td>
                    <td style="max-width:280px">{{ $a['address'] }}</td>
                    <td>
                        @if(!empty($a['created_at']))
                            {{ \Carbon\Carbon::parse($a['created_at'])->format('Y-m-d H:i') }}
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        <div class="actions">
                            <button type="button" class="btn btn-sm btn-info view-account-btn" data-role="{{ $a['role'] }}" data-account-id="{{ $a['id'] }}">View</button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="10"><div class="table-empty">No accounts found.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- CREATE ACCOUNT MODAL --}}
<div class="modal" id="createModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="createModalTitle">
    <div class="modal-card" role="document">
        <div class="modal-head">
            <div id="createModalTitle">Create Account</div>
            <button class="btn btn-light" id="closeCreateModal" aria-label="Close create account dialog" style="padding: 6px 8px;">✖</button>
        </div>
        <div class="modal-body">
            <form method="POST" action="{{ route('admin.accounts.store') }}" enctype="multipart/form-data" id="createAccountForm">
                @csrf

                <div class="grid grid-2" id="createFormGrid">
                    <div>
                        <label>Account Type</label>
                        <select name="role" required>
                            <option value="" disabled selected>Select role</option>
                            @if($currentRole === 'superadmin')
                                <option value="superadmin" {{ old('role') === 'superadmin' ? 'selected' : '' }}>Super Admin</option>
                                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="enforcer" {{ old('role') === 'enforcer' ? 'selected' : '' }}>Tomeco Enforcer</option>
                            @elseif($currentRole === 'admin')
                                <option value="enforcer" {{ old('role') === 'enforcer' ? 'selected' : '' }}>Tomeco Enforcer</option>
                            @endif
                        </select>
                    </div>
                    <div>
                        <label>Gender</label>
                        <select name="gender" required>
                            <option value="male" {{ old('gender')==='male'?'selected':'' }}>Male</option>
                            <option value="female" {{ old('gender')==='female'?'selected':'' }}>Female</option>
                            <option value="other" {{ old('gender')==='other'?'selected':'' }}>Other</option>
                        </select>
                    </div>

                    <div>
                        <label>Fullname</label>
                        <input type="text" name="fullname" value="{{ old('fullname') }}" required>
                    </div>
                    <div>
                        <label>Username</label>
                        <input type="text" name="username" value="{{ old('username') }}" required>
                    </div>
                    <div>
                        <label>ID Number</label>
                        <input type="text" name="id_number" value="{{ old('id_number') }}" placeholder="e.g. TOMECO-2024-001" required>
                    </div>

                    <div>
                        <label>Password</label>
                        <input type="password" name="password" required>
                        <div class="helper">Min 8 characters.</div>
                    </div>
                    <div>
                        <label>Confirm Password</label>
                        <input type="password" name="password_confirmation" required>
                    </div>

                    <div>
                        <label>Date of Birth</label>
                        <input type="date" name="dob" value="{{ old('dob') }}" required>
                    </div>
                    <div>
                        <label>Contact Number</label>
                        <input type="text" name="contact_number" value="{{ old('contact_number') }}"
                               pattern="^09\d{9}$" placeholder="09xxxxxxxxxx" required>
                        <div class="helper">PH format: 09XXXXXXXXX</div>
                    </div>

                    <div class="grid-2" style="grid-column:1 / -1">
                        <div>
                            <label>Address</label>
                            <input type="text" name="address" value="{{ old('address') }}" required>
                        </div>
                        <div>
                            <label>Profile Picture</label>
                            <input type="file" name="profile_picture" id="profilePictureUpload" accept="image/*" onchange="previewImage(this)">
                            <small class="text-muted" style="display: block; margin-top: 4px; font-size: 12px; color: #6b7280;">Accepted formats: JPG, PNG, GIF (Max size: 5MB)</small>
                            <div id="imagePreview" style="margin-top: 12px; display: none;">
                                <img id="previewImg" src="" alt="Preview" style="max-width: 100%; max-height: 300px; border-radius: 8px; border: 1px solid #d1d5db;">
                                <button type="button" onclick="removeImage()" class="btn btn-light" style="margin-top: 8px;">Remove Image</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:14px">
                    <button type="button" class="btn btn-light" id="cancelCreate">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- VIEW ACCOUNT MODAL --}}
<div class="modal" id="viewModal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="viewModalTitle">
    <div class="modal-card" role="document">
        <div class="modal-head">
            <div id="viewModalTitle">Account Details</div>
            <button class="btn btn-light" id="closeViewModal" aria-label="Close view account dialog" style="padding: 6px 8px;">✖</button>
        </div>
        <div class="modal-body" id="viewModalBody">
            <div style="text-align: center; padding: 40px;">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p style="margin-top: 12px; color: #6b7280;">Loading account details...</p>
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

    // Modal controls with better centering + accessibility
    const modal = document.getElementById('createModal');
    const openBtn = document.getElementById('openCreateModal');
    const closeBtn = document.getElementById('closeCreateModal');
    const cancelBtn = document.getElementById('cancelCreate');
    const firstInputSelector = 'select[name="role"], input[name="fullname"], input[name="username"]';

    function openModal(){
        modal.classList.add('open');
        modal.setAttribute('aria-hidden','false');
        document.body.style.overflow='hidden';
        // focus the first form control (small delay to ensure visible)
        setTimeout(()=>{
            const first = modal.querySelector(firstInputSelector);
            if(first) first.focus();
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

    openBtn?.addEventListener('click', openModal);
    closeBtn?.addEventListener('click', closeModal);
    cancelBtn?.addEventListener('click', closeModal);
    modal?.addEventListener('click', (e)=>{ if(e.target === modal) closeModal(); });

    // Image Preview functionality
    function previewImage(input) {
        const preview = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');
        
        if (input.files && input.files[0]) {
            const file = input.files[0];
            
            // Validate file type
            if (!file.type.match('image.*')) {
                alert('Please select a valid image file.');
                input.value = '';
                preview.style.display = 'none';
                return;
            }
            
            // Validate file size (5MB max)
            if (file.size > 5 * 1024 * 1024) {
                alert('Image size must be less than 5MB.');
                input.value = '';
                preview.style.display = 'none';
                return;
            }
            
            const reader = new FileReader();
            
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
            };
            
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
        }
    }
    
    function removeImage() {
        const input = document.getElementById('profilePictureUpload');
        const preview = document.getElementById('imagePreview');
        input.value = '';
        preview.style.display = 'none';
    }

    // If there are validation errors from server and page reloaded,
    // automatically open modal so users can see the errors.
    @if ($errors->any())
        setTimeout(()=>{ openModal(); }, 80);
    @endif

    // View Modal functionality
    const viewModal = document.getElementById('viewModal');
    const closeViewBtn = document.getElementById('closeViewModal');
    const viewModalBody = document.getElementById('viewModalBody');
    let currentAccountId = null;
    let currentAccountRole = null;
    let currentAccountData = null;

    function openViewModal() {
        viewModal.classList.add('open');
        viewModal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
    }

    function closeViewModal() {
        viewModal.classList.remove('open');
        viewModal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        viewModalBody.innerHTML = '<div style="text-align: center; padding: 40px;"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div><p style="margin-top: 12px; color: #6b7280;">Loading account details...</p></div>';
        currentAccountId = null;
        currentAccountRole = null;
        currentAccountData = null;
    }

    closeViewBtn?.addEventListener('click', closeViewModal);
    viewModal?.addEventListener('click', (e) => {
        if (e.target === viewModal) closeViewModal();
    });

    // Handle View button clicks - use event delegation for dynamically loaded content
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('view-account-btn')) {
            const btn = e.target;
            const accountId = btn.getAttribute('data-account-id');
            const accountRole = btn.getAttribute('data-role');
            
            if (!accountId || !accountRole) {
                console.error('Missing account ID or role');
                return;
            }
            
            currentAccountId = accountId;
            currentAccountRole = accountRole;
            openViewModal();
            
            fetch(`/admin/accounts/${accountRole}/${accountId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to fetch account');
                    }
                    return response.json();
                })
                .then(account => {
                    currentAccountData = account;
                    displayAccountDetails(account);
                })
                .catch(error => {
                    console.error('Error fetching account:', error);
                    viewModalBody.innerHTML = `
                        <div style="text-align: center; padding: 40px;">
                            <p style="color: #e53935;">Error loading account details. Please try again.</p>
                            <button type="button" class="btn btn-light" onclick="closeViewModal()" style="margin-top: 12px;">Close</button>
                        </div>
                    `;
                });
        }
    });

    function displayAccountDetails(account) {
        const roleClass = account.role === 'superadmin' ? 'role-superadmin' : 
                         account.role === 'admin' ? 'role-admin' : 'role-enforcer';
        const roleLabel = account.role === 'superadmin' ? 'Super Admin' : 
                         account.role === 'admin' ? 'Admin' : 'Tomeco Enforcer';
        const createdDate = account.created_at ? new Date(account.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : 'N/A';
        
        // Handle profile picture URL - ensure it's a full URL
        let profilePictureUrl = account.profile_picture || '';
        if (profilePictureUrl && !profilePictureUrl.startsWith('http') && !profilePictureUrl.startsWith('/')) {
            profilePictureUrl = '/storage/' + profilePictureUrl;
        }
        
        viewModalBody.innerHTML = `
            <div style="display: grid; gap: 16px;">
                <div style="display: flex; align-items: center; gap: 12px; padding-bottom: 16px; border-bottom: 1px solid #eee;">
                    ${profilePictureUrl ? 
                        `<img src="${profilePictureUrl}" alt="Profile" style="width: 64px; height: 64px; border-radius: 50%; object-fit: cover; border: 2px solid #e5e7eb;" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div style="width: 64px; height: 64px; border-radius: 50%; background: linear-gradient(135deg, #8B0000, #C00000); display: none; align-items: center; justify-content: center; font-weight: 700; font-size: 24px; color: #fff; border: 2px solid #e5e7eb;">${account.fullname ? account.fullname.charAt(0).toUpperCase() : '?'}</div>` :
                        `<div style="width: 64px; height: 64px; border-radius: 50%; background: linear-gradient(135deg, #8B0000, #C00000); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 24px; color: #fff; border: 2px solid #e5e7eb;">${account.fullname ? account.fullname.charAt(0).toUpperCase() : '?'}</div>`
                    }
                    <div>
                        <div style="font-size: 20px; font-weight: 600; margin-bottom: 4px;">${account.fullname || 'N/A'}</div>
                        <span class="badge ${roleClass}" style="font-size: 12px;">${roleLabel}</span>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #6b7280; display: block; margin-bottom: 4px;">Username</label>
                        <p style="margin: 0; font-size: 14px;">${account.username || 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #6b7280; display: block; margin-bottom: 4px;">ID Number</label>
                        <p style="margin: 0; font-size: 14px;">${account.id_number || '—'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #6b7280; display: block; margin-bottom: 4px;">Gender</label>
                        <p style="margin: 0; font-size: 14px;">${account.gender ? account.gender.charAt(0).toUpperCase() + account.gender.slice(1) : 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #6b7280; display: block; margin-bottom: 4px;">Date of Birth</label>
                        <p style="margin: 0; font-size: 14px;">${account.dob || '—'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #6b7280; display: block; margin-bottom: 4px;">Contact Number</label>
                        <p style="margin: 0; font-size: 14px;">${account.contact_number || 'N/A'}</p>
                    </div>
                    <div>
                        <label style="font-weight: 600; font-size: 13px; color: #6b7280; display: block; margin-bottom: 4px;">Created At</label>
                        <p style="margin: 0; font-size: 14px;">${createdDate}</p>
                    </div>
                </div>
                <div>
                    <label style="font-weight: 600; font-size: 13px; color: #6b7280; display: block; margin-bottom: 4px;">Address</label>
                    <p style="margin: 0; font-size: 14px;">${account.address || 'N/A'}</p>
                </div>
            </div>
            <div style="display:flex;justify-content:space-between;gap:8px;margin-top:20px; border-top: 1px solid #eee; padding-top: 16px;">
                <div>
                    <button type="button" class="btn btn-danger" onclick="confirmDeleteAccount('${account.role}', ${account.id})">Delete</button>
                </div>
                <div style="display:flex;gap:8px;">
                    <button type="button" class="btn btn-light" onclick="closeViewModal()">Close</button>
                    <button type="button" class="btn btn-primary" onclick="openEditAccountModal('${account.role}', ${account.id})">Edit</button>
                </div>
            </div>
        `;
    }

    // Edit Account functionality - reuse create modal
    function openEditAccountModal(role, id) {
        if (!id || !role) return;
        
        // Close view modal
        closeViewModal();
        
        // Fetch account data and populate form
        fetch(`/admin/accounts/${role}/${id}`)
            .then(response => response.json())
            .then(account => {
                // Update modal title
                document.getElementById('createModalTitle').textContent = 'Edit Account';
                
                // Update form action and method
                const form = document.getElementById('createAccountForm');
                form.action = `/admin/accounts/${role}/${id}`;
                
                // Add method spoofing for PUT
                let methodInput = form.querySelector('input[name="_method"]');
                if (!methodInput) {
                    methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    form.appendChild(methodInput);
                }
                methodInput.value = 'PUT';
                
                // Populate form fields
                form.querySelector('select[name="role"]').value = account.role;
                form.querySelector('select[name="role"]').disabled = true; // Don't allow role change
                form.querySelector('input[name="fullname"]').value = account.fullname || '';
                form.querySelector('input[name="username"]').value = account.username || '';
                form.querySelector('input[name="id_number"]').value = account.id_number || '';
                form.querySelector('select[name="gender"]').value = account.gender || '';
                form.querySelector('input[name="dob"]').value = account.dob || '';
                form.querySelector('input[name="contact_number"]').value = account.contact_number || '';
                form.querySelector('input[name="address"]').value = account.address || '';
                
                // Clear password fields
                form.querySelector('input[name="password"]').value = '';
                form.querySelector('input[name="password"]').required = false;
                form.querySelector('input[name="password_confirmation"]').value = '';
                form.querySelector('input[name="password_confirmation"]').required = false;
                
                // Update password helper text
                const passwordHelper = form.querySelector('input[name="password"]').nextElementSibling;
                if (passwordHelper && passwordHelper.classList.contains('helper')) {
                    passwordHelper.textContent = 'Leave blank to keep current password.';
                }
                
                // Handle profile picture preview if exists
                const imagePreview = document.getElementById('imagePreview');
                const previewImg = document.getElementById('previewImg');
                if (account.profile_picture) {
                    // Handle profile picture URL - ensure it's a full URL
                    let profilePictureUrl = account.profile_picture || '';
                    if (profilePictureUrl && !profilePictureUrl.startsWith('http') && !profilePictureUrl.startsWith('/')) {
                        profilePictureUrl = '/storage/' + profilePictureUrl;
                    }
                    previewImg.src = profilePictureUrl;
                    imagePreview.style.display = 'block';
                } else {
                    imagePreview.style.display = 'none';
                }
                
                // Update submit button text
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) submitBtn.textContent = 'Update Account';
                
                // Open create modal (which is now in edit mode)
                openModal();
            })
            .catch(error => {
                console.error('Error fetching account:', error);
                alert('Error loading account data. Please try again.');
            });
    }

    // Delete functionality
    function confirmDeleteAccount(role, id) {
        if (!id || !role) return;
        
        if (confirm(`Are you sure you want to delete this ${role} account? This action cannot be undone.`)) {
            // Create a form to submit DELETE request
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/accounts/${role}/${id}`;
            
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

    // Reset form when opening create modal (not edit)
    const originalOpenModal = openModal;
    openModal = function() {
        // Check if form is in edit mode
        const form = document.getElementById('createAccountForm');
        const methodInput = form?.querySelector('input[name="_method"]');
        const isEditMode = methodInput && methodInput.value === 'PUT';
        
        if (!isEditMode) {
            // Reset form for create mode
            form?.reset();
            document.getElementById('createModalTitle').textContent = 'Create Account';
            if (form) {
                form.action = '{{ route("admin.accounts.store") }}';
                const roleSelect = form.querySelector('select[name="role"]');
                if (roleSelect) roleSelect.disabled = false;
                
                // Reset password fields
                const passwordInput = form.querySelector('input[name="password"]');
                const passwordConfirm = form.querySelector('input[name="password_confirmation"]');
                if (passwordInput) {
                    passwordInput.required = true;
                    const helper = passwordInput.nextElementSibling;
                    if (helper && helper.classList.contains('helper')) {
                        helper.textContent = 'Min 8 characters.';
                    }
                }
                if (passwordConfirm) passwordConfirm.required = true;
                
                // Clear image preview
                const imagePreview = document.getElementById('imagePreview');
                const imageUpload = document.getElementById('profilePictureUpload');
                if (imagePreview) imagePreview.style.display = 'none';
                if (imageUpload) imageUpload.value = '';
            }
        }
        
        originalOpenModal();
    };
</script>
@endsection
