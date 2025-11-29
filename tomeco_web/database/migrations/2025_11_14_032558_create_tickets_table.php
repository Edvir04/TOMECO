<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Creates the 'ticket_issuance' table
        Schema::create('tickets', function (Blueprint $table) {
            $table->id(); // Primary key

            // Based on your $fillable array
            $table->string('citation_number')->nullable();
            $table->date('issued_date')->nullable(); // Cast as 'date'
            $table->time('issued_time')->nullable();
            $table->string('issued_by')->nullable();
            
            $table->string('driver_lastname'); // Set as required based on form
            $table->string('driver_firstname'); // Set as required based on form
            $table->string('driver_middlename')->nullable();
            $table->string('driver_address')->nullable();
            $table->string('dl_number')->nullable();
            $table->string('driver_contact')->nullable();
            $table->string('dl_type')->nullable(); // Prof, N/P, S/P, Others
            
            $table->string('plate_number')->nullable();
            $table->string('cr_number')->nullable();
            $table->string('vehicle_year', 4)->nullable(); // 4-char string
            $table->string('vehicle_make')->nullable();
            $table->string('vehicle_model')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->string('or_number')->nullable();
            
            $table->string('owner_name')->nullable();
            $table->string('owner_address')->nullable();
            
            // Cast as 'array' -> use json()
            $table->json('violations')->nullable(); 
            $table->string('violations_others_text')->nullable();
            
            $table->string('place')->nullable();
            $table->boolean('accident')->nullable(); // Yes/No for accident
            $table->text('incident_notes')->nullable(); // 'text' for longer strings
            $table->text('remarks')->nullable(); // 'text' for longer strings
            $table->string('admitted_or_protest')->nullable(); // Admitted or Under Protest
            
            $table->date('court_date')->nullable(); // Cast as 'date'
            $table->time('court_time')->nullable();
            
            $table->string('apprehending_officer')->nullable();
            $table->string('tomeco_did')->nullable();
            $table->longText('signature')->nullable(); // Officer e-signature
            $table->string('image')->nullable(); // Evidence photo
            $table->longText('driver_signature')->nullable(); // Driver e-signature

            $table->timestamps(); // Adds created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_issuance');
    }
};