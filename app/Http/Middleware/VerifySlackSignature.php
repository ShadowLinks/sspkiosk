<?php

namespace App\Http\Middleware;

use App\Services\Slack\SlackSignatureVerifier;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifySlackSignature
{
    public function __construct(
        private readonly SlackSignatureVerifier $signatureVerifier,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->signatureVerifier->isValid($request)) {
            return response()->json(['message' => 'Invalid Slack signature.'], 403);
        }

        return $next($request);
    }
}
