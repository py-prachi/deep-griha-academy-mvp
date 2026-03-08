<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeavingCertificate extends Model
{
    use SoftDeletes;

    protected $table = 'leaving_certificates';

    protected $fillable = [
        'admission_id',
        'lc_number',
        'issue_date',
        'issue_place',
        'pupil_name',
        'mother_name',
        'race_and_caste',
        'nationality',
        'place_of_birth',
        'date_of_birth',
        'last_school_attended',
        'date_of_admission',
        'progress',
        'conduct',
        'date_of_leaving',
        'standard_studying',
        'studying_since',
        'reason_for_leaving',
        'remarks',
        'fees_cleared',
        'fees_due',
        'issued_by',
    ];

    protected $casts = [
        'issue_date'        => 'date',
        'date_of_birth'     => 'date',
        'date_of_admission' => 'date',
        'date_of_leaving'   => 'date',
        'studying_since'    => 'date',
        'fees_cleared'      => 'boolean',
        'fees_due'          => 'decimal:2',
    ];

    public function admission()
    {
        return $this->belongsTo(Admission::class);
    }

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public static function generateLcNumber(): string
    {
        $last = static::withTrashed()->orderBy('id', 'desc')->first();

        if (!$last) {
            return 'LC001';
        }

        $numeric = (int) ltrim(substr($last->lc_number, 2), '0');
        $next    = $numeric + 1;

        return 'LC' . str_pad($next, 3, '0', STR_PAD_LEFT);
    }
}
