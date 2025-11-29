<?php

namespace App\Http\Controllers;

use App\Models\TicketIssued;
use Illuminate\Http\Request;

class TicketIssuedController extends Controller
{
    public function index(Request $request)
    {
        // Search query based on the 'search' parameter
        $query = TicketIssued::query();

        if ($request->has('search') && !empty($request->search)) {
            $query->where('drivers_name', 'like', '%' . $request->search . '%')
                ->orWhere('address', 'like', '%' . $request->search . '%')
                ->orWhere('plt_number', 'like', '%' . $request->search . '%');
        }

        // Paginate results
        $tickets = $query->paginate(10);  // Pagination is applied here

        // Return the view with the tickets
        return view('ticketIssued', compact('tickets'));
    }

    public function create()
    {
        return view('ticketIssued.create');  // Return the modal create form
    }

    public function store(Request $request)
    {
        $request->validate([
            'drivers_name' => 'required|string',
            'address' => 'required|string',
            'drivers_permit' => 'required|string',
            'plt_number' => 'required|string',
            'cr_number' => 'required|string',
            'required_date' => 'required|date',
            'or_number' => 'required|string',
            'make' => 'required|string',
            'model' => 'required|string',
            'type' => 'required|string',
            'year' => 'required|integer',
            'owner' => 'required|string',
            'owner_address' => 'required|string',
            'place' => 'required|string',
            'accident' => 'required|string',
            'apprehending_officer' => 'required|string',
            'tomeco_id' => 'required|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $ticket = new TicketIssued($request->all());

        if ($request->hasFile('image')) {
            $ticket->image = $request->file('image')->store('images', 'public');
        }

        $ticket->save();

        return redirect()->route('ticket-issued.index')->with('success', 'Ticket Issued successfully!');
    }

    public function edit($id)
    {
        $ticket = TicketIssued::findOrFail($id);
        return view('ticketIssued.edit', compact('ticket'));  // Return the modal edit form
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'drivers_name' => 'required|string',
            'address' => 'required|string',
            'drivers_permit' => 'required|string',
            'plt_number' => 'required|string',
            'cr_number' => 'required|string',
            'required_date' => 'required|date',
            'or_number' => 'required|string',
            'make' => 'required|string',
            'model' => 'required|string',
            'type' => 'required|string',
            'year' => 'required|integer',
            'owner' => 'required|string',
            'owner_address' => 'required|string',
            'place' => 'required|string',
            'accident' => 'required|string',
            'apprehending_officer' => 'required|string',
            'tomeco_id' => 'required|string',
            'image' => 'nullable|image|max:2048',
        ]);

        $ticket = TicketIssued::findOrFail($id);
        $ticket->update($request->all());

        if ($request->hasFile('image')) {
            $ticket->image = $request->file('image')->store('images', 'public');
        }

        return redirect()->route('ticket-issued.index')->with('success', 'Ticket updated successfully!');
    }

    public function destroy($id)
    {
        $ticket = TicketIssued::findOrFail($id);
        $ticket->delete();

        return redirect()->route('ticket-issued.index')->with('success', 'Ticket deleted successfully!');
    }
}
