<?php

namespace App\Http\Controllers;

use App\Models\Biker;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function showShops(): View
    {
        return view('admin.shops', [
            'shops' => User::query()
                ->where('role', User::ROLE_SHOP)
                ->with('ways')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function showUsers(): View
    {
        return view('admin.users', [
            'bikers' => Biker::query()->orderBy('name')->get(),
            'users' => User::query()
                ->where('role', User::ROLE_BIKER)
                ->with('biker')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function createUser(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('user', [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_BIKER])],
            'biker_id' => ['nullable', 'required_if:role,biker', 'exists:bikers,id'],
        ]);

        User::create($data);

        return redirect()->route('admin.users')->with('user_status', 'User created successfully.');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $field = filter_var($credentials['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (! Auth::attempt([$field => $credentials['username'], 'password' => $credentials['password']])) {
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

    public function createShop(Request $request): RedirectResponse
    {
        $shop = $request->validateWithBag('shop', [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $shop['role'] = User::ROLE_SHOP;
        User::create($shop);

        return redirect()->route('admin.shops')->with('shop_status', 'Shop account created successfully.');
    }

    public function updateShop(Request $request, User $shop): RedirectResponse
    {
        abort_unless($shop->role === User::ROLE_SHOP, 404);

        $data = $request->validateWithBag('shop', [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('users', 'username')->ignore($shop)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($shop)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $shop->update($data);

        return redirect()->route('admin.shops')->with('shop_status', 'Shop account updated successfully.');
    }
}
