<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TicketIssuance; // Import the model

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = TicketIssuance::query();

        // Apply search filter if there's a query
        if ($request->has('search')) {
            $query->where('drivers_name', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('plt_number', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('or_number', 'LIKE', '%' . $request->search . '%');
        }

        // Paginate results
        $tickets = $query->paginate(10); 

        // Pass tickets to the correct view
        return view('auth.payticket', compact('tickets'));
    }
}
