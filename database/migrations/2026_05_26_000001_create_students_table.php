<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('google_sub')->unique();
            $table->string('email')->unique();
            $table->string('name');
            $table->string('school')->nullable();
            $table->string('grade')->nullable();
            $table->string('org_unit_path')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->boolean('reset_enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
