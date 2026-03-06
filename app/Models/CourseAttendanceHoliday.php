<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseAttendanceHoliday extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'term',
        'holiday_date',
        'holiday_name',
        'note',
        'recorded_by',
    ];

    protected $casts = [
        'holiday_date' => 'date',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
