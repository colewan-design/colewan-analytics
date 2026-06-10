<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class AuthController extends Controller
{
    public function showLogin()
    {
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'password' => 'required|string',
        ]);

        if ($data['password'] === config('analytics.admin_password')) {
            $request->session()->put('analytics_admin', true);
            return redirect()->route('dashboard.index');
        }

        return back()->withErrors(['password' => 'Incorrect password.']);
    }

    public function logout(Request $request)
    {
        $request->session()->forget('analytics_admin');
        return redirect()->route('login');
    }
}
