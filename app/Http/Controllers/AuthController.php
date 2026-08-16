<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectUser(Auth::user());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $login = $request->input('login');
        $password = $request->input('password');
        $remember = $request->has('remember');

        $throttleKey = Str::lower($login) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'login' => "Terlalu banyak percobaan masuk. Silakan coba lagi dalam {$seconds} detik.",
            ])->withInput();
        }

        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $authAttempt = Auth::attempt([
            $fieldType => $login,
            'password' => $password,
        ], $remember);

        if ($authAttempt) {
            RateLimiter::clear($throttleKey);

            $user = Auth::user();

            if (!$user->isActive()) {
                Auth::logout();
                return back()->withErrors([
                    'login' => 'Akun Anda dinonaktifkan. Silakan hubungi administrator.',
                ])->withInput();
            }

            $user->update([
                'last_login_at' => now(),
            ]);

            $request->session()->regenerate();

            return $this->redirectUser($user);
        }

        RateLimiter::hit($throttleKey, 60);

        return back()->withErrors([
            'login' => 'Email/Username atau password salah.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    protected function redirectUser($user)
    {
        return match ($user->role) {
            UserRole::ADMIN => redirect()->intended(route('admin.dashboard')),
            UserRole::SUPERVISOR => redirect()->intended(route('supervisor.dashboard')),
            UserRole::TEAM_ADMIN => redirect()->intended(route('team.dashboard')),
            default => redirect()->route('login'),
        };
    }
}
