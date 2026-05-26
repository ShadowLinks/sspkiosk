<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('password_reset_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kiosk_id')->constrained()->restrictOnDelete();
            $table->string('status')->default('pending');
            $table->json('challenge_questions_presented')->nullable();
            $table->unsignedTinyInteger('challenge_score')->nullable();
            $table->foreignId('reset_photo_id')->nullable()->constrained('student_photos')->nullOnDelete();
            $table->string('slack_channel_id')->nullable();
            $table->string('slack_message_ts')->nullable();
            $table->dateTime('requested_at');
            $table->dateTime('expires_at');
            $table->string('approved_by_slack_user_id')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('denied_by_slack_user_id')->nullable();
            $table->timestamp('denied_at')->nullable();
            $table->text('denial_reason')->nullable();
            $table->timestamp('google_reset_attempted_at')->nullable();
            $table->boolean('google_reset_success')->nullable();
            $table->text('google_error_message')->nullable();
            $table->timestamps();

            $table->index(['status', 'requested_at']);
            $table->index(['student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_requests');
    }
};
