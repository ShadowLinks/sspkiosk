<?php

namespace Database\Factories;

use App\Enums\PasswordResetRequestStatus;
use App\Models\Kiosk;
use App\Models\PasswordResetRequest;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PasswordResetRequest>
 */
class PasswordResetRequestFactory extends Factory
{
    protected $model = PasswordResetRequest::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory()->registered(),
            'kiosk_id' => Kiosk::factory(),
            'kiosk_session_id' => null,
            'status' => PasswordResetRequestStatus::Pending,
            'challenge_questions_presented' => [
                ['id' => 1, 'question' => 'Sample question?'],
            ],
            'challenge_score' => 3,
            'reset_photo_id' => null,
            'slack_channel_id' => null,
            'slack_message_ts' => null,
            'requested_at' => now(),
            'expires_at' => now()->addMinutes(10),
        ];
    }
}
