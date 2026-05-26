<?php

namespace App\Services;

use App\Enums\StudentPhotoType;
use App\Models\Student;
use App\Models\StudentPhoto;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentRegistrationPhotoService
{
    public function storeRegistrationPhoto(Student $student, UploadedFile $file, Request $request): StudentPhoto
    {
        $disk = (string) config('student-password-reset.photo_storage_disk');
        $extension = $file->guessExtension() ?: 'jpg';
        $filename = 'registration-'.Str::uuid().'.'.$extension;
        $directory = 'student-photos/'.$student->id;

        $path = $file->storeAs($directory, $filename, $disk);

        $student->photos()
            ->where('type', StudentPhotoType::Registration)
            ->get()
            ->each(function (StudentPhoto $existing) use ($disk): void {
                Storage::disk($disk)->delete($existing->storage_path);
                $existing->delete();
            });

        return $student->photos()->create([
            'type' => StudentPhotoType::Registration,
            'storage_path' => $path,
            'metadata' => $this->buildMetadata($request),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildMetadata(Request $request): array
    {
        return [
            'captured_at' => now()->toIso8601String(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'kiosk_id' => $request->session()->get(config('kiosk.registration_session_kiosk_key')),
        ];
    }
}
