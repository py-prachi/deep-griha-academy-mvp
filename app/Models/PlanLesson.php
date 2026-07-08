<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanLesson extends Model
{
    protected $fillable = [
        'session_id', 'teacher_id', 'class_id', 'section_id', 'subject_id',
        'module_id', 'module_required',
        'date_written', 'date_execution', 'scheduled_date', 'period_id',
        'chapter_topic', 'period_timing',
        'learning_standard', 'objective', 'material_needed', 'training_component',
        'student_responses', 'hook', 'teach', 'guided_practice', 'independent_practice',
        'closure', 'homework', 'other_notes', 'remark', 'status',
    ];

    protected $casts = [
        'date_written'    => 'date',
        'scheduled_date'  => 'date',
        'module_required' => 'boolean',
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

    public function module()
    {
        return $this->belongsTo(PlanModule::class, 'module_id');
    }
}
