<?php

namespace App\Models;

use App\Enums\StudentPhotoType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPhoto extends Model
{
    protected $fillable = [
        'student_id',
        'type',
        'storage_path',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'type' => StudentPhotoType::class,
            'metadata' => 'array',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
