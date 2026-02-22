<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminAuthController extends Controller
{
    public function showLogin(): Response|RedirectResponse
    {
        if (session('admin_authenticated')) {
            return redirect('/admin');
        }

        return Inertia::render('Admin/Login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (
            $request->username === env('ADMIN_USERNAME') &&
            $request->password === env('ADMIN_PASSWORD')
        ) {
            $request->session()->put('admin_authenticated', true);
            $request->session()->regenerate();

            return redirect()->intended('/admin');
        }

        return back()->withErrors(['username' => 'Invalid credentials.'])->onlyInput('username');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('admin_authenticated');

        return redirect('/admin/login');
    }
}
