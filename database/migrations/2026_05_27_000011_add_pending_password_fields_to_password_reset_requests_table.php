<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('password_reset_requests', function (Blueprint $table) {
            $table->string('reset_mode')->nullable()->after('status');
            $table->text('encrypted_pending_password')->nullable()->after('reset_mode');
            $table->timestamp('pending_password_created_at')->nullable()->after('encrypted_pending_password');
            $table->timestamp('pending_password_displayed_at')->nullable()->after('pending_password_created_at');
            $table->timestamp('pending_password_deleted_at')->nullable()->after('pending_password_displayed_at');
            $table->timestamp('pending_password_expires_at')->nullable()->after('pending_password_deleted_at');
            $table->string('pending_password_type')->nullable()->after('pending_password_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('password_reset_requests', function (Blueprint $table) {
            $table->dropColumn([
                'reset_mode',
                'encrypted_pending_password',
                'pending_password_created_at',
                'pending_password_displayed_at',
                'pending_password_deleted_at',
                'pending_password_expires_at',
                'pending_password_type',
            ]);
        });
    }
};
