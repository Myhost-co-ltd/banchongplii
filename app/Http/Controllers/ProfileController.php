<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'current_password'     => 'required_with:password|nullable|string',
            'password'             => 'nullable|string|min:6|confirmed',
        ]);

        // Update name
        $user->name = $validated['name'];

        // Update password if provided
        if (! empty($validated['password'])) {
            if (empty($validated['current_password']) || ! Hash::check($validated['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'รหัสผ่านปัจจุบันไม่ถูกต้อง'])->withInput();
            }
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return back()->with('status', 'อัปเดตโปรไฟล์เรียบร้อยแล้ว');
    }
}
