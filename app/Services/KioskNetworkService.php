<?php

namespace App\Services;

use App\Models\Kiosk;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;

class KioskNetworkService
{
    public function isRequestIpAllowed(Request $request, ?Kiosk $kiosk = null): bool
    {
        $ip = $request->ip();

        if ($ip === null) {
            return false;
        }

        foreach (config('kiosk.allowed_networks', []) as $network) {
            if ($this->ipMatchesNetwork($ip, $network)) {
                return true;
            }
        }

        if ($kiosk === null) {
            return config('kiosk.allowed_networks') === [];
        }

        if ($kiosk->allowed_ip && $ip === $kiosk->allowed_ip) {
            return true;
        }

        if ($kiosk->allowed_subnet && $this->ipMatchesNetwork($ip, $kiosk->allowed_subnet)) {
            return true;
        }

        return $kiosk->allowed_ip === null
            && $kiosk->allowed_subnet === null
            && config('kiosk.allowed_networks') === [];
    }

    public function ipMatchesNetwork(string $ip, string $network): bool
    {
        $network = trim($network);

        if ($network === '') {
            return false;
        }

        if (! str_contains($network, '/')) {
            return $ip === $network;
        }

        return IpUtils::checkIp($ip, $network);
    }
}
