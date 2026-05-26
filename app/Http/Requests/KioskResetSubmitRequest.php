<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KioskResetSubmitRequest extends FormRequest
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
            'answers' => ['required', 'array', 'min:1'],
            'answers.*' => ['required', 'string', 'max:255'],
        ];
    }
}
