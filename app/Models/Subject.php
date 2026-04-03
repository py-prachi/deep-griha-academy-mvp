<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['name', 'code', 'sort_order', 'is_active'];

    public function classSubjects()
    {
        return $this->hasMany(ClassSubject::class);
    }

    public function subjectTeachers()
    {
        return $this->hasMany(SubjectTeacher::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
