<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeavingCertificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_user_id',
        'lc_number',
        'student_name',
        'mother_name',
        'race_and_caste',
        'nationality',
        'place_of_birth',
        'date_of_birth',
        'last_school_attended',
        'date_of_admission',
        'standard_studying',
        'in_standard_since',
        'register_no',
        'progress',
        'conduct',
        'date_of_leaving',
        'reason_for_leaving',
        'remark',
        'outstanding_balance_at_lc',
        'fee_warning_acknowledged',
        'issued_by',
        'issued_date',
    ];

    protected $casts = [
        'date_of_birth'              => 'date',
        'date_of_admission'          => 'date',
        'date_of_leaving'            => 'date',
        'issued_date'                => 'date',
        'outstanding_balance_at_lc'  => 'decimal:2',
        'fee_warning_acknowledged'   => 'boolean',
    ];

    // ── RELATIONSHIPS ─────────────────────────────────────────────────────

    public function student()
    {
        return $this->belongsTo(User::class, 'student_user_id');
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    // ── HELPER METHODS ────────────────────────────────────────────────────

    // Generate next global LC number
    public static function nextLcNumber()
    {
        $last = self::orderBy('lc_number', 'desc')->first();
        return $last ? $last->lc_number + 1 : 1;
    }

    // Format LC number for display: LC001, LC002...
    public function formattedLcNumber()
    {
        return 'LC' . str_pad($this->lc_number, 3, '0', STR_PAD_LEFT);
    }
}
