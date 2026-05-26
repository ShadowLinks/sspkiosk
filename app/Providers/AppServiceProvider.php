<?php

namespace App\Providers;

use App\Services\AuditLogService;
use App\Services\ConfigurationValidatorService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AuditLogService::class);
        $this->app->singleton(ConfigurationValidatorService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningUnitTests()) {
            return;
        }

        $missing = $this->app->make(ConfigurationValidatorService::class)->allMissing();

        if ($missing !== []) {
            Log::warning('SSP Kiosk: required configuration is incomplete.', [
                'missing' => $missing,
            ]);
        }
    }
}
