<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketIssued extends Model
{
    use HasFactory;

    // Specify the correct table name
    protected $table = 'ticket_issuance'; // Use the correct table name here

    protected $fillable = [
        'drivers_name', 'address', 'drivers_permit', 'plt_number', 'cr_number',
        'required_date', 'or_number', 'make', 'model', 'type', 'year', 'owner',
        'owner_address', 'place', 'accident', 'apprehending_officer', 'tomeco_id',
        'image', 'prof', 'np', 'sp', 'violation1', 'violation2', 'violation3',
        'violation4', 'violation5', 'violation6', 'violation7', 'violation8',
        'violation9', 'violation10', 'violation11', 'violation12', 'admitted',
        'under_protest'
    ];
}
