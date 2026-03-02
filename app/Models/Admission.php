<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Admission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'status',
        'cancel_reason',
        'session_id',
        'class_id',
        'section_id',
        'academic_year',
        'dga_admission_no',
        'general_id',
        'student_user_id',
        'fee_category',
        'discounted_amount',
        'student_name',
        'date_of_birth',
        'gender',
        'caste',
        'religion',
        'nationality',
        'place_of_birth',
        'language_spoken_at_home',
        'photo_path',
        'father_name',
        'father_occupation',
        'mother_name',
        'mother_occupation',
        'full_address',
        'village',
        'distance_from_school',
        'contact_residence',
        'contact_mobile',
        'contact_emergency',
        'guardian_name',
        'guardian_occupation',
        'guardian_address',
        'sibling_name_age',
        'transport_required',
        'allergies_medical',
        'doctor_name_phone',
        'previous_school',
        'inquiry_date',
        'confirmed_date',
    ];

    protected $casts = [
        'date_of_birth'     => 'date',
        'inquiry_date'      => 'date',
        'confirmed_date'    => 'date',
        'transport_required'=> 'boolean',
        'discounted_amount' => 'decimal:2',
    ];

    // ── STATUS CONSTANTS ──────────────────────────────────────────────────
    const STATUS_INQUIRY   = 'inquiry';
    const STATUS_PENDING   = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';

    // ── RELATIONSHIPS ─────────────────────────────────────────────────────

    public function session()
    {
        return $this->belongsTo(SchoolSession::class, 'session_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_user_id');
    }

    public function documents()
    {
        return $this->hasMany(AdmissionDocument::class, 'admission_id');
    }

    // ── HELPER METHODS ────────────────────────────────────────────────────

    // Check if all required documents are received
    public function hasIncompleteDocuments()
    {
        return $this->documents()
                    ->where('status', 'pending')
                    ->whereNotIn('document_type', ['previous_school_lc', 'caste_certificate', 'rte_documents'])
                    ->exists();
    }

    // Generate next DGA admission number for pre-primary
    // Format: DGA/26-27/001
    public static function generateDgaAdmissionNo($academicYear)
    {
        $lastAdmission = self::withTrashed()
            ->where('academic_year', $academicYear)
            ->whereNotNull('dga_admission_no')
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastAdmission) {
            // Extract number from format DGA/26-27/001
            $parts = explode('/', $lastAdmission->dga_admission_no);
            $nextNumber = ((int) end($parts)) + 1;
        }

        // Format year: 2026-2027 → 26-27
        $shortYear = substr($academicYear, 2, 2) . '-' . substr($academicYear, 7, 2);

        return 'DGA/' . $shortYear . '/' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    // ── SCOPES ────────────────────────────────────────────────────────────

    public function scopeInquiry($query)
    {
        return $query->where('status', self::STATUS_INQUIRY);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopeCancelled($query)
    {
        return $query->withTrashed()->where('status', self::STATUS_CANCELLED);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_INQUIRY, self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }
}
