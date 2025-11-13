<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    /**
     * Mass assignable attributes for a student record.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_code',
        'title',
        'first_name',
        'last_name',
    ];
}
