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
        'payment_category',
        'cheque_no',
        'cheque_date',
        'bank_name',
        'transaction_ref',
        'is_internal_transfer',
        'recorded_by',
        'notes',
    ];

    protected $casts = [
        'payment_date'         => 'date',
        'cheque_date'          => 'date',
        'amount_paid'          => 'decimal:2',
        'is_internal_transfer' => 'boolean',
    ];

    // ── PAYMENT MODE CONSTANTS ────────────────────────────────────────────

    const MODE_CASH   = 'cash';
    const MODE_CHEQUE = 'cheque';
    const MODE_QR     = 'qr';

    // ── PAYMENT CATEGORY CONSTANTS ────────────────────────────────────────

    const CATEGORY_FEE  = 'fee';
    const CATEGORY_MISC = 'misc';

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

    // Total fee payments only (excludes misc) — used for balance calculation
    public static function totalFeesPaidByStudent($studentUserId)
    {
        return self::where('student_user_id', $studentUserId)
                   ->where('payment_category', self::CATEGORY_FEE)
                   ->sum('amount_paid');
    }

    // Total misc payments — used for misc sales report
    public static function totalMiscPaidByStudent($studentUserId)
    {
        return self::where('student_user_id', $studentUserId)
                   ->where('payment_category', self::CATEGORY_MISC)
                   ->sum('amount_paid');
    }

    // Keep old method name working — now only counts fee payments
    // so existing callers that use this for balance calc stay correct
    public static function totalPaidByStudent($studentUserId)
    {
        return self::totalFeesPaidByStudent($studentUserId);
    }
}
