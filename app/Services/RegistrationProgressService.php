<?php

namespace App\Services;

use App\Enums\StudentPhotoType;
use App\Models\Student;

class RegistrationProgressService
{
    public function hasEnoughChallengeQuestions(Student $student): bool
    {
        $count = $student->challengeQuestions()->count();
        $min = config('student-password-reset.min_challenge_questions_per_student');
        $max = config('student-password-reset.max_challenge_questions_per_student');

        return $count >= $min && $count <= $max;
    }

    public function hasRegistrationPhoto(Student $student): bool
    {
        return $student->photos()
            ->where('type', StudentPhotoType::Registration)
            ->exists();
    }

    public function isReadyToComplete(Student $student): bool
    {
        return $this->hasEnoughChallengeQuestions($student)
            && $this->hasRegistrationPhoto($student);
    }

    public function nextRouteName(Student $student): string
    {
        if ($student->isRegistered()) {
            return 'register.already-registered';
        }

        if (! $this->hasEnoughChallengeQuestions($student)) {
            return 'register.questions';
        }

        if (! $this->hasRegistrationPhoto($student)) {
            return 'register.photo';
        }

        return 'register.review';
    }
}
