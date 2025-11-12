<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        // mock ข้อมูลนักเรียน
        $students = [
            ['no'=>1,'id'=>2997,'name'=>'นายเจนวิทย์ บุตรหมัน'],
            ['no'=>2,'id'=>3006,'name'=>'นายปภาวิน สายนุ้ย'],
            ['no'=>3,'id'=>3366,'name'=>'นายณัฐศิษฏ์ จงรักษ์'],
            ['no'=>4,'id'=>4474,'name'=>'นายอนุชิต โล่เสื้อ'],
            ['no'=>5,'id'=>2706,'name'=>'น.ส.ชนากานต์ ป้องปิด'],
        ];
        return view('attendance', compact('students'));
    }
}
