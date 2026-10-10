<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Gagal login dengan Google. Silakan coba lagi.');
        }

        $user = User::where('google_id', $googleUser->id)->first();

        if ($user) {
            $user->update([
                'full_name'  => $googleUser->name,
                'avatar_url' => $googleUser->avatar,
            ]);
        } else {
            $user = User::where('email', $googleUser->email)->first();

            if ($user) {
                $user->update([
                    'google_id'  => $googleUser->id,
                    'avatar_url' => $googleUser->avatar,
                ]);
            } else {
                $user = User::create([
                    'google_id'  => $googleUser->id,
                    'full_name'  => $googleUser->name,
                    'email'      => $googleUser->email,
                    'username'   => $this->generateUsername($googleUser),
                    'avatar_url' => $googleUser->avatar,
                    'password'   => null,
                    'role'       => User::ROLE_CUSTOMER,
                ]);
            }
        }

        Auth::login($user, remember: true);

        return redirect()->intended($user->home());
    }

    private function generateUsername($googleUser): string
    {
        $base = $googleUser->nickname
            ?? Str::before($googleUser->email, '@')
            ?? Str::slug($googleUser->name);

        $base = Str::slug($base);

        if (empty($base)) {
            $base = 'user';
        }

        $username = $base;
        $counter  = 1;

        while (User::where('username', $username)->exists()) {
            $username = $base . $counter;
            $counter++;
        }

        return $username;
    }
}