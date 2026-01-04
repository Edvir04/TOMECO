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
            // DSS tracking fields
            $table->integer('unpaid_violation_count')->default(0)->after('paid_at')->comment('Count of unpaid violations for this violator');
            $table->string('dss_penalty_level')->nullable()->after('unpaid_violation_count')->comment('Current DSS penalty level: warning, suspension_temp, suspension_extended, permanent_ban');
            $table->timestamp('dss_penalty_applied_at')->nullable()->after('dss_penalty_level');
            $table->boolean('dss_sms_sent')->default(false)->after('dss_penalty_applied_at')->comment('Whether DSS SMS notification was sent');
            $table->decimal('dss_penalty_fine_increase', 10, 2)->default(0)->after('dss_sms_sent')->comment('Additional fine amount due to DSS penalties');
            $table->text('dss_notes')->nullable()->after('dss_penalty_fine_increase')->comment('DSS penalty notes and details');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn([
                'unpaid_violation_count',
                'dss_penalty_level',
                'dss_penalty_applied_at',
                'dss_sms_sent',
                'dss_penalty_fine_increase',
                'dss_notes'
            ]);
        });
    }
};

