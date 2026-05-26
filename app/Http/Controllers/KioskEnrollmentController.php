<?php

namespace App\Http\Controllers;

use App\Exceptions\KioskAuthenticationException;
use App\Http\Requests\KioskEnrollRequest;
use App\Services\AuditLogService;
use App\Services\KioskEnrollmentService;
use App\Services\KioskNetworkService;
use Illuminate\Http\JsonResponse;

class KioskEnrollmentController extends Controller
{
    public function __construct(
        private readonly KioskEnrollmentService $enrollment,
        private readonly KioskNetworkService $networks,
        private readonly AuditLogService $auditLog,
    ) {}

    public function enroll(KioskEnrollRequest $request): JsonResponse
    {
        if (! $this->networks->isRequestIpAllowed($request)) {
            return response()->json([
                'message' => 'Request IP is not allowed.',
                'reason' => 'ip_not_allowed',
            ], 403);
        }

        try {
            $result = $this->enrollment->enroll(
                $request->validated('enrollment_code'),
                (string) $request->ip(),
            );
        } catch (KioskAuthenticationException $exception) {
            $this->auditLog->logSystem('kiosk.enrollment.failed', 'kiosk', null, [
                'reason' => $exception->getReasonCode(),
                'ip_address' => $request->ip(),
            ]);

            return response()->json([
                'message' => $exception->getMessage(),
                'reason' => $exception->getReasonCode(),
            ], $exception->getCode());
        }

        $this->auditLog->logSystem('kiosk.enrollment.completed', 'kiosk', (string) $result['kiosk_id'], [
            'kiosk_uuid' => $result['kiosk_uuid'],
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'kiosk_id' => $result['kiosk_id'],
            'kiosk_uuid' => $result['kiosk_uuid'],
            'secret' => $result['secret'],
            'message' => 'Store the secret on the kiosk device. It will not be shown again.',
        ]);
    }
}
