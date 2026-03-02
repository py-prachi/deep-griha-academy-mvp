<?php

namespace App\Models;

use App\Models\Mark;
use App\Models\StudentParentInfo;
use App\Models\StudentAcademicInfo;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasRoles, HasFactory, Notifiable;

    protected $guard_name = 'web';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'gender',
        'nationality',
        'phone',
        'address',
        'address2',
        'city',
        'zip',
        'photo',
        'birthday',
        'religion',
        'blood_type',
        'role',
        // ── DGA fields ──────────────────────────
        'admission_id',
        'fee_category',
        'dga_admission_no',
        'general_id',
        'village',
        'distance_from_school',
        'student_status',
        'date_of_leaving',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_leaving'   => 'date',
    ];

    // ── EXISTING RELATIONSHIPS ────────────────────────────────────────────

    public function parent_info()
    {
        return $this->hasOne(StudentParentInfo::class, 'student_id', 'id');
    }

    public function academic_info()
    {
        return $this->hasOne(StudentAcademicInfo::class, 'student_id', 'id');
    }

    public function marks()
    {
        return $this->hasMany(Mark::class, 'student_id', 'id');
    }

    // ── NEW DGA RELATIONSHIPS ─────────────────────────────────────────────

    public function admission()
    {
        return $this->belongsTo(Admission::class, 'admission_id');
    }

    public function feePayments()
    {
        return $this->hasMany(FeePayment::class, 'student_user_id');
    }

    public function leavingCertificate()
    {
        return $this->hasOne(LeavingCertificate::class, 'student_user_id');
    }

    // ── HELPER METHODS ────────────────────────────────────────────────────

    // Total fee paid by this student
    public function totalFeePaid()
    {
        return $this->feePayments()->sum('amount_paid');
    }

    // Fee balance remaining
    public function feeBalance($totalFeeDue)
    {
        return $totalFeeDue - $this->totalFeePaid();
    }

    // Fee payment status
    public function feeStatus($totalFeeDue)
    {
        $paid = $this->totalFeePaid();
        if ($paid <= 0) return 'due';
        if ($paid >= $totalFeeDue) return 'paid';
        return 'partial';
    }

    // Is this user a student?
    public function isStudent()
    {
        return $this->role === 'student';
    }

    // Is this user active (not left)?
    public function isActive()
    {
        return $this->student_status === 'active';
    }

    // Full name helper
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
