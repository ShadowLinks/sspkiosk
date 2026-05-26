<?php

namespace App\Http\Middleware;

use App\Services\ConfigurationValidatorService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureGoogleAuthConfigured
{
    public function __construct(
        private readonly ConfigurationValidatorService $configurationValidator,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->configurationValidator->isWorkflowConfigured('google_auth')) {
            abort(503, 'Student registration is not configured.');
        }

        if ($this->configurationValidator->requiresGoogleDirectoryLookup()
            && ! $this->configurationValidator->isWorkflowConfigured('google_directory_lookup')) {
            abort(503, 'Student org unit verification is not configured.');
        }

        return $next($request);
    }
}
