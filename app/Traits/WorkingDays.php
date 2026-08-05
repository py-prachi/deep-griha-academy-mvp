<?php

namespace App\Traits;

use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Support\Collection;

trait WorkingDays
{
    // Every Mon-Fri date between $from and $to (inclusive) that isn't a
    // recorded holiday for the session — the school's actual working days.
    public function getWorkingDays($from, $to, $sessionId): Collection
    {
        $from = Carbon::parse($from)->startOfDay();
        $to   = Carbon::parse($to)->startOfDay();

        if ($from->gt($to)) {
            return collect();
        }

        $holidayDates = Holiday::where('session_id', $sessionId)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->pluck('date')
            ->map(fn($d) => Carbon::parse($d)->toDateString())
            ->flip();

        $days = collect();
        $cursor = $from->copy();
        while ($cursor->lte($to)) {
            if ($cursor->isWeekday() && !$holidayDates->has($cursor->toDateString())) {
                $days->push($cursor->copy());
            }
            $cursor->addDay();
        }

        return $days;
    }
}
