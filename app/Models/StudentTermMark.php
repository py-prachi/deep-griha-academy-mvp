<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentTermMark extends Model
{
    protected $fillable = [
        'student_id', 'subject_id', 'class_id', 'section_id', 'session_id', 'term',
        'oral_internal', 'activity_internal', 'test', 'hw',
        'oral_written', 'activity_written', 'writing',
        'internal_total', 'written_total', 'grand_total',
        'grade', 'absent_components', 'entered_by', 'entered_at', 'verified_by', 'verified_at',
    ];

    // Decode absent_components JSON automatically
    public function getAbsentComponentsAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    public function setAbsentComponentsAttribute($value)
    {
        $this->attributes['absent_components'] = $value ? json_encode($value) : null;
    }

    public function isComponentAbsent($component)
    {
        return in_array($component, $this->absent_components ?? []);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}
