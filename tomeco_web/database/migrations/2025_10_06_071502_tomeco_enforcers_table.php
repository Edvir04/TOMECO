<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    Schema::create('tomeco_enforcers', function (Blueprint $table) {
        $table->id();
        $table->string('fullname');
        $table->string('username');  
        $table->string('id_number')->nullable();
        $table->string('password');
        $table->enum('gender', ['male', 'female', 'other']);
        $table->date('dob');
        $table->string('contact_number');
        $table->string('address');
        $table->string('profile_picture')->nullable();
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('tomeco_enforcers');
}

};
