<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminTeacherController extends Controller
{
    public function index()
    {
        $teacherRoleId = Role::firstOrCreate(['name' => 'teacher'])->id;

        $teachers = User::query()
            ->when($teacherRoleId, fn ($q) => $q->where('role_id', $teacherRoleId))
            ->with('role')
            ->orderBy('name')
            ->get();

        return view('admin.add-teacher', compact('teachers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(
            [
                'first_name' => 'required|string|max:100',
                'last_name'  => 'required|string|max:100',
                'email'      => 'required|email|max:255|unique:users,email',
                'phone'      => 'nullable|string|max:30|unique:users,phone',
                'major'      => 'nullable|string|max:100',
            ],
            [
                'email.unique' => 'อีเมลนี้มีอยู่ในระบบแล้ว',
                'phone.unique' => 'เบอร์โทรนี้มีอยู่ในระบบแล้ว',
            ],
            [
                'first_name' => 'ชื่อ',
                'last_name'  => 'นามสกุล',
                'email'      => 'อีเมล',
                'phone'      => 'เบอร์โทร',
                'major'      => 'วิชาเอก',
            ]
        );

        $teacherRoleId = Role::firstOrCreate(['name' => 'teacher'])->id;

        User::create([
            'name'     => trim($data['first_name'] . ' ' . $data['last_name']),
            'email'    => $data['email'],
            'phone'    => $data['phone'] ?? null,
            'major'    => $data['major'] ?? null,
            'password' => Hash::make('12345678'),
            'role_id'  => $teacherRoleId,
            'homeroom' => $data['homeroom'] ?? null,
        ]);

        return back()->with('status', 'เพิ่มข้อมูลครูเรียบร้อยแล้ว (รหัสผ่านเริ่มต้น 12345678)');
    }

    public function update(Request $request, User $teacher)
    {
        $teacherRoleId = Role::firstOrCreate(['name' => 'teacher'])->id;

        $data = $request->validate(
            [
                'first_name' => 'required|string|max:100',
                'last_name'  => 'required|string|max:100',
                'email'      => ['required','email','max:255',"unique:users,email,{$teacher->id}"],
                'phone'      => ['nullable','string','max:30',"unique:users,phone,{$teacher->id}"],
                'major'      => 'nullable|string|max:100',
            ],
            [
                'email.unique' => 'อีเมลนี้มีอยู่ในระบบแล้ว',
                'phone.unique' => 'เบอร์โทรนี้มีอยู่ในระบบแล้ว',
            ],
            [
                'first_name' => 'ชื่อ',
                'last_name'  => 'นามสกุล',
                'email'      => 'อีเมล',
                'phone'      => 'เบอร์โทร',
                'major'      => 'วิชาเอก',
            ]
        );

        $teacher->update([
            'name'     => trim($data['first_name'] . ' ' . $data['last_name']),
            'email'    => $data['email'],
            'phone'    => $data['phone'] ?? null,
            'major'    => $data['major'] ?? null,
            'homeroom' => $data['homeroom'] ?? null,
            'role_id'  => $teacherRoleId,
        ]);

        return back()->with('status', 'อัปเดตข้อมูลครูเรียบร้อยแล้ว');
    }

    public function destroy(User $teacher)
    {
        $teacher->delete();
        return back()->with('status', 'ลบข้อมูลครูเรียบร้อยแล้ว');
    }
}
