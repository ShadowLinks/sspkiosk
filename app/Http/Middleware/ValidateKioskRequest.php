<?php

namespace App\Http\Middleware;

use App\Exceptions\KioskAuthenticationException;
use App\Services\KioskSecurityService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateKioskRequest
{
    public function __construct(
        private readonly KioskSecurityService $kioskSecurity,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $requireHeartbeat = 'false'): Response
    {
        try {
            $this->kioskSecurity->verifyRequest(
                $request,
                filter_var($requireHeartbeat, FILTER_VALIDATE_BOOL),
            );
        } catch (KioskAuthenticationException $exception) {
            return response()->json([
                'message' => 'Kiosk authentication failed.',
                'reason' => $exception->getReasonCode(),
            ], $exception->getCode());
        }

        return $next($request);
    }
}
