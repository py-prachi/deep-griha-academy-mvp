<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportCardPublished extends Model
{
    protected $table = 'report_card_published';

    protected $fillable = [
        'class_id',
        'section_id',
        'session_id',
        'term',
        'published_at',
        'published_by',
    ];

    protected $dates = ['published_at'];
}
