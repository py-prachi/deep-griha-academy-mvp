<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeSettlement extends Model
{
    protected $fillable = [
        'student_user_id', 'session_id', 'outstanding_amount',
        'settlement_type', 'remark', 'settled_by',
    ];

    protected $casts = [
        'outstanding_amount' => 'decimal:2',
    ];

    public static $typeLabels = [
        'waived'          => 'Waived',
        'paid_offline'    => 'Paid Offline',
        'carried_forward' => 'Carried Forward',
        'other'           => 'Other',
    ];

    public static $typeBadges = [
        'waived'          => 'bg-success',
        'paid_offline'    => 'bg-primary',
        'carried_forward' => 'bg-warning text-dark',
        'other'           => 'bg-secondary',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_user_id');
    }

    public function settledBy()
    {
        return $this->belongsTo(User::class, 'settled_by');
    }

    public function session()
    {
        return $this->belongsTo(\App\Models\SchoolSession::class, 'session_id');
    }

    public function recoveryPayments()
    {
        return $this->hasMany(\App\Models\FeePayment::class, 'rollover_id');
    }

    public function recoveredAmount(): float
    {
        return (float) $this->recoveryPayments()->sum('amount_paid');
    }

    public function remainingAmount(): float
    {
        return max(0, (float) $this->outstanding_amount - $this->recoveredAmount());
    }
}
