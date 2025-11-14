<?php

namespace App\Http\Controllers;

class TeacherController extends Controller
{
    public function index()
    {
        return response()->json([
            "dashboard" => "Teacher Dashboard",
            "message"   => "ยินดีต้อนรับครู"
        ]);
    }
}
