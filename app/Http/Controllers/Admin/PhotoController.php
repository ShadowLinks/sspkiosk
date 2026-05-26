<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentPhoto;
use App\Services\StudentPhotoStorageService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PhotoController extends Controller
{
    public function __construct(
        private readonly StudentPhotoStorageService $photoStorage,
    ) {}

    public function show(StudentPhoto $studentPhoto): StreamedResponse
    {
        abort_unless(
            $this->photoStorage->isSafeStoragePath($studentPhoto->storage_path),
            404,
        );

        $disk = (string) config('student-password-reset.photo_storage_disk');

        abort_unless(Storage::disk($disk)->exists($studentPhoto->storage_path), 404);

        return Storage::disk($disk)->response(
            $studentPhoto->storage_path,
            basename($studentPhoto->storage_path),
            ['Cache-Control' => 'private, no-store'],
        );
    }
}
