<?php

namespace App\Models;

use App\Enums\StudentPhotoType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    /** @use HasFactory<\Database\Factories\StudentFactory> */
    use HasFactory;

    protected $fillable = [
        'google_sub',
        'email',
        'name',
        'school',
        'grade',
        'org_unit_path',
        'registered_at',
        'reset_enabled',
    ];

    protected function casts(): array
    {
        return [
            'registered_at' => 'datetime',
            'reset_enabled' => 'boolean',
        ];
    }

    public function challengeQuestions(): HasMany
    {
        return $this->hasMany(StudentChallengeQuestion::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(StudentPhoto::class);
    }

    public function passwordResetRequests(): HasMany
    {
        return $this->hasMany(PasswordResetRequest::class);
    }

    public function isRegistered(): bool
    {
        return $this->registered_at !== null;
    }

    public function hasRegistrationPhoto(): bool
    {
        return $this->photos()
            ->where('type', StudentPhotoType::Registration)
            ->exists();
    }
}
