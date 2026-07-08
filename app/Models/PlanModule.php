<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanModule extends Model
{
    protected $fillable = [
        'session_id', 'teacher_id', 'class_id', 'section_id', 'subject_id',
        'date_written', 'topic', 'learning_outcome', 'assessment', 'rubric',
        'objectives', 'duration_and_flow', 'materials',
    ];

    protected $casts = [
        'date_written' => 'date',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function lessons()
    {
        return $this->hasMany(PlanLesson::class, 'module_id');
    }
}
