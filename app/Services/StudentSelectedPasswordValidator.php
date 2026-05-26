<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Validation\Validator;

class StudentSelectedPasswordValidator
{
    /**
     * @return array<int, string>
     */
    public function rules(): array
    {
        $min = (int) config('student-password-reset.password_policy.min_length');

        $rules = ['required', 'string', "min:{$min}"];

        if (config('student-password-reset.password_policy.require_uppercase')) {
            $rules[] = 'regex:/[A-Z]/';
        }

        if (config('student-password-reset.password_policy.require_lowercase')) {
            $rules[] = 'regex:/[a-z]/';
        }

        if (config('student-password-reset.password_policy.require_number')) {
            $rules[] = 'regex:/[0-9]/';
        }

        if (config('student-password-reset.password_policy.require_symbol')) {
            $rules[] = 'regex:/[^A-Za-z0-9]/';
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'regex' => 'The password does not meet complexity requirements.',
            'min' => 'The password must be at least :min characters.',
        ];
    }

    public function validateForStudent(string $password, string $confirmation, Student $student): Validator
    {
        $validator = validator(
            [
                'password' => $password,
                'password_confirmation' => $confirmation,
            ],
            [
                'password' => $this->rules(),
                'password_confirmation' => ['required', 'same:password'],
            ],
            $this->messages(),
        );

        $validator->after(function ($validator) use ($password, $student): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if ($this->containsForbiddenPart($password, $student->email, config('student-password-reset.password_policy.prevent_email_parts'))) {
                $validator->errors()->add('password', 'The password cannot include parts of your email address.');
            }

            if ($this->containsForbiddenPart($password, $student->name, config('student-password-reset.password_policy.prevent_name_parts'))) {
                $validator->errors()->add('password', 'The password cannot include parts of your name.');
            }
        });

        return $validator;
    }

    private function containsForbiddenPart(string $password, ?string $source, bool $enabled): bool
    {
        if (! $enabled || $source === null || $source === '') {
            return false;
        }

        $passwordLower = strtolower($password);

        foreach (preg_split('/[\s@._-]+/', strtolower($source)) ?: [] as $part) {
            $part = trim($part);

            if (strlen($part) >= 3 && str_contains($passwordLower, $part)) {
                return true;
            }
        }

        return false;
    }
}
