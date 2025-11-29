<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketIssuance extends Model
{
    use HasFactory;

    protected $table = 'ticket_issuance'; // Define the table name

    protected $fillable = [
        'drivers_name', 'address', 'drivers_permit', 'plt_number', 
        'cr_number', 'required_date', 'or_number', 'make', 'model', 
        'type', 'year', 'owner', 'owner_address', 'place', 'accident', 
        'apprehending_officer', 'tomeco_id', 'prof', 'np', 'sp', 
        'violation1', 'violation2', 'violation3', 'violation4', 
        'violation5', 'violation6', 'violation7', 'violation8', 
        'violation9', 'violation10', 'violation11', 'violation12', 
        'admitted', 'under_protest', 'created_at', 'updated_at'
    ];
}
