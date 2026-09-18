<?php

namespace App\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && ! $user->is_active) {
            auth()->logout();
            return redirect()->route('admin.login')->withErrors([
                'email' => 'حساب کاربری شما غیرفعال است.',
            ]);
        }

        return $next($request);
    }
}
