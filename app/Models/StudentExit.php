<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentExit extends Model
{
    // ── EXIT TYPE CONSTANTS ─────────────────────────────────────────────
    // 'genuine'    = the student actually left the school — a Leaving
    //                Certificate is expected and counts toward attrition.
    // 'correction' = the "exit" was used to remove/fix a confirmed admission
    //                that was created in error or needs a class change —
    //                not a real departure, no LC needed, excluded from
    //                genuine-exit counts in reports.
    // null         = not yet reviewed/categorized by an admin (legacy records).
    const TYPE_GENUINE    = 'genuine';
    const TYPE_CORRECTION = 'correction';

    protected $fillable = [
        'admission_id',
        'exit_type',
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

    public function isGenuine()
    {
        return $this->exit_type === self::TYPE_GENUINE;
    }

    public function isCorrection()
    {
        return $this->exit_type === self::TYPE_CORRECTION;
    }

    public function isCategorized()
    {
        return !is_null($this->exit_type);
    }

    public function scopeGenuine($query)
    {
        return $query->where('exit_type', self::TYPE_GENUINE);
    }

    public function scopeCorrection($query)
    {
        return $query->where('exit_type', self::TYPE_CORRECTION);
    }

    public function scopeUncategorized($query)
    {
        return $query->whereNull('exit_type');
    }
}
