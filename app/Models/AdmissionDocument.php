<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdmissionDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'admission_id',
        'document_type',
        'status',
        'received_date',
        'file_path',
    ];

    protected $casts = [
        'received_date' => 'date',
    ];

    // ── DOCUMENT TYPE CONSTANTS ───────────────────────────────────────────
    const TYPE_BIRTH_CERT      = 'birth_certificate';
    const TYPE_PREVIOUS_LC     = 'previous_school_lc';
    const TYPE_AADHAAR         = 'aadhaar_card';
    const TYPE_PHOTOS          = 'passport_photos';
    const TYPE_CASTE_CERT      = 'caste_certificate';
    const TYPE_RTE_DOCS        = 'rte_documents';

    // ── STATUS CONSTANTS ──────────────────────────────────────────────────
    const STATUS_RECEIVED = 'received';
    const STATUS_PENDING  = 'pending';
    const STATUS_NA       = 'na';

    // Human readable labels for display in UI
    public static function typeLabels()
    {
        return [
            self::TYPE_BIRTH_CERT  => 'Birth Certificate',
            self::TYPE_PREVIOUS_LC => 'Previous School LC',
            self::TYPE_AADHAAR     => 'Aadhaar Card',
            self::TYPE_PHOTOS      => '2 Passport Photos',
            self::TYPE_CASTE_CERT  => 'Caste Certificate',
            self::TYPE_RTE_DOCS    => 'RTE Documents',
        ];
    }

    // ── RELATIONSHIPS ─────────────────────────────────────────────────────

    public function admission()
    {
        return $this->belongsTo(Admission::class, 'admission_id');
    }
}
