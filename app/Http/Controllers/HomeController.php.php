<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function dashboard()
    {
        $role = Auth::user()->role_name;

        return match ($role) {
            'superadmin' => view('dashboard.superadmin'),
            'director'   => view('dashboard.director'),
            'teacher'    => view('dashboard.teacher'),
            default      => abort(403, 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้'),
        };
    }
}
