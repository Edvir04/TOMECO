<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $table = 'tickets';
    
    protected $fillable = [
        'citation_number',
        'issued_date',
        'issued_time',
        'issued_by',
        'driver_lastname',
        'driver_firstname',
        'driver_middlename',
        'driver_address',
        'dl_number',
        'driver_contact',
        'plate_number',
        'cr_number',
        'vehicle_year',
        'vehicle_make',
        'vehicle_model',
        'owner_name',
        'owner_address',
        'violations', // This will store the array of checkboxes
        'violations_others_text', // This stores the "others" description
        'place',
        'incident_notes',
        'remarks',
        'court_date',
        'court_time',
        'apprehending_officer',
        'tomeco_did',
        'signature',
        'images',
        'vehicle_type',
        'or_number',
        'dl_type',
        'accident',
        'admitted_or_protest',
        'driver_signature',
        'status',
        'price',
        'court_action_status',
        'unpaid_violation_count',
        'dss_penalty_level',
        'dss_penalty_applied_at',
        'dss_sms_sent',
        'dss_penalty_fine_increase',
        'dss_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'violations' => 'array', // Automatically handles JSON encoding/decoding
        'images' => 'array', // Automatically handles JSON encoding/decoding for multiple images
        'issued_date' => 'date',
        'court_date' => 'date',
        'accident' => 'boolean',
        'price' => 'decimal:2', // Cast price as decimal with 2 decimal places
        'is_archived' => 'boolean',
        'paid_at' => 'datetime',
        'dss_penalty_applied_at' => 'datetime',
        'dss_sms_sent' => 'boolean',
        'dss_penalty_fine_increase' => 'decimal:2',
    ];
}