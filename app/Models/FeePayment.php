<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FeePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_user_id',
        'challan_no',
        'payment_date',
        'amount_paid',
        'payment_mode',
        'cheque_no',
        'cheque_date',
        'bank_name',
        'transaction_ref',
        'is_internal_transfer',
        'recorded_by',
        'notes',
    ];

    protected $casts = [
        'payment_date'        => 'date',
        'cheque_date'         => 'date',
        'amount_paid'         => 'decimal:2',
        'is_internal_transfer'=> 'boolean',
    ];

    // ── PAYMENT MODE CONSTANTS ────────────────────────────────────────────
    const MODE_CASH   = 'cash';
    const MODE_CHEQUE = 'cheque';
    const MODE_QR     = 'qr';

    // ── RELATIONSHIPS ─────────────────────────────────────────────────────

    public function student()
    {
        return $this->belongsTo(User::class, 'student_user_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function lineItems()
    {
        return $this->hasMany(FeeLineItem::class, 'fee_payment_id');
    }

    // ── HELPER METHODS ────────────────────────────────────────────────────

    // Generate next global challan number
    public static function nextChallanNo()
    {
        $last = self::orderBy('challan_no', 'desc')->first();
        return $last ? $last->challan_no + 1 : 1;
    }

    // Get total paid by a student
    public static function totalPaidByStudent($studentUserId)
    {
        return self::where('student_user_id', $studentUserId)
                   ->sum('amount_paid');
    }
}
