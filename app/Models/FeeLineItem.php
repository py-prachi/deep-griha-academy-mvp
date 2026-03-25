<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FeeLineItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'fee_payment_id',
        'description',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // ── LABEL GROUPS ──────────────────────────────────────────────────────

    // Fee line items — these payments reduce the student's fee balance
    public static function feeLabels()
    {
        return [
            'admission_fee'        => 'Admission Fee',
            'tuition_fee'          => 'Tuition Fees',
            'transport_charges'    => 'Transport Charges',
            'transfer_certificate' => 'Transfer Certificate',
            'bonafide_certificate' => 'Bonafide Certificate',
            'other_fee'            => 'Other (Fee)',
        ];
    }

    // Misc line items — challan issued but fee balance is NOT affected
    public static function miscLabels()
    {
        return [
            'uniform'    => 'Uniform',
            'notebooks'  => 'Notebooks',
            'stationery' => 'Stationery',
            'sports'     => 'Sports',
            'other_misc' => 'Other (Misc)',
        ];
    }

    // All labels combined — used for challan display, reports etc.
    public static function descriptionLabels()
    {
        return array_merge(self::feeLabels(), self::miscLabels());
    }

    // Check if a description key belongs to misc category
    public static function isMiscDescription($description)
    {
        return array_key_exists($description, self::miscLabels());
    }

    // ── RELATIONSHIPS ─────────────────────────────────────────────────────

    public function payment()
    {
        return $this->belongsTo(FeePayment::class, 'fee_payment_id');
    }
}
