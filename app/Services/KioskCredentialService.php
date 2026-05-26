<?php

namespace App\Services;

use App\Models\Kiosk;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class KioskCredentialService
{
    public function generateSecret(): string
    {
        return Str::random(64);
    }

    public function encryptSecret(string $secret): string
    {
        return Crypt::encryptString($secret);
    }

    public function decryptSecret(Kiosk $kiosk): string
    {
        if ($kiosk->secret_hash === null || $kiosk->secret_hash === '') {
            throw new \RuntimeException('Kiosk is not enrolled.');
        }

        return Crypt::decryptString($kiosk->secret_hash);
    }

    public function isEnrolled(Kiosk $kiosk): bool
    {
        return $kiosk->secret_hash !== null && $kiosk->secret_hash !== '';
    }
}
