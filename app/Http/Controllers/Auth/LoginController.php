<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showResidentLoginForm()
    {
        return view('auth.login');
    }

    public function showPersonnelLoginForm()
    {
        return view('auth.personnel-login');
    }

    public function showAdminLoginForm()
    {
        return view('auth.admin-login');
    }

    public function residentLogin(Request $request)
    {
        $validated = $request->validate([
            'tracking_number' => ['required', 'string'],
            'pin' => ['required', 'string'],
        ]);

        $throttleKey =
            'resident-login:' .
            strtolower($validated['tracking_number']) .
            '|' .
            $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'tracking_number' => __('auth.throttle', [
                    'seconds' => $seconds
                ]),
            ])->status(429);
        }

        $user = User::where(
                'tracking_number',
                strtoupper($validated['tracking_number'])
            )
            ->where('role', 'resident')
            ->first();

        if (
            ! $user ||
            ! $user->pin ||
            ! Hash::check($validated['pin'], $user->pin)
        ) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'tracking_number' => __('auth.failed'),
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'tracking_number' => 'Your account has been deactivated.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        return redirect()->intended(
            route('resident.dashboard')
        );
    }

    public function personnelLogin(Request $request)
    {
        $validated = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey =
            'personnel-login:' .
            strtolower($validated['login']) .
            '|' .
            $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'login' => __('auth.throttle', [
                    'seconds' => $seconds
                ]),
            ])->status(429);
        }

        $field = filter_var(
            $validated['login'],
            FILTER_VALIDATE_EMAIL
        ) ? 'email' : 'username';

        if (! Auth::attempt([
            $field => $validated['login'],
            'password' => $validated['password'],
        ], $request->boolean('remember'))) {

            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'login' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'login' => 'Your account has been deactivated.',
            ]);
        }

        if ($user->role !== 'personnel') {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'login' => 'This account is not authorized for the personnel portal.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        return redirect()->intended(
            route('personnel.dashboard')
        );
    }

    public function adminLogin(Request $request)
    {
        $validated = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey =
            'admin-login:' .
            strtolower($validated['login']) .
            '|' .
            $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'login' => __('auth.throttle', [
                    'seconds' => $seconds
                ]),
            ])->status(429);
        }

        $field = filter_var(
            $validated['login'],
            FILTER_VALIDATE_EMAIL
        ) ? 'email' : 'username';

        if (! Auth::attempt([
            $field => $validated['login'],
            'password' => $validated['password'],
        ], $request->boolean('remember'))) {

            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'login' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'login' => 'Your account has been deactivated.',
            ]);
        }

        if ($user->role !== 'admin') {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'login' => 'This account is not authorized for the admin portal.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        return redirect()->intended(
            route('admin.dashboard')
        );
    }
}
