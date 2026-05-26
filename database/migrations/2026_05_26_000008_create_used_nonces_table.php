<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('used_nonces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kiosk_id')->constrained()->cascadeOnDelete();
            $table->string('nonce');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['kiosk_id', 'nonce']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('used_nonces');
    }
};
