<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'start', 'end', 'session_id',
        'activity_type', 'description', 'purpose', 'location', 'duration',
        'participants', 'participant_count', 'skills_values', 'photo_url', 'outcome',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
