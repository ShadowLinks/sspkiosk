<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('password_reset_requests', function (Blueprint $table) {
            $table->string('kiosk_session_id')->nullable()->after('kiosk_id');
        });
    }

    public function down(): void
    {
        Schema::table('password_reset_requests', function (Blueprint $table) {
            $table->dropColumn('kiosk_session_id');
        });
    }
};
