<?php

namespace App\Services;

use App\Enums\KioskStatus;
use App\Exceptions\KioskAuthenticationException;
use App\Models\Kiosk;
use App\Models\KioskEnrollmentCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class KioskEnrollmentService
{
    public function __construct(
        private readonly KioskCredentialService $credentials,
    ) {}

    /**
     * @param  array{name: string, school?: ?string, location?: ?string, allowed_ip?: ?string, allowed_subnet?: ?string}  $attributes
     */
    public function createKiosk(array $attributes): Kiosk
    {
        return Kiosk::query()->create([
            'kiosk_uuid' => (string) Str::uuid(),
            'name' => $attributes['name'],
            'school' => $attributes['school'] ?? null,
            'location' => $attributes['location'] ?? null,
            'status' => KioskStatus::Active,
            'allowed_ip' => $attributes['allowed_ip'] ?? null,
            'allowed_subnet' => $attributes['allowed_subnet'] ?? null,
            'secret_hash' => null,
        ]);
    }

    public function issueEnrollmentCode(Kiosk $kiosk): string
    {
        $plainCode = $this->generatePlainEnrollmentCode();

        KioskEnrollmentCode::query()->create([
            'kiosk_id' => $kiosk->id,
            'code_hash' => Hash::make($plainCode),
            'expires_at' => now()->addMinutes(config('kiosk.enrollment_code_expires_minutes')),
        ]);

        return $plainCode;
    }

    /**
     * @return array{kiosk_id: int, kiosk_uuid: string, secret: string}
     */
    public function enroll(string $enrollmentCode, string $ipAddress): array
    {
        $enrollmentCode = trim($enrollmentCode);

        if ($enrollmentCode === '') {
            throw new KioskAuthenticationException('Enrollment code is required.', 'missing_enrollment_code');
        }

        return DB::transaction(function () use ($enrollmentCode, $ipAddress): array {
            $record = $this->findValidEnrollmentCode($enrollmentCode);

            if ($record === null) {
                throw new KioskAuthenticationException('Invalid or expired enrollment code.', 'invalid_enrollment_code');
            }

            $kiosk = $record->kiosk()->lockForUpdate()->first();

            if (! $kiosk || ! $kiosk->isActive()) {
                throw new KioskAuthenticationException('Kiosk is not active.', 'kiosk_inactive');
            }

            if ($kiosk->secret_hash !== null) {
                throw new KioskAuthenticationException('Kiosk is already enrolled.', 'kiosk_already_enrolled');
            }

            $secret = $this->credentials->generateSecret();
            $kiosk->secret_hash = $this->credentials->encryptSecret($secret);
            $kiosk->last_seen_at = now();
            $kiosk->save();

            $record->forceFill(['used_at' => now()])->save();

            return [
                'kiosk_id' => $kiosk->id,
                'kiosk_uuid' => $kiosk->kiosk_uuid,
                'secret' => $secret,
            ];
        });
    }

    private function findValidEnrollmentCode(string $plainCode): ?KioskEnrollmentCode
    {
        $candidates = KioskEnrollmentCode::query()
            ->with('kiosk')
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->limit(50)
            ->get();

        foreach ($candidates as $candidate) {
            if (Hash::check($plainCode, $candidate->code_hash)) {
                return $candidate;
            }
        }

        return null;
    }

    private function generatePlainEnrollmentCode(): string
    {
        return strtoupper(collect(range(1, 3))
            ->map(fn (): string => Str::upper(Str::random(4)))
            ->implode('-'));
    }
}
