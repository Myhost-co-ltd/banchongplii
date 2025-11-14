<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        return response()->json([
            "dashboard" => "Superadmin Dashboard",
            "message"   => "ยินดีต้อนรับ Superadmin"
        ]);
    }
}
