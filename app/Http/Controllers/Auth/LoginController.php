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
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $loginType = $request->input('login_type', 'staff');

        if ($loginType === 'resident') {
            return $this->residentLogin($request);
        }

        return $this->staffLogin($request);
    }

    protected function staffLogin(Request $request)
    {
        $validated = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $field = filter_var($validated['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::attempt([$field => $validated['login'], 'password' => $validated['password']], $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            if (! $user->is_active) {
                Auth::logout();
                throw ValidationException::withMessages(['login' => 'Your account has been deactivated.']);
            }

            return match ($user->role) {
                'admin' => redirect()->intended(route('admin.dashboard')),
                'personnel' => redirect()->intended(route('personnel.dashboard')),
                default => redirect()->intended(route('resident.dashboard')),
            };
        }

        throw ValidationException::withMessages([
            'login' => __('auth.failed'),
        ]);
    }

    protected function residentLogin(Request $request)
    {
        $validated = $request->validate([
            'tracking_number' => ['required', 'string'],
            'pin' => ['required', 'string'],
        ]);

        $throttleKey = 'resident-login:'.strtolower($validated['tracking_number']);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'tracking_number' => __('auth.throttle', ['seconds' => $seconds]),
            ])->status(429);
        }

        $user = User::where('tracking_number', strtoupper($validated['tracking_number']))
            ->where('role', 'resident')
            ->first();

        if (! $user || ! $user->pin || ! Hash::check($validated['pin'], $user->pin)) {
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

        return redirect()->intended(route('resident.dashboard'));
    }
}
