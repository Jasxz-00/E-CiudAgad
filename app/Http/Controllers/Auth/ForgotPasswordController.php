<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    protected string $firebaseWebApiKey;

    public function __construct()
    {
        $this->firebaseWebApiKey = config('services.firebase.web_api_key', '');
    }

    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    public function sendRecovery(Request $request)
    {
        $channel = $request->input('channel', 'email');

        if ($channel === 'phone') {
            return $this->sendPhoneRecovery($request);
        }

        return $this->sendEmailRecovery($request);
    }

    protected function sendEmailRecovery(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $this->ensureIsNotRateLimited('forgot-password:'.$validated['email']);

        $user = User::where('email', $validated['email'])->first();

        if ($user && $user->role !== 'resident') {
            throw ValidationException::withMessages([
                'email' => __('Password reset is only available for resident accounts.'),
            ]);
        }

        $status = Password::sendResetLink(
            $validated['email']
        );

        if ($status === Password::RESET_LINK_SENT) {
            $this->clearRateLimiter('forgot-password:'.$validated['email']);

            Cache::put('resent_forgot_password_'.$validated['email'], true, now()->addMinutes(5));

            return back()->with('status', __($status));
        }

        throw ValidationException::withMessages([
            'email' => __($status),
        ]);
    }

    protected function sendPhoneRecovery(Request $request)
    {
        $validated = $request->validate([
            'contact_number' => ['required', 'string'],
        ]);

        $contactNumber = $validated['contact_number'];

        $this->ensureIsNotRateLimited('forgot-password-phone:'.$contactNumber);

        $resident = Resident::where('contact_number', $contactNumber)->first();

        if (! $resident) {
            throw ValidationException::withMessages([
                'contact_number' => __('No account found with this phone number.'),
            ]);
        }

        $user = $resident->user;

        if (! $user || $user->role !== 'resident') {
            throw ValidationException::withMessages([
                'contact_number' => __('No account found with this phone number.'),
            ]);
        }

        $phoneOtp = random_int(100000, 999999);

        Cache::put('phone_reset_otp_'.$contactNumber, [
            'otp' => $phoneOtp,
            'user_id' => $user->id,
            'expires_at' => now()->addMinutes(10),
        ], now()->addMinutes(10));

        $this->clearRateLimiter('forgot-password-phone:'.$contactNumber);

        session()->flash('phone_reset_contact', $contactNumber);
        session()->flash('phone_otp_sent', true);

        return back()->with('status', __('auth.reset_code_sent'));
    }

    public function showResetForm(Request $request, ?string $token = null)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $validated,
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();

                $user->setRememberToken(Str::random(60));

                DB::table('password_reset_tokens')
                    ->where('email', $user->email)
                    ->delete();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', __($status));
        }

        throw ValidationException::withMessages([
            'email' => __($status),
        ]);
    }

    public function verifyPhoneOtp(Request $request)
    {
        $validated = $request->validate([
            'contact_number' => ['required', 'string'],
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $contactNumber = $validated['contact_number'];
        $otp = $validated['otp'];

        $cached = Cache::get('phone_reset_otp_'.$contactNumber);

        if (! $cached || $cached['otp'] != $otp || now()->isAfter($cached['expires_at'])) {
            Cache::forget('phone_reset_otp_'.$contactNumber);

            throw ValidationException::withMessages([
                'otp' => __('Invalid or expired OTP code. Please request a new one.'),
            ]);
        }

        $token = app('auth.password.broker')->createToken(
            User::find($cached['user_id'])
        );

        Cache::forget('phone_reset_otp_'.$contactNumber);

        session()->flash('phone_reset_token', $token);
        session()->flash('phone_reset_user_id', $cached['user_id']);

        return redirect()->route('password.phone-reset')->with('phone_otp_verified', true);
    }

    public function showPhoneResetForm(Request $request)
    {
        if (! $request->session()->get('phone_otp_verified')) {
            return redirect()->route('password.request');
        }

        return view('auth.phone-reset');
    }

    protected function ensureIsNotRateLimited(string $key): void
    {
        $rateLimiter = app(RateLimiter::class);

        if ($rateLimiter->tooManyAttempts($key, 3)) {
            $seconds = $rateLimiter->availableIn($key);

            throw ValidationException::withMessages([
                'email' => __('auth.throttle', ['seconds' => $seconds]),
            ])->status(429);
        }

        $rateLimiter->hit($key, now()->addMinutes(15));
    }

    protected function clearRateLimiter(string $key): void
    {
        app(RateLimiter::class)->clear($key);
    }
}
