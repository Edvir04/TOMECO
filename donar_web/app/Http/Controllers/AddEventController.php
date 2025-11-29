<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;

class AddEventController extends Controller
{
    public function index()
    {
        return view('layouts.addEvent');
    }
    

    public function store(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'address' => 'required|string',
            'contact' => 'required|string',
            'date' => 'required|date',
        ]);

        // Create a new event instance
        $event = new Event();
        $event->event_name = $validatedData['name'];
        $event->description = $validatedData['description'];
        $event->address = $validatedData['address'];
        $event->phone = $validatedData['contact'];
        $event->date_of_event = $validatedData['date'];
        $event->save();

        return redirect()->back()->with('status', 'Event added successfully!');
    }

}
