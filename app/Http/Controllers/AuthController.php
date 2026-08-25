<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            return back()->withErrors(['username' => 'The username or password is incorrect.'])->withInput(['username']);
        }

        $request->session()->regenerate();

        $role = Auth::user()->role;

        return match ($role) {
            User::ROLE_ADMIN  => redirect()->route('admin.shops'),
            User::ROLE_SHOP   => redirect()->route('shop.orders'),
            User::ROLE_BIKER  => redirect()->route('bikers.ways'),
            default           => redirect()->route('login'),
        };
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
