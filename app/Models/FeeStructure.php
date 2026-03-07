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
        'admission_fee',
        'tuition_fee',
        'transport_fee',
        'other_fee',
        'total_fee',
    ];

    const CATEGORIES = ['general', 'rte', 'coc', 'discount'];

    const CATEGORY_LABELS = [
        'general'  => 'General',
        'rte'      => 'RTE',
        'coc'      => 'COC',
        'discount' => 'Discount',
    ];

    protected $casts = [
        'admission_fee' => 'decimal:2',
        'tuition_fee'   => 'decimal:2',
        'transport_fee' => 'decimal:2',
        'other_fee'     => 'decimal:2',
        'total_fee'     => 'decimal:2',
    ];

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
                $feeStructure->admission_fee +
                $feeStructure->tuition_fee +
                $feeStructure->transport_fee +
                $feeStructure->other_fee;
        });
    }
}
