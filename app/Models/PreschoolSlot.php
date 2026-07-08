<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreschoolSlot extends Model
{
    protected $fillable = [
        'plan_id', 'sort_order', 'activity_name', 'material', 'objective',
        'actual_teach', 'assessment',
    ];

    public function plan()
    {
        return $this->belongsTo(PreschoolPlan::class, 'plan_id');
    }
}
