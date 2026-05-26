<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PasswordResetRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\PasswordResetRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasswordResetRequestController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $query = PasswordResetRequest::query()
            ->with(['student', 'kiosk'])
            ->latest('requested_at');

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        return view('admin.requests.index', [
            'requests' => $query->paginate(25)->withQueryString(),
            'status' => $status,
            'statuses' => PasswordResetRequestStatus::cases(),
        ]);
    }

    public function show(PasswordResetRequest $passwordResetRequest): View
    {
        $passwordResetRequest->load(['student', 'kiosk', 'resetPhoto']);

        return view('admin.requests.show', [
            'resetRequest' => $passwordResetRequest,
        ]);
    }
}
