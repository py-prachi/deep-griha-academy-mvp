<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarkExamDate extends Model
{
    protected $fillable = [
        'subject_id', 'class_id', 'session_id', 'term', 'component', 'exam_date',
    ];

    protected $casts = [
        'exam_date' => 'date',
    ];
}
