<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', User::class);
        return view('admin.users.index', [
            'items' => User::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', User::class);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:10'],
            'role' => ['required', 'in:super_admin,editor'],
        ], [
            'email.unique' => 'این ایمیل قبلاً ثبت شده است.',
            'password.min' => 'رمز عبور حداقل ۱۰ نویسه باشد.',
        ]);
        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = true;
        $user = User::query()->create($data);
        Audit::log('create', $user, 'ایجاد کاربر '.$user->email);
        return back()->with('success', 'کاربر ایجاد شد.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'unique:users,email,'.$user->id],
            'role' => ['required', 'in:super_admin,editor'],
            'is_active' => ['sometimes', 'boolean'],
            'password' => ['nullable', 'string', 'min:10'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        if ($user->isSuperAdmin() && ! $data['is_active']) {
            $others = User::query()
                ->where('role', User::ROLE_SUPER_ADMIN)
                ->where('id', '!=', $user->id)
                ->where('is_active', true)
                ->exists();
            if (! $others) {
                return back()->withErrors(['is_active' => 'دست‌کم یک مدیر کل فعال باید باقی بماند.']);
            }
        }
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $user->update($data);
        Audit::log('update', $user, 'ویرایش کاربر '.$user->email);
        return back()->with('success', 'کاربر به‌روز شد.');
    }
}
