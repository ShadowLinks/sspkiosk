<?php

namespace App\Jobs;

use App\Models\PasswordResetRequest;
use App\Services\SlackApprovalService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendSlackResetApprovalJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $passwordResetRequestId,
    ) {}

    public function handle(SlackApprovalService $slackApproval): void
    {
        $request = PasswordResetRequest::query()->find($this->passwordResetRequestId);

        if (! $request || ! $request->isPending()) {
            return;
        }

        try {
            $slackApproval->sendApprovalMessage($request);
        } catch (\RuntimeException $exception) {
            Log::warning('Slack approval message not sent yet.', [
                'request_id' => $this->passwordResetRequestId,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
