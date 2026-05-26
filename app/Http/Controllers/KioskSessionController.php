<?php

namespace App\Http\Controllers;

use App\Models\Kiosk;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KioskSessionController extends Controller
{
    public function bind(Request $request): JsonResponse
    {
        /** @var Kiosk $kiosk */
        $kiosk = $request->attributes->get('kiosk');

        $request->session()->put(config('kiosk.registration_session_kiosk_key'), $kiosk->id);

        return response()->json([
            'status' => 'ok',
            'kiosk_id' => $kiosk->id,
            'kiosk_uuid' => $kiosk->kiosk_uuid,
        ]);
    }
}
