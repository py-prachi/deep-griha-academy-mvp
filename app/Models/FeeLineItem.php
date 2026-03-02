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

    // Human readable labels for challan display
    public static function descriptionLabels()
    {
        return [
            'admission_fee'        => 'Admission Fee',
            'tuition_fee'          => 'Tuition Fees',
            'other_fee'            => 'Other Fees',
            'transfer_certificate' => 'Transfer Certificate',
            'bonafide_certificate' => 'Bonafide Certificate',
            'transport_charges'    => 'Transport Charges',
            'stationery'           => 'Stationery',
            'uniform'              => 'Uniform',
            'sports'               => 'Sports',
            'notebooks'            => 'Notebooks',
        ];
    }

    // ── RELATIONSHIPS ─────────────────────────────────────────────────────

    public function payment()
    {
        return $this->belongsTo(FeePayment::class, 'fee_payment_id');
    }
}
