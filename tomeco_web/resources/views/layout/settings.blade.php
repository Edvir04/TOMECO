@extends('layout.app')

@section('title', 'Settings — TOMECO')

@section('content')
<div class="settings-page">
    <div class="settings-container">
        <div class="profile-card">
            <div class="profile-section">
                <div class="profile-picture-wrapper">
                    @if(!empty($user->profile_picture))
                        <img src="{{ $user->profile_picture }}" alt="Profile Picture" class="profile-picture" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="profile-picture-placeholder" style="display: none;">
                            {{ strtoupper(substr($user->fullname ?? 'U', 0, 1)) }}
                        </div>
                    @else
                        <div class="profile-picture-placeholder">
                            {{ strtoupper(substr($user->fullname ?? 'U', 0, 1)) }}
                        </div>
                    @endif
                </div>
                
                <div class="profile-info">
                    <div class="info-item">
                        <label>ID</label>
                        <p>{{ $user->id_number ?? 'N/A' }}</p>
                    </div>
                    <div class="info-item">
                        <label>Name</label>
                        <p>{{ $user->fullname ?? 'N/A' }}</p>
                    </div>
                    <div class="info-item">
                        <label>Role</label>
                        <p class="role-badge {{ $role === 'admin' || $role === 'superadmin' ? 'role-admin' : 'role-other' }}">
                            {{ ucfirst($role) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="profile-actions">
                <button class="btn-personal-info" onclick="showPersonalInfo()">
                    Personal Information
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Personal Information Modal -->
<div id="personalInfoModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Personal Information</h2>
            <button class="modal-close" onclick="closePersonalInfoModal()">&times;</button>
        </div>
        <div class="modal-body" id="personalInfoContent">
            <div class="loading-spinner">
                <div class="spinner"></div>
                <p>Loading personal information...</p>
            </div>
        </div>
    </div>
</div>

<style>
.settings-page {
    max-width: 1100px;
    margin: 0 auto;
    padding: 24px 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: calc(100vh - 200px);
}

.settings-container {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
    flex: 1;
}

.profile-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    padding: 40px;
    width: 100%;
    max-width: 500px;
    text-align: center;
}

.profile-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 24px;
    margin-bottom: 32px;
    padding-bottom: 32px;
    border-bottom: 1px solid #e5e7eb;
}

.profile-picture-wrapper {
    position: relative;
}

.profile-picture {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #e5e7eb;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.profile-picture-placeholder {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, #8B0000, #C00000);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
    font-weight: 700;
    color: #fff;
    border: 4px solid #e5e7eb;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.profile-info {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 20px;
    align-items: center;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 6px;
    width: 100%;
    align-items: center;
}

.info-item label {
    font-size: 13px;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-item p {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
}

.role-badge {
    display: inline-block;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    width: fit-content;
}

.role-admin {
    background: #dbeafe;
    color: #1e40af;
}

.role-other {
    background: #f3f4f6;
    color: #4b5563;
}

.profile-actions {
    display: flex;
    justify-content: center;
}

.btn-personal-info {
    background: linear-gradient(135deg, #8B0000, #C00000);
    color: #fff;
    border: none;
    padding: 12px 32px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(139, 0, 0, 0.2);
}

.btn-personal-info:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(139, 0, 0, 0.3);
}

.btn-personal-info:active {
    transform: translateY(0);
}

/* Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-content {
    background: #fff;
    border-radius: 16px;
    width: 90%;
    max-width: 700px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: slideUp 0.3s ease;
    margin: auto;
}

@keyframes slideUp {
    from {
        transform: translateY(20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px;
    border-bottom: 1px solid #e5e7eb;
}

.modal-header h2 {
    margin: 0;
    font-size: 24px;
    font-weight: 700;
    color: #1f2937;
}

.modal-close {
    background: none;
    border: none;
    font-size: 32px;
    color: #6b7280;
    cursor: pointer;
    line-height: 1;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.modal-close:hover {
    background: #f3f4f6;
    color: #1f2937;
}

.modal-body {
    padding: 24px;
}

.loading-spinner {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px;
    gap: 16px;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #f3f4f6;
    border-top: 4px solid #8B0000;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.personal-info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.personal-info-item {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.personal-info-item label {
    font-size: 13px;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.personal-info-item p {
    margin: 0;
    font-size: 16px;
    font-weight: 500;
    color: #1f2937;
}

.personal-info-item input,
.personal-info-item select,
.personal-info-item textarea {
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 16px;
    font-weight: 500;
    color: #1f2937;
    width: 100%;
    transition: border-color 0.2s ease;
}

.personal-info-item input[type="file"] {
    padding: 8px;
    cursor: pointer;
}

.personal-info-item input[type="file"]::-webkit-file-upload-button {
    background: linear-gradient(135deg, #8B0000, #C00000);
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    margin-right: 12px;
    transition: all 0.2s ease;
}

.personal-info-item input[type="file"]::-webkit-file-upload-button:hover {
    background: linear-gradient(135deg, #A00000, #D00000);
}

.personal-info-item input:focus,
.personal-info-item select:focus,
.personal-info-item textarea:focus {
    outline: none;
    border-color: #8B0000;
    box-shadow: 0 0 0 3px rgba(139, 0, 0, 0.1);
}

.personal-info-item textarea {
    resize: vertical;
    min-height: 80px;
}

.personal-info-full {
    grid-column: span 2;
}

.modal-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid #e5e7eb;
}

.btn-edit,
.btn-save,
.btn-cancel {
    padding: 10px 24px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
}

.btn-edit {
    background: linear-gradient(135deg, #8B0000, #C00000);
    color: #fff;
    box-shadow: 0 2px 8px rgba(139, 0, 0, 0.2);
}

.btn-edit:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(139, 0, 0, 0.3);
}

.btn-save {
    background: #10b981;
    color: #fff;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.2);
}

.btn-save:hover {
    background: #059669;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-cancel {
    background: #f3f4f6;
    color: #4b5563;
}

.btn-cancel:hover {
    background: #e5e7eb;
}

@media (max-width: 640px) {
    .settings-page {
        padding: 16px;
    }

    .profile-card {
        padding: 24px;
    }

    .profile-picture,
    .profile-picture-placeholder {
        width: 100px;
        height: 100px;
    }

    .profile-picture-placeholder {
        font-size: 40px;
    }

    .personal-info-grid {
        grid-template-columns: 1fr;
    }

    .personal-info-full {
        grid-column: span 1;
    }

    .modal-content {
        width: 95%;
        margin: 20px;
    }

    .modal-actions {
        flex-direction: column;
    }

    .btn-edit,
    .btn-save,
    .btn-cancel {
        width: 100%;
    }
}
</style>

<script>
let isEditMode = false;
let currentUserData = null;

function showPersonalInfo() {
    const modal = document.getElementById('personalInfoModal');
    const content = document.getElementById('personalInfoContent');
    const title = document.getElementById('modalTitle');
    
    // Reset to view mode
    isEditMode = false;
    title.textContent = 'Personal Information';
    
    // Show modal with loading state
    modal.style.display = 'flex';
    content.innerHTML = `
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p>Loading personal information...</p>
        </div>
    `;
    
    // Fetch personal information
    fetch('{{ route("admin.settings.personal-info") }}', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Failed to fetch personal information');
        }
        return response.json();
    })
    .then(data => {
        currentUserData = data;
        displayPersonalInfo(data);
    })
    .catch(error => {
        console.error('Error:', error);
        content.innerHTML = `
            <div style="text-align: center; padding: 40px;">
                <p style="color: #e53935; margin-bottom: 16px;">Error loading personal information. Please try again.</p>
                <button class="btn-personal-info" onclick="showPersonalInfo()" style="padding: 8px 24px; font-size: 14px;">Retry</button>
            </div>
        `;
    });
}

function displayPersonalInfo(data) {
    const roleLabel = data.role === 'superadmin' ? 'Super Admin' : 
                     data.role === 'admin' ? 'Admin' : 'Tomeco Enforcer';
    const createdDate = data.created_at ? new Date(data.created_at).toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    }) : 'N/A';
    
    const content = document.getElementById('personalInfoContent');
    
    // Handle profile picture URL - ensure it's a full URL
    let profilePictureUrl = data.profile_picture || '';
    if (profilePictureUrl && !profilePictureUrl.startsWith('http') && !profilePictureUrl.startsWith('/')) {
        profilePictureUrl = '/storage/' + profilePictureUrl;
    }
    
    content.innerHTML = `
        <div style="display: flex; align-items: center; gap: 16px; padding-bottom: 20px; margin-bottom: 20px; border-bottom: 1px solid #e5e7eb;">
            ${profilePictureUrl ? 
                `<img src="${profilePictureUrl}" alt="Profile" style="width: 64px; height: 64px; border-radius: 50%; object-fit: cover; border: 2px solid #e5e7eb;" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div style="width: 64px; height: 64px; border-radius: 50%; background: linear-gradient(135deg, #8B0000, #C00000); display: none; align-items: center; justify-content: center; font-weight: 700; font-size: 24px; color: #fff; border: 2px solid #e5e7eb;">${data.fullname ? data.fullname.charAt(0).toUpperCase() : '?'}</div>` :
                `<div style="width: 64px; height: 64px; border-radius: 50%; background: linear-gradient(135deg, #8B0000, #C00000); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 24px; color: #fff; border: 2px solid #e5e7eb;">${data.fullname ? data.fullname.charAt(0).toUpperCase() : '?'}</div>`
            }
            <div>
                <div style="font-size: 20px; font-weight: 600; margin-bottom: 4px;">${data.fullname || 'N/A'}</div>
                <span class="role-badge ${data.role === 'admin' || data.role === 'superadmin' ? 'role-admin' : 'role-other'}" style="font-size: 12px;">${roleLabel}</span>
            </div>
        </div>
        <div class="personal-info-grid">
            <div class="personal-info-item">
                <label>Username</label>
                <p>${data.username || 'N/A'}</p>
            </div>
            <div class="personal-info-item">
                <label>ID Number</label>
                <p>${data.id_number || '—'}</p>
            </div>
            <div class="personal-info-item">
                <label>Gender</label>
                <p>${data.gender ? data.gender.charAt(0).toUpperCase() + data.gender.slice(1) : 'N/A'}</p>
            </div>
            <div class="personal-info-item">
                <label>Date of Birth</label>
                <p>${data.dob || '—'}</p>
            </div>
            <div class="personal-info-item">
                <label>Contact Number</label>
                <p>${data.contact_number || 'N/A'}</p>
            </div>
            <div class="personal-info-item">
                <label>Account Created</label>
                <p>${createdDate}</p>
            </div>
            <div class="personal-info-item personal-info-full">
                <label>Address</label>
                <p>${data.address || 'N/A'}</p>
            </div>
        </div>
        <div class="modal-actions">
            <button class="btn-edit" onclick="enableEditMode()">Edit</button>
        </div>
    `;
}

function enableEditMode() {
    isEditMode = true;
    const title = document.getElementById('modalTitle');
    title.textContent = 'Edit Personal Information';
    
    const data = currentUserData;
    const content = document.getElementById('personalInfoContent');
    
    content.innerHTML = `
        <form id="editPersonalInfoForm" onsubmit="savePersonalInfo(event)">
            <div class="personal-info-grid">
                <div class="personal-info-item">
                    <label>Full Name</label>
                    <input type="text" name="fullname" value="${data.fullname || ''}" required>
                </div>
                <div class="personal-info-item">
                    <label>Username</label>
                    <input type="text" name="username" value="${data.username || ''}" required>
                </div>
                <div class="personal-info-item">
                    <label>ID Number</label>
                    <input type="text" name="id_number" value="${data.id_number || ''}" required>
                </div>
                <div class="personal-info-item">
                    <label>Gender</label>
                    <select name="gender" required>
                        <option value="male" ${data.gender === 'male' ? 'selected' : ''}>Male</option>
                        <option value="female" ${data.gender === 'female' ? 'selected' : ''}>Female</option>
                        <option value="other" ${data.gender === 'other' ? 'selected' : ''}>Other</option>
                    </select>
                </div>
                <div class="personal-info-item">
                    <label>Date of Birth</label>
                    <input type="date" name="dob" value="${data.dob || ''}" required>
                </div>
                <div class="personal-info-item">
                    <label>Contact Number</label>
                    <input type="text" name="contact_number" value="${data.contact_number || ''}" required>
                </div>
                <div class="personal-info-item personal-info-full">
                    <label>Address</label>
                    <textarea name="address" required>${data.address || ''}</textarea>
                </div>
                <div class="personal-info-item personal-info-full">
                    <label>Profile Picture</label>
                    <input type="file" name="profile_picture" id="profilePictureUpload" accept="image/*" onchange="previewImage(this)">
                    <small class="text-muted" style="display: block; margin-top: 4px; font-size: 12px; color: #6b7280;">Accepted formats: JPG, PNG, GIF (Max size: 5MB)</small>
                    <div id="imagePreview" style="margin-top: 12px; display: ${data.profile_picture ? 'block' : 'none'};">
                        <img id="previewImg" src="${data.profile_picture || ''}" alt="Preview" style="max-width: 100%; max-height: 300px; border-radius: 8px; border: 1px solid #d1d5db; display: block;">
                        ${data.profile_picture ? `<button type="button" onclick="removeImage()" class="btn-cancel" style="margin-top: 8px; padding: 6px 16px;">Remove Image</button>` : ''}
                    </div>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="cancelEdit()">Cancel</button>
                <button type="submit" class="btn-save">Save Changes</button>
            </div>
        </form>
    `;
}

function cancelEdit() {
    if (currentUserData) {
        displayPersonalInfo(currentUserData);
    }
}

function savePersonalInfo(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    const content = document.getElementById('personalInfoContent');
    content.innerHTML = `
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p>Saving changes...</p>
        </div>
    `;
    
    fetch('{{ route("admin.settings.update") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            currentUserData = result.user;
            displayPersonalInfo(result.user);
            // Show success message
            const successMsg = document.createElement('div');
            successMsg.style.cssText = 'background: #10b981; color: white; padding: 12px; border-radius: 6px; margin-bottom: 16px; text-align: center;';
            successMsg.textContent = result.message || 'Personal information updated successfully!';
            content.insertBefore(successMsg, content.firstChild);
            setTimeout(() => successMsg.remove(), 3000);
        } else {
            throw new Error(result.message || 'Failed to update');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        content.innerHTML = `
            <div style="text-align: center; padding: 40px;">
                <p style="color: #e53935; margin-bottom: 16px;">Error: ${error.message || 'Failed to update personal information. Please try again.'}</p>
                <button class="btn-cancel" onclick="enableEditMode()">Go Back to Edit</button>
            </div>
        `;
    });
}

function closePersonalInfoModal() {
    document.getElementById('personalInfoModal').style.display = 'none';
    isEditMode = false;
    currentUserData = null;
}

// Close modal when clicking outside
document.getElementById('personalInfoModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePersonalInfoModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !isEditMode) {
        closePersonalInfoModal();
    }
});

// Image Preview functionality
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    
    // Check if elements exist
    if (!preview || !previewImg) {
        console.error('Preview elements not found');
        return;
    }
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Validate file type
        if (!file.type.match('image.*')) {
            alert('Please select a valid image file.');
            input.value = '';
            if (preview) preview.style.display = 'none';
            return;
        }
        
        // Validate file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            alert('Image size must be less than 5MB.');
            input.value = '';
            if (preview) preview.style.display = 'none';
            return;
        }
        
        const reader = new FileReader();
        
        reader.onload = function(e) {
            if (previewImg) {
                previewImg.src = e.target.result;
                previewImg.style.display = 'block';
            }
            if (preview) {
                preview.style.display = 'block';
                // Remove existing remove button if any
                const existingBtn = preview.querySelector('button');
                if (existingBtn) {
                    existingBtn.remove();
                }
                // Add remove button
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'btn-cancel';
                removeBtn.style.cssText = 'margin-top: 8px; padding: 6px 16px;';
                removeBtn.textContent = 'Remove Image';
                removeBtn.onclick = removeImage;
                preview.appendChild(removeBtn);
            }
        };
        
        reader.onerror = function() {
            alert('Error reading the file. Please try again.');
            input.value = '';
            if (preview) preview.style.display = 'none';
        };
        
        reader.readAsDataURL(file);
    } else {
        if (preview) preview.style.display = 'none';
    }
}

function removeImage() {
    const input = document.getElementById('profilePictureUpload');
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    
    if (input) {
        input.value = '';
    }
    if (preview) {
        preview.style.display = 'none';
    }
    if (previewImg) {
        previewImg.src = '';
    }
}
</script>
@endsection
