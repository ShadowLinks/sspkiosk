<?php

namespace App\Services\Slack;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class SlackApiClient
{
    private function client(): PendingRequest
    {
        return Http::baseUrl('https://slack.com/api/')
            ->withToken((string) config('slack.bot_token'))
            ->acceptJson()
            ->asJson();
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function postMessage(array $payload): array
    {
        $response = $this->client()->post('chat.postMessage', $payload);

        return $response->json() ?? [];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function updateMessage(array $payload): array
    {
        $response = $this->client()->post('chat.update', $payload);

        return $response->json() ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    public function listUsergroupMembers(string $usergroupId): array
    {
        $response = $this->client()->get('usergroups.users.list', [
            'usergroup' => $usergroupId,
        ]);

        return $response->json() ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    public function uploadFile(string $channelId, string $filename, string $contents, string $title): array
    {
        $response = Http::withToken((string) config('slack.bot_token'))
            ->attach('file', $contents, $filename)
            ->post('https://slack.com/api/files.upload', [
                'channels' => $channelId,
                'title' => $title,
                'filename' => $filename,
            ]);

        return $response->json() ?? [];
    }
}
