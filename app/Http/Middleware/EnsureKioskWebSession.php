<?php

namespace App\Http\Middleware;

use App\Models\Kiosk;
use App\Services\KioskNetworkService;
use App\Services\KioskSecurityService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureKioskWebSession
{
    public function __construct(
        private readonly KioskSecurityService $kioskSecurity,
        private readonly KioskNetworkService $networks,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $kioskId = $request->session()->get(config('kiosk.registration_session_kiosk_key'));

        if (! $kioskId) {
            return redirect()
                ->route('kiosk.reset.unavailable')
                ->with('error', 'This kiosk is not set up. Please ask technology staff for help.');
        }

        $kiosk = Kiosk::query()->find($kioskId);

        if (! $kiosk || ! $kiosk->isActive()) {
            return redirect()
                ->route('kiosk.reset.unavailable')
                ->with('error', 'This kiosk is not available.');
        }

        if (! $this->networks->isRequestIpAllowed($request, $kiosk)) {
            abort(403, 'Request IP is not allowed.');
        }

        if (config('kiosk.require_active_heartbeat') && ! $this->kioskSecurity->hasFreshHeartbeat($kiosk)) {
            return redirect()
                ->route('kiosk.reset.unavailable')
                ->with('error', 'This kiosk needs to check in with the server. Please wait a moment and try again.');
        }

        $request->attributes->set('kiosk', $kiosk);

        return $next($request);
    }
}
