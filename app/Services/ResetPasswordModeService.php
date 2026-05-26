<?php

namespace App\Services;

use App\Enums\ResetPasswordMode;

class ResetPasswordModeService
{
    public function isModeConfigured(): bool
    {
        return ResetPasswordMode::tryFromConfig() !== null;
    }

    public function mode(): ResetPasswordMode
    {
        $mode = ResetPasswordMode::tryFromConfig();

        if ($mode === null) {
            throw new \RuntimeException('RESET_PASSWORD_MODE is missing or invalid.');
        }

        return $mode;
    }

    public function modeOrNull(): ?ResetPasswordMode
    {
        return ResetPasswordMode::tryFromConfig();
    }
}
