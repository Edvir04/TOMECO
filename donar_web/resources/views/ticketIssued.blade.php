@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Ticket Issuances</h1>

    <!-- Search form -->
    <form method="GET" action="{{ route('ticket-issued') }}" class="mb-4">
        <div class="input-group">
            <input type="text" class="form-control" name="search" placeholder="Search tickets..." value="{{ request()->input('search') }}">
            <button class="btn btn-primary" type="submit">Search</button>
        </div>
    </form>

    <!-- Success message -->
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Button to create new ticket -->
    <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#createModal">Create New Ticket</button>

    <!-- Ticket Table -->
    @if (isset($tickets))  
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Driver's Name</th>
                    <th>Address</th>
                    <th>PLT Number</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tickets as $ticket)
                    <tr>
                        <td>{{ $ticket->id }}</td>
                        <td>{{ $ticket->drivers_name }}</td>
                        <td>{{ $ticket->address }}</td>
                        <td>{{ $ticket->plt_number }}</td>
                        <td>
                            <!-- Edit button (opens modal) -->
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $ticket->id }}" data-id="{{ $ticket->id }}" data-drivers_name="{{ $ticket->drivers_name }}" data-address="{{ $ticket->address }}" data-plt_number="{{ $ticket->plt_number }}">Edit</button>

                            <!-- Delete form -->
                            <form action="{{ route('ticket-issued.destroy', $ticket->id) }}" method="POST" style="display:inline-block;">
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
            {{ $tickets->links('pagination::bootstrap-4') }}  <!-- This will now work correctly -->
        </div>

    @endif
</div>

<!-- Modal for Create Ticket -->
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('ticket-issued.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">Create Ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="drivers_name">Driver's Name</label>
                        <input type="text" name="drivers_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" name="address" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="plt_number">PLT Number</label>
                        <input type="text" name="plt_number" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Create Ticket</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal for Edit Ticket -->
@foreach($tickets as $ticket)
<div class="modal fade" id="editModal{{ $ticket->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $ticket->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('ticket-issued.update', $ticket->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel{{ $ticket->id }}">Edit Ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="editDriversName{{ $ticket->id }}">Driver's Name</label>
                        <input type="text" name="drivers_name" id="editDriversName{{ $ticket->id }}" class="form-control" value="{{ $ticket->drivers_name }}" required>
                    </div>
                    <div class="form-group">
                        <label for="editAddress{{ $ticket->id }}">Address</label>
                        <input type="text" name="address" id="editAddress{{ $ticket->id }}" class="form-control" value="{{ $ticket->address }}">
                    </div>
                    <div class="form-group">
                        <label for="editPltNumber{{ $ticket->id }}">PLT Number</label>
                        <input type="text" name="plt_number" id="editPltNumber{{ $ticket->id }}" class="form-control" value="{{ $ticket->plt_number }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endforeach

@endsection

@push('scripts')
<script>
    // Populate Edit modal with ticket data
    const editModal = document.querySelectorAll('.modal.fade');
    editModal.forEach(modal => {
        modal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const ticketId = button.getAttribute('data-id');
            const driversName = button.getAttribute('data-drivers_name');
            const address = button.getAttribute('data-address');
            const pltNumber = button.getAttribute('data-plt_number');

            document.getElementById('editDriversName' + ticketId).value = driversName;
            document.getElementById('editAddress' + ticketId).value = address;
            document.getElementById('editPltNumber' + ticketId).value = pltNumber;
        });
    });
</script>
@endpush
