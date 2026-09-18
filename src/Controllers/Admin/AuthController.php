<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function show(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'ایمیل را وارد کنید.',
            'email.email' => 'ایمیل معتبر نیست.',
            'password.required' => 'رمز عبور را وارد کنید.',
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            Log::warning('auth.failed', ['email' => $credentials['email']]);

            return back()->withErrors([
                'email' => 'ایمیل یا رمز عبور نادرست است.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();
        $user = $request->user();
        if (! $user->is_active) {
            Auth::logout();
            return back()->withErrors(['email' => 'حساب کاربری شما غیرفعال است.']);
        }

        $user->forceFill(['last_login_at' => now()])->save();
        Audit::log('login', $user, 'ورود به پنل مدیریت');

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Audit::log('logout', $request->user(), 'خروج از پنل');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
