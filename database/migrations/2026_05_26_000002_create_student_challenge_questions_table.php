<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_challenge_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->text('question_text');
            $table->string('answer_hash');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_challenge_questions');
    }
};
