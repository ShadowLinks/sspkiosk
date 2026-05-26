<?php

namespace App\Services\Slack;

use Illuminate\Http\Request;

class SlackSignatureVerifier
{
    public function isValid(Request $request): bool
    {
        $signingSecret = config('slack.signing_secret');

        if ($signingSecret === null || $signingSecret === '') {
            return false;
        }

        $timestamp = $request->header('X-Slack-Request-Timestamp');
        $signature = $request->header('X-Slack-Signature');

        if (! is_string($timestamp) || ! is_string($signature)) {
            return false;
        }

        if (! ctype_digit($timestamp)) {
            return false;
        }

        $tolerance = config('slack.signature_tolerance_seconds');

        if (abs(time() - (int) $timestamp) > $tolerance) {
            return false;
        }

        $sigBasestring = 'v0:'.$timestamp.':'.$request->getContent();
        $computed = 'v0='.hash_hmac('sha256', $sigBasestring, $signingSecret);

        return hash_equals($computed, $signature);
    }
}
