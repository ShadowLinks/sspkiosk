<?php

namespace Tests\Unit;

use App\Services\StudentPhotoStorageService;
use Tests\TestCase;

class StudentPhotoStorageServiceTest extends TestCase
{
    public function test_rejects_unsafe_storage_paths(): void
    {
        $service = new StudentPhotoStorageService;

        $this->assertFalse($service->isSafeStoragePath('../etc/passwd'));
        $this->assertFalse($service->isSafeStoragePath('/student-photos/1/photo.jpg'));
        $this->assertFalse($service->isSafeStoragePath('other/photo.jpg'));
        $this->assertTrue($service->isSafeStoragePath('student-photos/12/registration.jpg'));
    }
}
