<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'grade',
        'rooms',
        'term',
        'year',
        'description',
        'teaching_hours',
        'lessons',
        'assignments',
    ];

    protected $casts = [
        'rooms' => 'array',
        'teaching_hours' => 'array',
        'lessons' => 'array',
        'assignments' => 'array',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
