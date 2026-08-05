<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use App\Traits\SchoolSession;
use App\Interfaces\SchoolSessionInterface;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HolidayController extends Controller
{
    use SchoolSession;

    protected $schoolSessionRepository;

    public function __construct(SchoolSessionInterface $schoolSessionRepository)
    {
        $this->schoolSessionRepository = $schoolSessionRepository;

        $this->middleware(function ($request, $next) {
            if (auth()->user()->role !== 'admin') {
                abort(403);
            }
            return $next($request);
        });
    }

    public function index()
    {
        $sessionId = $this->getSchoolCurrentSession();

        $holidays = Holiday::where('session_id', $sessionId)
            ->orderBy('date')
            ->get();

        $holidayGroups = $this->groupConsecutive($holidays);

        return view('holidays.index', compact('holidayGroups', 'sessionId'));
    }

    // Collapse holiday dates into display rows:
    //  - A run of dates that are ALL holidays (any name, no calendar gap)
    //    forms one "coverage block" — e.g. Summer Vacation with Bakrid
    //    sitting in the middle of it is still one uninterrupted block.
    //  - Within a block, each distinct name gets its own row spanning its
    //    own first-to-last date in that block (so "Summer Vacation" shows
    //    as one 25 Apr - 31 May range even though Bakrid, a different name,
    //    falls on 27 May — Bakrid still gets its own separate row too).
    private function groupConsecutive($holidays): array
    {
        // 1) Split into coverage blocks: break only on a true calendar gap.
        $blocks = [];
        $block  = [];
        $prevDate = null;

        foreach ($holidays as $holiday) {
            $date = Carbon::parse($holiday->date);

            if ($prevDate && !$prevDate->copy()->addDay()->isSameDay($date)) {
                $blocks[] = $block;
                $block = [];
            }

            $block[] = ['date' => $date, 'holiday' => $holiday];
            $prevDate = $date;
        }
        if (!empty($block)) {
            $blocks[] = $block;
        }

        // 2) Within each block, group by name — same name can span the
        // whole block even if a different name interrupts it.
        $groups = [];
        foreach ($blocks as $block) {
            $byName = [];
            $order  = [];
            foreach ($block as $entry) {
                $name = $entry['holiday']->name;
                if (!isset($byName[$name])) {
                    $byName[$name] = ['name' => $name, 'start' => $entry['date'], 'end' => $entry['date'], 'ids' => []];
                    $order[] = $name;
                }
                $byName[$name]['end'] = $entry['date'];
                $byName[$name]['ids'][] = $entry['holiday']->id;
            }
            foreach ($order as $name) {
                $groups[] = $byName[$name];
            }
        }

        // 3) Display order: by each row's earliest date.
        usort($groups, fn($a, $b) => $a['start'] <=> $b['start']);

        return $groups;
    }

    public function store(Request $request)
    {
        $sessionId = $this->getSchoolCurrentSession();

        $request->validate([
            'date'     => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:date',
            'name'     => 'required|string|max:150',
        ]);

        // No end date — single-day holiday, same as before.
        if (!$request->filled('end_date')) {
            $exists = Holiday::where('session_id', $sessionId)
                ->whereDate('date', $request->date)
                ->exists();

            if ($exists) {
                return back()->withErrors(['date' => 'A holiday is already recorded for that date.']);
            }

            Holiday::create([
                'date'       => $request->date,
                'name'       => $request->name,
                'session_id' => $sessionId,
            ]);

            return back()->with('status', 'Holiday added.');
        }

        // End date given — a holiday period (e.g. summer vacation). Expand
        // into one row per calendar day; silently skip dates already recorded
        // rather than failing the whole batch over one overlap.
        $cursor = Carbon::parse($request->date);
        $end    = Carbon::parse($request->end_date);
        $added  = 0;

        while ($cursor->lte($end)) {
            $exists = Holiday::where('session_id', $sessionId)
                ->whereDate('date', $cursor->toDateString())
                ->exists();
            if (!$exists) {
                Holiday::create([
                    'date'       => $cursor->toDateString(),
                    'name'       => $request->name,
                    'session_id' => $sessionId,
                ]);
                $added++;
            }
            $cursor->addDay();
        }

        return back()->with('status', "Holiday period added — {$added} day(s) recorded.");
    }

    public function destroy(Holiday $holiday)
    {
        $holiday->delete();

        return back()->with('status', 'Holiday removed.');
    }

    // Deletes every date in a grouped range in one action (the range shown
    // in the list is a display grouping — each date is still its own row
    // underneath, so removing the range removes all of them together).
    public function destroyRange(Request $request)
    {
        $sessionId = $this->getSchoolCurrentSession();

        $ids = array_filter(explode(',', (string) $request->input('ids')));

        Holiday::where('session_id', $sessionId)
            ->whereIn('id', $ids)
            ->delete();

        return back()->with('status', 'Holiday period removed.');
    }
}
