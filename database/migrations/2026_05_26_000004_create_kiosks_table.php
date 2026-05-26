<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kiosks', function (Blueprint $table) {
            $table->id();
            $table->uuid('kiosk_uuid')->unique();
            $table->string('name');
            $table->string('school')->nullable();
            $table->string('location')->nullable();
            $table->string('status')->default('active');
            $table->string('allowed_ip')->nullable();
            $table->string('allowed_subnet')->nullable();
            $table->string('secret_hash')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kiosks');
    }
};
