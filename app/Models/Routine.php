<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Routine extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'start',
        'end',
        'weekday',
        'class_id',
        'section_id',
        'course_id',
        'session_id',
        'period_id',
    ];

    /**
     * Get the schoolClass.
     */
    public function schoolClass() {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Get the section.
     */
    public function section() {
        return $this->belongsTo(Section::class, 'section_id');
    }

    /**
     * Get the subject (stored as ClassSubject id in course_id column).
     */
    public function course() {
        return $this->belongsTo(ClassSubject::class, 'course_id');
    }

    /**
     * Get the timetable period.
     */
    public function period() {
        return $this->belongsTo(TimetablePeriod::class, 'period_id');
    }
}
