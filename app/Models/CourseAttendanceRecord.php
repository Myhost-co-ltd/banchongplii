<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseAttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'student_id',
        'term',
        'attendance_date',
        'status',
        'deduction_points',
        'note',
        'recorded_by',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'deduction_points' => 'decimal:2',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
