<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BloodRequest;

class BloodRequestController extends Controller
{
    public function index()
    {
        $bloodRequests = BloodRequest::all();
        return view('layouts.bloodRequest', compact('bloodRequests'));
    }
}
