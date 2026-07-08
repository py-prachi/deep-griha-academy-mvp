<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreschoolPlan extends Model
{
    protected $fillable = [
        'session_id', 'teacher_id', 'class_id', 'section_id', 'plan_date',
    ];

    protected $casts = [
        'plan_date' => 'date',
    ];

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

    public function slots()
    {
        return $this->hasMany(PreschoolSlot::class, 'plan_id')->orderBy('sort_order');
    }
}
