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
        // Only run if image column exists and images column doesn't exist
        if (Schema::hasColumn('tickets', 'image') && !Schema::hasColumn('tickets', 'images')) {
            Schema::table('tickets', function (Blueprint $table) {
                // Change image column from string to json to store multiple images
                $table->json('images')->nullable()->after('signature');
            });
            
            // Migrate existing single image data to images array (PostgreSQL syntax)
            \DB::statement("UPDATE tickets SET images = json_build_array(image)::jsonb WHERE image IS NOT NULL");
            
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropColumn('image');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only run if images column exists and image column doesn't exist
        if (Schema::hasColumn('tickets', 'images') && !Schema::hasColumn('tickets', 'image')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->string('image')->nullable()->after('signature');
            });
            
            // Migrate images array back to single image (take first image) - PostgreSQL syntax
            \DB::statement("UPDATE tickets SET image = images->>0 WHERE images IS NOT NULL AND jsonb_array_length(images::jsonb) > 0");
            
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropColumn('images');
            });
        }
    }
};

