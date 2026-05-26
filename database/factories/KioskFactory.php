<?php

namespace Database\Factories;

use App\Enums\KioskStatus;
use App\Models\Kiosk;
use App\Services\KioskCredentialService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Kiosk>
 */
class KioskFactory extends Factory
{
    protected $model = Kiosk::class;

    public function definition(): array
    {
        return [
            'kiosk_uuid' => (string) Str::uuid(),
            'name' => fake()->words(3, true).' Kiosk',
            'school' => fake()->optional()->city(),
            'location' => fake()->optional()->streetAddress(),
            'status' => KioskStatus::Active,
            'allowed_ip' => null,
            'allowed_subnet' => null,
            'secret_hash' => null,
            'last_seen_at' => null,
        ];
    }

    public function enrolled(): static
    {
        return $this->state(function (): array {
            $credentials = app(KioskCredentialService::class);
            $secret = $credentials->generateSecret();

            return [
                'secret_hash' => $credentials->encryptSecret($secret),
                'last_seen_at' => now(),
            ];
        });
    }

    public function disabled(): static
    {
        return $this->state(fn () => ['status' => KioskStatus::Disabled]);
    }
}
