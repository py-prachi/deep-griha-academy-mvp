<?php

namespace App\Traits;

use App\Models\Holiday;
use App\Models\SpecialSchoolDay;
use Carbon\Carbon;
use Illuminate\Support\Collection;

trait WorkingDays
{
    // Every working date between $from and $to (inclusive) for the session:
    // Mon-Fri minus recorded holidays, plus any Saturday/Sunday explicitly
    // marked as a Special School Day (school opened that day for classes).
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

        $specialDates = SpecialSchoolDay::where('session_id', $sessionId)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->pluck('date')
            ->map(fn($d) => Carbon::parse($d)->toDateString())
            ->flip();

        $days = collect();
        $cursor = $from->copy();
        while ($cursor->lte($to)) {
            $dateString = $cursor->toDateString();
            $isRegularWorkingDay = $cursor->isWeekday() && !$holidayDates->has($dateString);
            $isMarkedSpecialDay  = $cursor->isWeekend() && $specialDates->has($dateString);

            if ($isRegularWorkingDay || $isMarkedSpecialDay) {
                $days->push($cursor->copy());
            }
            $cursor->addDay();
        }

        return $days;
    }

    // Can attendance be taken for this date? Any weekday not a holiday, or
    // a weekend explicitly marked as a Special School Day.
    public function isWorkingDay($date, $sessionId): bool
    {
        return $this->getWorkingDays($date, $date, $sessionId)->isNotEmpty();
    }
}
