<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentObservation extends Model
{
    protected $table = 'student_observations';

    protected $fillable = [
        'student_id',
        'class_id',
        'section_id',
        'session_id',
        'term',
        'remarks',
        'created_by',
    ];
}
