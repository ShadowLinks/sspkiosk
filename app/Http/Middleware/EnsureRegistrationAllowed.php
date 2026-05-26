<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRegistrationAllowed
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('student-password-reset.registration_requires_kiosk')) {
            return $next($request);
        }

        $kioskSessionKey = config('kiosk.registration_session_kiosk_key');
        $kioskId = $request->session()->get($kioskSessionKey);

        if ($kioskId) {
            return $next($request);
        }

        if ($request->routeIs('register.kiosk-required')) {
            return $next($request);
        }

        return redirect()->route('register.kiosk-required');
    }
}
