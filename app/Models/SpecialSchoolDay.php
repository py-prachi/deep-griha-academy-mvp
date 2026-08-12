<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SpecialSchoolDay extends Model
{
    use HasFactory;

    protected $fillable = ['date', 'name', 'session_id'];

    protected $casts = [
        'date' => 'date',
    ];

    public function session()
    {
        return $this->belongsTo(SchoolSession::class, 'session_id');
    }
}
