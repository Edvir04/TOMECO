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
        Schema::table('tickets', function (Blueprint $table) {
            // Change image column from string to json to store multiple images
            $table->json('images')->nullable()->after('signature');
        });
        
        // Migrate existing single image data to images array
        \DB::statement("UPDATE tickets SET images = JSON_ARRAY(image) WHERE image IS NOT NULL");
        
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('image')->nullable()->after('signature');
        });
        
        // Migrate images array back to single image (take first image)
        \DB::statement("UPDATE tickets SET image = JSON_UNQUOTE(JSON_EXTRACT(images, '$[0]')) WHERE images IS NOT NULL AND JSON_LENGTH(images) > 0");
        
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('images');
        });
    }
};

