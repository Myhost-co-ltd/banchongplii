<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        $teacherRoleId = Role::where('name', 'teacher')->value('id');

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => bcrypt($request->password),
            'role_id'  => $teacherRoleId,
        ]);

        Auth::login($user);

        return redirect()->route('dashboard.teacher');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return back()->withErrors([
                'email' => 'สิทธิ์ผู้ใช้ไม่ถูกต้อง',
            ])->withInput();
        }

        $roleName = optional(Auth::user()->role)->name;

        return match ($roleName) {
            'superadmin', 'admin' => redirect()->route('dashboard.admin'),
            'teacher'              => redirect()->route('dashboard.teacher'),
            'director'             => redirect()->route('dashboard.director'),
            default                => $this->logoutWithError(),
        };
    }

    protected function logoutWithError()
    {
        Auth::logout();

        return redirect()->route('login')->withErrors([
            'email' => 'สิทธิ์ผู้ใช้ไม่ถูกต้อง',
        ]);
    }

    public function logout()
    {
        Auth::logout();

        return redirect()->route('login');
    }
}
