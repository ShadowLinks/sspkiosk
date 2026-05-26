<?php

namespace App\Http\Controllers;

use App\Services\SlackApprovalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SlackInteractionController extends Controller
{
    public function __construct(
        private readonly SlackApprovalService $slackApproval,
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $payloadString = $request->input('payload');

        if (! is_string($payloadString)) {
            parse_str($request->getContent(), $parsed);
            $payloadString = $parsed['payload'] ?? null;
        }

        $payload = is_string($payloadString) ? json_decode($payloadString, true) : null;

        if (! is_array($payload)) {
            return response()->json(['message' => 'Invalid payload.'], 400);
        }

        $result = $this->slackApproval->handleInteraction($payload);

        if (isset($result['challenge'])) {
            return response()->json(['challenge' => $result['challenge']]);
        }

        if ($result === []) {
            return response()->json();
        }

        return response()->json($result);
    }
}
