<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentExit extends Model
{
    protected $fillable = [
        'admission_id',
        'exit_date',
        'reason_for_leaving',
        'liked_most',
        'liked_least',
        'suggestions',
        'rating',
        'parent_name',
        'parent_contact',
        'staff_name',
        'form_submitted_at',
    ];

    protected $casts = [
        'exit_date'         => 'date',
        'form_submitted_at' => 'date',
        'rating'            => 'integer',
    ];

    public function admission()
    {
        return $this->belongsTo(Admission::class, 'admission_id');
    }
}
