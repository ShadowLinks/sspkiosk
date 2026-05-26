<?php

namespace Tests\Feature;

use App\Models\Kiosk;
use App\Models\PasswordResetRequest;
use App\Models\Student;
use App\Models\StudentPhoto;
use App\Enums\StudentPhotoType;
use App\Services\SlackApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SlackApprovalMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_approval_message_posts_to_slack(): void
    {
        Storage::fake('local');
        config([
            'slack.bot_token' => 'xoxb-test',
            'slack.reset_channel_id' => 'C_RESET',
            'student-password-reset.slack_approval_required' => true,
        ]);

        Http::fake([
            'slack.com/api/chat.postMessage' => Http::response([
                'ok' => true,
                'channel' => 'C_RESET',
                'ts' => '1111.2222',
            ]),
            'slack.com/api/files.upload' => Http::response(['ok' => true]),
        ]);

        $student = Student::factory()->registered()->create();
        $kiosk = Kiosk::factory()->create();
        $photo = StudentPhoto::query()->create([
            'student_id' => $student->id,
            'type' => StudentPhotoType::Registration,
            'storage_path' => 'student-photos/'.$student->id.'/reg.jpg',
            'metadata' => [],
        ]);
        Storage::disk('local')->put($photo->storage_path, 'fake-image');

        $resetPhoto = StudentPhoto::query()->create([
            'student_id' => $student->id,
            'type' => StudentPhotoType::ResetRequest,
            'storage_path' => 'student-photos/'.$student->id.'/reset.jpg',
            'metadata' => ['ip_address' => '10.0.0.5'],
        ]);
        Storage::disk('local')->put($resetPhoto->storage_path, 'fake-image');

        $request = PasswordResetRequest::factory()->create([
            'student_id' => $student->id,
            'kiosk_id' => $kiosk->id,
            'reset_photo_id' => $resetPhoto->id,
            'challenge_score' => 3,
            'challenge_questions_presented' => [
                ['id' => 1, 'question' => 'Q1'],
                ['id' => 2, 'question' => 'Q2'],
                ['id' => 3, 'question' => 'Q3'],
            ],
        ]);

        app(SlackApprovalService::class)->sendApprovalMessage($request);

        $request->refresh();
        $this->assertSame('C_RESET', $request->slack_channel_id);
        $this->assertSame('1111.2222', $request->slack_message_ts);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'chat.postMessage')
                && str_contains($request->body(), 'Approve Reset')
                && str_contains($request->body(), 'Passwords are never posted in Slack');
        });
    }
}
