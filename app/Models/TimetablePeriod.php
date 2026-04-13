<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimetablePeriod extends Model
{
    protected $fillable = [
        'label',
        'start_time',
        'end_time',
        'sort_order',
        'is_break',
        'weekday',
    ];

    protected $casts = [
        'is_break' => 'boolean',
    ];

    public function scopeOrdered($q)
    {
        return $q->orderBy('sort_order');
    }

    /**
     * Get periods for a specific weekday.
     * Falls back to default (weekday=0) if no day-specific periods exist.
     */
    public static function getForDay($weekday)
    {
        $daySpecific = static::where('weekday', $weekday)->orderBy('sort_order')->get();
        if ($daySpecific->isNotEmpty()) {
            return $daySpecific;
        }
        return static::where('weekday', 0)->orderBy('sort_order')->get();
    }
}
