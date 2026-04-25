<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentCounselling;
use App\Models\User;
use App\Models\Promotion;
use App\Traits\SchoolSession;
use App\Interfaces\SchoolSessionInterface;

class CounsellingController extends Controller
{
    use SchoolSession;

    protected $schoolSessionRepository;

    public function __construct(SchoolSessionInterface $schoolSessionRepository)
    {
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->middleware(function ($request, $next) {
            if (auth()->user()->role !== 'admin') abort(403);
            return $next($request);
        });
    }

    public function index()
    {
        $session_id = $this->getSchoolCurrentSession();

        $active = StudentCounselling::with(['student'])
            ->where('session_id', $session_id)
            ->whereNull('end_date')
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(fn($c) => $this->withClassInfo($c, $session_id));

        $past = StudentCounselling::with(['student'])
            ->where('session_id', $session_id)
            ->whereNotNull('end_date')
            ->orderBy('end_date', 'desc')
            ->get()
            ->map(fn($c) => $this->withClassInfo($c, $session_id));

        // Students for the add dropdown — all active students in current session
        $students = Promotion::with(['student', 'schoolClass', 'section'])
            ->where('session_id', $session_id)
            ->get()
            ->sortBy(fn($p) => optional($p->student)->first_name)
            ->values();

        return view('counselling.index', compact('active', 'past', 'students', 'session_id'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_user_id' => 'required|exists:users,id',
            'start_date'      => 'required|date',
            'reason'          => 'nullable|string|max:255',
            'remarks'         => 'nullable|string',
        ]);

        $session_id = $this->getSchoolCurrentSession();

        StudentCounselling::create([
            'student_user_id' => $request->student_user_id,
            'session_id'      => $session_id,
            'start_date'      => $request->start_date,
            'reason'          => $request->reason,
            'remarks'         => $request->remarks,
            'created_by'      => auth()->id(),
        ]);

        return back()->with('status', 'Student added to counselling.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'remarks' => 'nullable|string',
            'reason'  => 'nullable|string|max:255',
        ]);

        $record = StudentCounselling::findOrFail($id);
        $record->update($request->only('remarks', 'reason'));

        return back()->with('status', 'Remarks updated.');
    }

    public function end(Request $request, $id)
    {
        $request->validate(['end_date' => 'required|date']);
        $record = StudentCounselling::findOrFail($id);
        $record->update(['end_date' => $request->end_date]);

        return back()->with('status', 'Counselling session marked as ended.');
    }

    // ── Helper: attach class/section info to a counselling record ────────────
    private function withClassInfo(StudentCounselling $c, $session_id)
    {
        $promotion = Promotion::with(['schoolClass', 'section'])
            ->where('student_id', $c->student_user_id)
            ->where('session_id', $session_id)
            ->first();
        $c->class_name   = $promotion ? optional($promotion->schoolClass)->class_name : '—';
        $c->section_name = $promotion ? optional($promotion->section)->section_name : '—';
        return $c;
    }
}
