<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiagnosticResult extends Model
{
    protected $fillable = [
        'session_id', 'class_id', 'section_id', 'subject_id',
        'student_id', 'teacher_id', 'assessment_type',
        'marks_obtained', 'needs', 'notes',
    ];

    const ASSESSMENT_TYPES = [
        'diagnostic'  => 'Diagnostic Test',
        'unit_test_1' => 'Unit Test 1',
        'unit_test_2' => 'Unit Test 2',
        'first_term'  => 'First Term',
        'unit_test_3' => 'Unit Test 3',
        'second_term' => 'Second Term',
        'annual'      => 'Annual',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}
