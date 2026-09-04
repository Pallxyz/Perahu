<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class TtsController extends Controller
{
    // Voice ID yang kamu pilih dari ElevenLabs Voice Library
    private const VOICE_ID = 'hpp4J3VqNfWAUOO0d1Us';

    public function speak(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'text' => 'required|string|max:2000',
        ])->validate();

        $apiKey = config('services.elevenlabs.key');

        $response = Http::withHeaders([
            'xi-api-key'   => $apiKey,
            'Content-Type' => 'application/json',
            'Accept'       => 'audio/mpeg',
        ])->post('https://api.elevenlabs.io/v1/text-to-speech/' . self::VOICE_ID, [
            'text'     => $validated['text'],
            'model_id' => 'eleven_multilingual_v2', // support banyak bahasa termasuk Indonesia
            'voice_settings' => [
                'stability'        => 0.5,
                'similarity_boost' => 0.75,
            ],
        ]);

        if ($response->failed()) {
            return response()->json([
                'error'  => 'Gagal generate suara.',
                'detail' => $response->json(),
            ], 502);
        }

        // Response-nya berupa file audio mentah (MP3), bukan JSON
        return response($response->body(), 200)
            ->header('Content-Type', 'audio/mpeg');
    }
}