<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditActorType;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $action = trim((string) $request->query('action', ''));
        $actorType = $request->query('actor_type');
        $targetId = trim((string) $request->query('target_id', ''));

        $logs = AuditLog::query()
            ->when($action !== '', fn ($query) => $query->where('action', 'like', '%'.$action.'%'))
            ->when($actorType !== null && $actorType !== '', fn ($query) => $query->where('actor_type', $actorType))
            ->when($targetId !== '', fn ($query) => $query->where('target_id', $targetId))
            ->latest('created_at')
            ->paginate(50)
            ->withQueryString();

        return view('admin.audit.index', [
            'logs' => $logs,
            'action' => $action,
            'actorType' => $actorType,
            'targetId' => $targetId,
            'actorTypes' => AuditActorType::cases(),
        ]);
    }
}
