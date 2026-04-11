<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrePrimarySkillGrade extends Model
{
    protected $table = 'preprimary_skill_grades';

    protected $fillable = [
        'student_id',
        'session_id',
        'class_id',
        'section_id',
        'term',
        'skill_code',
        'grade',
        'created_by',
    ];
}
