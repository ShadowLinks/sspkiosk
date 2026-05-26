<?php

namespace App\Services;

use App\Enums\AuditActorType;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as RequestFacade;

class AuditLogService
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function log(
        string $action,
        AuditActorType $actorType,
        ?string $actorId = null,
        ?string $targetType = null,
        ?string $targetId = null,
        array $metadata = [],
        ?Request $request = null,
    ): ?AuditLog {
        if (! config('audit.enabled', true)) {
            return null;
        }

        $request ??= RequestFacade::instance();

        return AuditLog::query()->create([
            'actor_type' => $actorType,
            'actor_id' => $actorId,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'metadata' => $metadata === [] ? null : $metadata,
            'created_at' => now(),
        ]);
    }

    public function logSystem(
        string $action,
        ?string $targetType = null,
        ?string $targetId = null,
        array $metadata = [],
    ): ?AuditLog {
        return $this->log($action, AuditActorType::System, null, $targetType, $targetId, $metadata);
    }

    public function logStudent(
        string $action,
        int|string $studentId,
        array $metadata = [],
        ?Request $request = null,
    ): ?AuditLog {
        return $this->log(
            $action,
            AuditActorType::Student,
            (string) $studentId,
            'student',
            (string) $studentId,
            $metadata,
            $request,
        );
    }

    public function logKiosk(
        string $action,
        int|string $kioskId,
        array $metadata = [],
        ?Request $request = null,
    ): ?AuditLog {
        return $this->log(
            $action,
            AuditActorType::Kiosk,
            (string) $kioskId,
            'kiosk',
            (string) $kioskId,
            $metadata,
            $request,
        );
    }

    public function logTech(
        string $action,
        string $slackUserId,
        ?string $targetType = null,
        ?string $targetId = null,
        array $metadata = [],
    ): ?AuditLog {
        return $this->log(
            $action,
            AuditActorType::Tech,
            $slackUserId,
            $targetType,
            $targetId,
            $metadata,
        );
    }

    public function logAdmin(
        string $action,
        int|string $adminId,
        ?string $targetType = null,
        ?string $targetId = null,
        array $metadata = [],
        ?Request $request = null,
    ): ?AuditLog {
        return $this->log(
            $action,
            AuditActorType::Admin,
            (string) $adminId,
            $targetType,
            $targetId,
            $metadata,
            $request,
        );
    }
}
