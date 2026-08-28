<?php

namespace App\Http\Controllers;

use App\Models\BatteryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BatteryLogController extends Controller
{
    /**
     * Terima data telemetri baterai yang dikirim JS setiap kali
     * ESP32 broadcast lewat WebSocket. Endpoint ini murni logging,
     * jadi tidak pernah menyentuh jalur kontrol motor/servo.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'voltage'    => ['required', 'numeric', 'min:0', 'max:20'],
            'percentage' => ['required', 'integer', 'min:0', 'max:100'],
            'status'     => ['nullable', 'string', 'max:50'],
            'rssi'       => ['nullable', 'integer'],
        ]);

        if ($validator->fails()) {
            // Data korup dari ESP32 (misal ADC error) tidak boleh bikin request 500,
            // cukup ditolak halus supaya JS tidak spam retry.
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $log = BatteryLog::create($validator->validated());

        return response()->json([
            'success' => true,
            'data'    => $log,
        ], 201);
    }

    /**
     * Ambil histori terakhir untuk mengisi Chart.js saat dashboard
     * pertama kali dibuka (sebelum data realtime WS mulai masuk).
     */
    public function history(Request $request)
    {
        $limit = (int) $request->query('limit', 50);
        $limit = max(1, min($limit, 500));

        $logs = BatteryLog::orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();

        return response()->json([
            'success' => true,
            'data'    => $logs,
        ]);
    }
}