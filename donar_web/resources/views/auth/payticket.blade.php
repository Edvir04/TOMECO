@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Issued Tickets</h1>

    <!-- Search Form -->
    <form method="GET" action="{{ route('payticket') }}" class="mb-3">
        <input type="text" name="search" class="form-control d-inline w-50" placeholder="Search by Driver's Name, Plate No, or OR No" value="{{ request('search') }}">
        <button type="submit" class="btn btn-primary">Search</button>
    </form>

    <!-- Tickets Table -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Driver's Name</th>
                <th>Address</th>
                <th>Permit</th>
                <th>Plate Number</th>
                <th>OR Number</th>
                <th>Violation</th>
                <th>Created At</th>
                <th>Action</th> <!-- Added Action Column for Pay Now Button -->
            </tr>
        </thead>
        <tbody>
            @forelse ($tickets as $ticket)
            <tr>
                <td>{{ $ticket->id }}</td>
                <td>{{ $ticket->drivers_name }}</td>
                <td>{{ $ticket->address }}</td>
                <td>{{ $ticket->drivers_permit }}</td>
                <td>{{ $ticket->plt_number }}</td>
                <td>{{ $ticket->or_number }}</td>
                <td>
                    @php
                        $violations = [
                            $ticket->violation1,
                            $ticket->violation2,
                            $ticket->violation3,
                            $ticket->violation4,
                            $ticket->violation5,
                            $ticket->violation6,
                            $ticket->violation7,
                            $ticket->violation8,
                            $ticket->violation9,
                            $ticket->violation10,
                            $ticket->violation11,
                            $ticket->violation12
                        ];
                
                        // Filter out "true", "false", and empty values
                        $filteredViolations = array_filter($violations, function($violation) {
                            return $violation !== "true" && $violation !== "false" && !empty($violation);
                        });
                
                        // Join the filtered violations with a slash (/)
                        $displayViolations = implode('/', $filteredViolations);
                    @endphp
                
                    {{ $displayViolations }}
                </td>
                
                <td>{{ $ticket->created_at }}</td>
                <td>
                    <!-- Pay Now Button -->
                    <a href="" class="btn btn-success btn-sm">Pay Now!</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center">No records found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pagination Links -->
    <div class="d-flex justify-content-center">
        {{ $tickets->links('pagination::bootstrap-4') }} <!-- Add bootstrap pagination view -->
    </div>
</div>
@endsection
