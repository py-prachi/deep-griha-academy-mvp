<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FeeStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'session_id',
        'academic_year',
        'fee_category',
        'tuition_fee',
        'girls_tuition_fee',
        'transport_fee',
        'other_fee',
        'total_fee',
    ];

    const CATEGORIES = ['general', 'rte', 'coc'];

    const CATEGORY_LABELS = [
        'general' => 'General',
        'rte'     => 'RTE',
        'coc'     => 'COC',
    ];

    protected $casts = [
        'tuition_fee'       => 'decimal:2',
        'girls_tuition_fee' => 'decimal:2',
        'transport_fee'     => 'decimal:2',
        'other_fee'         => 'decimal:2',
        'total_fee'         => 'decimal:2',
    ];

    // Return effective tuition fee for a given gender
    public function tuitionFeeForGender($gender)
    {
        if ($this->fee_category === 'general' && strtolower($gender) === 'female' && $this->girls_tuition_fee !== null) {
            return $this->girls_tuition_fee;
        }
        return $this->tuition_fee;
    }

    // ── RELATIONSHIPS ─────────────────────────────────────────────────────

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    // ── HELPER METHODS ────────────────────────────────────────────────────

    // Auto-calculate total before saving
    protected static function booted()
    {
        static::saving(function ($feeStructure) {
            $feeStructure->total_fee =
                ($feeStructure->tuition_fee  ?? 0) +
                ($feeStructure->transport_fee ?? 0) +
                ($feeStructure->other_fee     ?? 0);
        });
    }
}
