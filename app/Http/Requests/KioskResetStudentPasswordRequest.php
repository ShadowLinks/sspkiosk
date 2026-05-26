<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KioskResetStudentPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'password' => ['required', 'string'],
            'password_confirmation' => ['required', 'string'],
        ];
    }
}
