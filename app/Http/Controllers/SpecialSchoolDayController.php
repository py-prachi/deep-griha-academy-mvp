<?php

namespace App\Http\Controllers;

use App\Models\SpecialSchoolDay;
use App\Traits\SchoolSession;
use App\Interfaces\SchoolSessionInterface;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SpecialSchoolDayController extends Controller
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

        $specialDays = SpecialSchoolDay::where('session_id', $sessionId)
            ->orderBy('date')
            ->get();

        return view('special-school-days.index', compact('specialDays', 'sessionId'));
    }

    public function store(Request $request)
    {
        $sessionId = $this->getSchoolCurrentSession();

        $request->validate([
            'date' => 'required|date',
            'name' => 'nullable|string|max:150',
        ]);

        if (!Carbon::parse($request->date)->isWeekend()) {
            return back()->withErrors(['date' => 'That date is already a normal school day (Mon–Fri) — only weekends need to be marked here.']);
        }

        $exists = SpecialSchoolDay::where('session_id', $sessionId)
            ->whereDate('date', $request->date)
            ->exists();

        if ($exists) {
            return back()->withErrors(['date' => 'That date is already marked as a Special School Day.']);
        }

        SpecialSchoolDay::create([
            'date'       => $request->date,
            'name'       => $request->name,
            'session_id' => $sessionId,
        ]);

        return back()->with('status', 'Special School Day added.');
    }

    public function destroy(SpecialSchoolDay $specialSchoolDay)
    {
        $specialSchoolDay->delete();

        return back()->with('status', 'Special School Day removed.');
    }
}
