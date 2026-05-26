<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRegistrationPhotoRequest extends FormRequest
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
        $maxKilobytes = (int) config('student-password-reset.registration_photo_max_kilobytes', 5120);

        return [
            'photo' => [
                'required',
                'file',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:'.$maxKilobytes,
            ],
        ];
    }
}
