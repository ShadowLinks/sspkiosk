<?php

namespace App\Http\Middleware;

use App\Services\ResetPasswordModeService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureResetPasswordModeConfigured
{
    public function __construct(
        private readonly ResetPasswordModeService $resetPasswordMode,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->resetPasswordMode->isModeConfigured()) {
            return redirect()
                ->route('kiosk.reset.unavailable')
                ->with('error', 'Password reset is not available. Please see technology staff.');
        }

        return $next($request);
    }
}
