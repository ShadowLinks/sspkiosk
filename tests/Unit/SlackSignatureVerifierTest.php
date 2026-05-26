<?php

namespace Tests\Unit;

use App\Services\Slack\SlackSignatureVerifier;
use Illuminate\Http\Request;
use Tests\TestCase;

class SlackSignatureVerifierTest extends TestCase
{
    public function test_accepts_valid_signature(): void
    {
        config(['slack.signing_secret' => 'test_secret']);
        $body = 'payload=%7B%7D';
        $timestamp = (string) time();
        $sigBasestring = 'v0:'.$timestamp.':'.$body;
        $signature = 'v0='.hash_hmac('sha256', $sigBasestring, 'test_secret');

        $request = Request::create('/slack/interactions', 'POST', [], [], [], [], $body);
        $request->headers->set('X-Slack-Request-Timestamp', $timestamp);
        $request->headers->set('X-Slack-Signature', $signature);

        $this->assertTrue((new SlackSignatureVerifier)->isValid($request));
    }

    public function test_rejects_expired_timestamp(): void
    {
        config([
            'slack.signing_secret' => 'test_secret',
            'slack.signature_tolerance_seconds' => 60,
        ]);

        $body = '';
        $timestamp = (string) (time() - 120);
        $signature = 'v0='.hash_hmac('sha256', 'v0:'.$timestamp.':'.$body, 'test_secret');

        $request = Request::create('/slack/interactions', 'POST', content: $body);
        $request->headers->set('X-Slack-Request-Timestamp', $timestamp);
        $request->headers->set('X-Slack-Signature', $signature);

        $this->assertFalse((new SlackSignatureVerifier)->isValid($request));
    }
}
