<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentCounselling extends Model
{
    protected $fillable = [
        'student_user_id', 'session_id', 'start_date', 'end_date',
        'reason', 'remarks', 'created_by',
    ];

    protected $dates = ['start_date', 'end_date'];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_user_id');
    }

    public function session()
    {
        return $this->belongsTo(SchoolSession::class, 'session_id');
    }

    public function isActive()
    {
        return is_null($this->end_date);
    }
}
