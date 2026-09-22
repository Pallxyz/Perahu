<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class SocialAuthController extends Controller
{
    public function redirect(string $provider)
    {
        if ($provider !== 'google') {
            abort(404);
        }

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider)
    {
        if ($provider !== 'google') {
            abort(404);
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (Exception $e) {
            return redirect()->route('login')
                ->with('status', 'Login dengan Google gagal, silakan coba lagi.');
        }

        $user = User::where('email', $socialUser->getEmail())->first();

        if ($user) {
            // Kalau email sudah ada, update provider info-nya
            $user->update([
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
            ]);
        } else {
            // Kalau belum ada, bikin user baru
            $user = User::create([
                'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                'email' => $socialUser->getEmail(),
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'password' => bcrypt(str()->random(24)),
                'email_verified_at' => now(),
            ]);
        }

        Auth::login($user, remember: true);

        return redirect()->intended(route('dashboard', absolute: false));
    }
}