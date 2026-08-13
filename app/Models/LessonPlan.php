<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonPlan extends Model
{
    protected $fillable = [
        'session_id', 'teacher_id', 'class_id', 'section_id', 'subject_id',
        'month', 'days_allocated', 'chapter_number', 'chapter_name',
        'learning_standards', 'status', 'admin_remark',
    ];

    const MONTHS = [
        'april'     => 'April',
        'june'      => 'June',
        'july'      => 'July',
        'august'    => 'August',
        'september' => 'September',
        'october'   => 'October',
        'november'  => 'November',
        'december'  => 'December',
        'january'   => 'January',
        'february'  => 'February',
        'march'     => 'March',
    ];

    const STATUS_LABELS = [
        'planned'   => 'Planned',
        'completed' => 'Completed',
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
}
