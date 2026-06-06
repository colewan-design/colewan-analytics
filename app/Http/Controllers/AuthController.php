<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'password' => 'required|string',
        ]);

        $expected = config('analytics.admin_password');

        if (Hash::check($data['password'], $expected) || $data['password'] === $expected) {
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
