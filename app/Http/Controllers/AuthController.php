<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private const LOGIN_ATTEMPTS_PER_EMAIL_IP = 5;

    private const LOGIN_ATTEMPTS_PER_IP = 30;

    private const LOGIN_RATE_LIMIT_DECAY_SECONDS = 60;

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'password_confirmation' => ['required', 'string', 'same:password'],
        ], [
            'password_confirmation.same' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $user = new User([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);
        $user->role = 'user';
        $user->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/')->with('success', 'Akun berhasil dibuat!');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $normalizedEmail = Str::transliterate(Str::lower($credentials['email']));
        $ipAddress = $request->ip() ?? 'unknown';
        $emailIpKey = 'login:'.hash('sha256', $normalizedEmail.'|'.$ipAddress);
        $ipKey = 'login-ip:'.$ipAddress;

        if (RateLimiter::tooManyAttempts($emailIpKey, self::LOGIN_ATTEMPTS_PER_EMAIL_IP)
            || RateLimiter::tooManyAttempts($ipKey, self::LOGIN_ATTEMPTS_PER_IP)) {
            throw ValidationException::withMessages([
                'email' => 'Terlalu banyak percobaan masuk. Silakan coba kembali beberapa saat lagi.',
            ]);
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($emailIpKey, self::LOGIN_RATE_LIMIT_DECAY_SECONDS);
            RateLimiter::hit($ipKey, self::LOGIN_RATE_LIMIT_DECAY_SECONDS);

            throw ValidationException::withMessages([
                'email' => 'Email atau password tidak sesuai.',
            ]);
        }

        RateLimiter::clear($emailIpKey);
        $request->session()->regenerate();

        if ($request->user()->role === 'admin') {
            return redirect()->route('admin.home');
        }

        return redirect()->intended('/');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Kamu berhasil logout.');
    }
}
