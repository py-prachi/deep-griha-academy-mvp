<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CounsellingRemark extends Model
{
    protected $fillable = [
        'counselling_id', 'remark', 'remark_date', 'class_name', 'section_name', 'created_by',
    ];

    protected $dates = ['remark_date'];

    public function counselling()
    {
        return $this->belongsTo(StudentCounselling::class, 'counselling_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
