<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;

class RoleController extends Controller
{
    // เปลี่ยน role ให้ user
    public function updateRole(Request $request, $id)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id'
        ]);

        User::findOrFail($id)->update([
            'role_id' => $request->role_id
        ]);

        return response()->json(['message' => 'อัปเดตบทบาทสำเร็จ']);
    }

    // ดึง role ทั้งหมด (สำหรับ dropdown)
    public function allRoles()
    {
        return Role::all();
    }
}
