<?php

namespace App\Services;

use App\Enums\StudentPhotoType;
use App\Models\Student;
use App\Models\StudentPhoto;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentResetPhotoService
{
    public function storeResetRequestPhoto(Student $student, UploadedFile $file, Request $request): StudentPhoto
    {
        $disk = (string) config('student-password-reset.photo_storage_disk');
        $extension = $file->guessExtension() ?: 'jpg';
        $filename = 'reset-request-'.Str::uuid().'.'.$extension;
        $directory = 'student-photos/'.$student->id;

        $path = $file->storeAs($directory, $filename, $disk);

        return $student->photos()->create([
            'type' => StudentPhotoType::ResetRequest,
            'storage_path' => $path,
            'metadata' => [
                'captured_at' => now()->toIso8601String(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'kiosk_id' => $request->attributes->get('kiosk')?->id
                    ?? $request->session()->get(config('kiosk.registration_session_kiosk_key')),
            ],
        ]);
    }
}
