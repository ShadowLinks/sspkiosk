<?php

namespace App\Services;

use App\Services\Slack\SlackApiClient;

class SlackApproverService
{
    public function __construct(
        private readonly SlackApiClient $slackApi,
    ) {}

    public function isAuthorizedApprover(string $slackUserId): bool
    {
        $usergroupId = config('slack.approver_usergroup_id');

        if ($usergroupId === null || $usergroupId === '') {
            return false;
        }

        $response = $this->slackApi->listUsergroupMembers($usergroupId);

        if (! ($response['ok'] ?? false)) {
            return false;
        }

        $users = $response['users'] ?? [];

        return in_array($slackUserId, $users, true);
    }
}
