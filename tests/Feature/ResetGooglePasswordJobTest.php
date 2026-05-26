<?php

namespace Tests\Feature;

use App\Enums\PasswordResetRequestStatus;
use App\Enums\PendingPasswordType;
use App\Jobs\ResetGooglePasswordJob;
use App\Models\PasswordResetRequest;
use App\Models\Student;
use App\Services\GoogleWorkspaceDirectoryService;
use App\Services\PendingPasswordService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ResetGooglePasswordJobTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_job_resets_password_only_when_approved_processing(): void
    {
        $student = Student::factory()->registered()->create();
        $request = PasswordResetRequest::factory()->create([
            'student_id' => $student->id,
            'status' => PasswordResetRequestStatus::ApprovedProcessing,
            'approved_at' => now(),
        ]);

        app(PendingPasswordService::class)->store($request, 'Mint-River-4321-Sky', PendingPasswordType::TemporaryGenerated);

        $directory = Mockery::mock(GoogleWorkspaceDirectoryService::class);
        $directory->shouldReceive('resetPassword')->once();

        (new ResetGooglePasswordJob($request->id))->handle(
            $directory,
            app(PendingPasswordService::class),
            app(\App\Services\AuditLogService::class),
            app(\App\Services\SlackApprovalService::class),
        );

        $request->refresh();
        $this->assertSame(PasswordResetRequestStatus::Completed, $request->status);
        $this->assertTrue($request->google_reset_success);
    }

    public function test_job_skips_when_not_approved_processing(): void
    {
        $request = PasswordResetRequest::factory()->create([
            'status' => PasswordResetRequestStatus::Pending,
        ]);

        $directory = Mockery::mock(GoogleWorkspaceDirectoryService::class);
        $directory->shouldNotReceive('resetPassword');

        (new ResetGooglePasswordJob($request->id))->handle(
            $directory,
            app(PendingPasswordService::class),
            app(\App\Services\AuditLogService::class),
            app(\App\Services\SlackApprovalService::class),
        );

        $this->assertNull($request->fresh()->google_reset_attempted_at);
    }

    public function test_job_is_idempotent(): void
    {
        $request = PasswordResetRequest::factory()->create([
            'status' => PasswordResetRequestStatus::ApprovedProcessing,
            'google_reset_attempted_at' => now(),
            'google_reset_success' => true,
        ]);

        $directory = Mockery::mock(GoogleWorkspaceDirectoryService::class);
        $directory->shouldNotReceive('resetPassword');

        (new ResetGooglePasswordJob($request->id))->handle(
            $directory,
            app(PendingPasswordService::class),
            app(\App\Services\AuditLogService::class),
            app(\App\Services\SlackApprovalService::class),
        );

        $this->assertTrue($request->fresh()->google_reset_success);
    }
}
