<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    // ===============================
    // 🔹 แสดงหน้า Login
    // ===============================
    public function showLogin()
    {
        return view('auth.login'); // ต้องมีไฟล์ resources/views/auth/login.blade.php
    }

    // ===============================
    // 🔹 แสดงหน้า Register
    // ===============================
    public function showRegister()
    {
        return view('auth.register');
    }

    // ===============================
    // 🔹 สมัครสมาชิก
    // ===============================
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => bcrypt($request->password),
            'role'     => 'teacher',  // ค่า default
        ]);

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    // ===============================
    // 🔹 Login
    // ===============================
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return back()->withErrors([
                'email' => 'อีเมลหรือรหัสผ่านไม่ถูกต้อง',
            ]);
        }

        // ======================
        // 🔥 ตรวจ role แล้ว redirect
        // ======================
        $role = Auth::user()->role;

        if ($role === 'superadmin') {
            return redirect()->route('dashboard.superadmin');
        }

        if ($role === 'teacher') {
            return redirect()->route('dashboard.teacher');
        }

        if ($role === 'director') {
            return redirect()->route('dashboard.director');
        }

        // fallback ถ้า role ไม่ถูกต้อง
        return redirect()->route('dashboard');
    }

    // ===============================
    // 🔹 Logout
    // ===============================
    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }
}
