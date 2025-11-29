@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Employee Accounts</h1>

    <!-- Search form -->
    <form method="GET" action="{{ route('employees-account') }}" class="mb-4">
        <div class="input-group">
            <input type="text" class="form-control" name="search" placeholder="Search employees..." value="{{ request()->input('search') }}">
            <button class="btn btn-primary" type="submit">Search</button>
        </div>
    </form>

    <!-- Success message -->
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Button to create new employee -->
    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#createEmployeeModal">Create New Employee</button>

    <!-- Employee Table -->
    @if (isset($employees))  
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>username</th>
                    <th>Phone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($employees as $employee)
                    <tr>
                        <td>{{ $employee->id }}</td>
                        <td>{{ $employee->username }}</td>
                        <td>{{ $employee->username }}</td>
                        <td>{{ $employee->phone }}</td>
                        <td>
                            <!-- Edit button (opens modal) -->
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editEmployeeModal" data-id="{{ $employee->id }}" data-username="{{ $employee->username }}" data-username="{{ $employee->username }}" data-phone="{{ $employee->phone }}">Edit</button>

                            <!-- Delete form -->
                            <form action="{{ route('employees-account.destroy', $employee->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="d-flex justify-content-center">
            {{ $employees->links('pagination::bootstrap-4') }}
        </div>
    @endif
</div>

<!-- Modal for Create Employee -->
<div class="modal fade" id="createEmployeeModal" tabindex="-1" aria-labelledby="createEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createEmployeeModalLabel">Create New Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('employees-account.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="username">username</label>
                        <input type="username" name="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="text" name="phone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="birthdate">Birthdate</label>
                        <input type="date" name="birthdate" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Create Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal for Edit Employee -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEmployeeModalLabel">Edit Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('employees-account.update', ':id') }}" method="POST" id="editEmployeeForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="editUsername">Username</label>
                        <input type="text" name="username" id="editUsername" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="editusername">username</label>
                        <input type="username" name="username" id="editusername" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="editPhone">Phone</label>
                        <input type="text" name="phone" id="editPhone" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="editBirthdate">Birthdate</label>
                        <input type="date" name="birthdate" id="editBirthdate" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Populate Edit modal with employee data
    const editModal = document.getElementById('editEmployeeModal');
    editModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const employeeId = button.getAttribute('data-id');
        const username = button.getAttribute('data-username');
        const username = button.getAttribute('data-username');
        const phone = button.getAttribute('data-phone');

        const form = document.getElementById('editEmployeeForm');
        form.action = form.action.replace(':id', employeeId);

        document.getElementById('editUsername').value = username;
        document.getElementById('editusername').value = username;
        document.getElementById('editPhone').value = phone;
    });
</script>
@endpush
