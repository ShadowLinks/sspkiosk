<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentChallengeQuestion extends Model
{
    protected $fillable = [
        'student_id',
        'question_text',
        'answer_hash',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
