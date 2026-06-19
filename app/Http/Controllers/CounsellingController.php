<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentCounselling;
use App\Models\CounsellingRemark;
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

        // No session filter — counselling records persist across academic years
        $active = StudentCounselling::with(['student.admission', 'remarkLogs'])
            ->whereNull('end_date')
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(fn($c) => $this->withClassInfo($c, $session_id));

        $past = StudentCounselling::with(['student.admission', 'remarkLogs'])
            ->whereNotNull('end_date')
            ->orderBy('end_date', 'desc')
            ->get()
            ->map(fn($c) => $this->withClassInfo($c, $session_id));

        // Students for the add dropdown — all active students in current session, searchable by name
        $students = Promotion::with(['student.admission', 'schoolClass', 'section'])
            ->where('session_id', $session_id)
            ->get()
            ->filter(fn($p) => $p->student)
            ->sortBy(fn($p) => $p->student->first_name . ' ' . $p->student->last_name)
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
            'reason' => 'nullable|string|max:255',
        ]);

        $record = StudentCounselling::findOrFail($id);
        $record->update($request->only('reason'));

        return back()->with('status', 'Reason updated.');
    }

    public function addRemark(Request $request, $id)
    {
        $request->validate([
            'remark'      => 'required|string',
            'remark_date' => 'required|date',
        ]);

        $counselling  = StudentCounselling::findOrFail($id);
        $session_id   = $this->getSchoolCurrentSession();
        $promotion    = Promotion::with(['schoolClass', 'section'])
            ->where('student_id', $counselling->student_user_id)
            ->where('session_id', $session_id)
            ->first();

        CounsellingRemark::create([
            'counselling_id' => $id,
            'remark'         => $request->remark,
            'remark_date'    => $request->remark_date,
            'class_name'     => $promotion ? optional($promotion->schoolClass)->class_name : null,
            'section_name'   => $promotion ? optional($promotion->section)->section_name : null,
            'created_by'     => auth()->id(),
        ]);

        return back()->with('status', 'Remark added.');
    }

    public function end(Request $request, $id)
    {
        $request->validate(['end_date' => 'required|date']);
        $record = StudentCounselling::findOrFail($id);
        $record->update(['end_date' => $request->end_date]);

        return back()->with('status', 'Counselling session marked as ended.');
    }

    public function report(Request $request)
    {
        $session_id = $this->getSchoolCurrentSession();
        $activeTab  = $request->get('tab', 'active');
        $threshold  = max(1, (int) $request->get('threshold', 60));
        $month      = $request->get('month', now()->format('Y-m'));

        try {
            $monthStart = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (\Exception $e) {
            $month = now()->format('Y-m');
            $monthStart = now()->startOfMonth();
        }
        $monthEnd = $monthStart->copy()->endOfMonth();

        // ── 1. ACTIVE CASES ──────────────────────────────────────────
        $activeCases = StudentCounselling::with(['student.admission', 'remarkLogs'])
            ->whereNull('end_date')
            ->orderBy('start_date')
            ->get()
            ->map(function ($c) use ($session_id) {
                $c = $this->withClassInfo($c, $session_id);
                $c->days_active = \Carbon\Carbon::parse($c->start_date)->diffInDays(now());
                return $c;
            });

        // ── 2. CLASS-WISE SUMMARY ────────────────────────────────────
        $allRecords = StudentCounselling::with(['student.admission'])
            ->get()
            ->map(function ($c) use ($session_id) { return $this->withClassInfo($c, $session_id); });

        $classwise = $allRecords
            ->groupBy('class_name')
            ->map(function ($items, $class) {
                return [
                    'class'  => $class,
                    'active' => $items->filter(function ($i) { return is_null($i->end_date); })->count(),
                    'closed' => $items->filter(function ($i) { return !is_null($i->end_date); })->count(),
                    'total'  => $items->count(),
                ];
            })
            ->filter(function ($r) { return $r['class'] !== '—'; })
            ->sortBy('class')
            ->values();

        // ── 3. REASON-WISE SUMMARY ───────────────────────────────────
        $reasonwise = StudentCounselling::selectRaw(
            "COALESCE(NULLIF(TRIM(reason), ''), 'Not Specified') as reason,
             COUNT(*) as total,
             SUM(CASE WHEN end_date IS NULL THEN 1 ELSE 0 END) as active,
             SUM(CASE WHEN end_date IS NOT NULL THEN 1 ELSE 0 END) as closed"
        )->groupBy('reason')->orderByDesc('total')->get();

        // ── 4. LONG-DURATION CASES ───────────────────────────────────
        $longDuration = StudentCounselling::with(['student.admission', 'remarkLogs'])
            ->whereNull('end_date')
            ->whereRaw('DATEDIFF(NOW(), start_date) >= ?', [$threshold])
            ->orderBy('start_date')
            ->get()
            ->map(function ($c) use ($session_id) {
                $c = $this->withClassInfo($c, $session_id);
                $c->days_active = \Carbon\Carbon::parse($c->start_date)->diffInDays(now());
                return $c;
            });

        // ── 5. MONTHLY ACTIVITY ──────────────────────────────────────
        $monthlyOpened  = StudentCounselling::whereBetween('start_date', [$monthStart, $monthEnd])->count();
        $monthlyClosed  = StudentCounselling::whereNotNull('end_date')
                            ->whereBetween('end_date', [$monthStart, $monthEnd])->count();
        $monthlyRemarks = CounsellingRemark::whereBetween('remark_date', [$monthStart, $monthEnd])->count();

        $monthlyCases = StudentCounselling::with(['student.admission', 'remarkLogs'])
            ->where('start_date', '<=', $monthEnd)
            ->where(function ($q) use ($monthStart) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $monthStart);
            })
            ->orderBy('start_date')
            ->get()
            ->map(function ($c) use ($session_id) { return $this->withClassInfo($c, $session_id); });

        // ── 6. CLOSED CASES ─────────────────────────────────────────
        $closedCases = StudentCounselling::with(['student.admission', 'remarkLogs'])
            ->whereNotNull('end_date')
            ->orderBy('end_date', 'desc')
            ->get()
            ->map(function ($c) use ($session_id) {
                $c = $this->withClassInfo($c, $session_id);
                $c->duration_days = \Carbon\Carbon::parse($c->start_date)
                    ->diffInDays(\Carbon\Carbon::parse($c->end_date));
                return $c;
            });

        // ── PDF DOWNLOAD ─────────────────────────────────────────────
        if ($request->get('pdf')) {
            $type = $activeTab;
            $pdf  = \Barryvdh\DomPDF\Facade\Pdf::loadView('counselling.report-pdf', compact(
                'type', 'activeCases', 'classwise', 'reasonwise',
                'longDuration', 'threshold',
                'monthlyCases', 'monthlyOpened', 'monthlyClosed', 'monthlyRemarks',
                'month', 'monthStart',
                'closedCases'
            ))->setPaper('a4', 'portrait');
            return $pdf->download('counselling-' . $type . '-' . now()->format('Y-m-d') . '.pdf');
        }

        return view('counselling.report', compact(
            'activeCases', 'classwise', 'reasonwise',
            'longDuration', 'threshold',
            'monthlyCases', 'monthlyOpened', 'monthlyClosed', 'monthlyRemarks',
            'month', 'monthStart',
            'closedCases',
            'activeTab'
        ));
    }

    private function withClassInfo(StudentCounselling $c, $session_id)
    {
        $promotion = Promotion::with(['schoolClass', 'section'])
            ->where('student_id', $c->student_user_id)
            ->where('session_id', $session_id)
            ->first();
        $c->class_name    = $promotion ? optional($promotion->schoolClass)->class_name : '—';
        $c->section_name  = $promotion ? optional($promotion->section)->section_name : '—';
        $admission        = optional($c->student)->admission;
        $c->display_id    = optional($admission)->general_id ?? optional($admission)->dga_admission_no ?? '—';
        $c->student_name  = trim(optional($c->student)->first_name . ' ' . optional($c->student)->last_name) ?: '—';
        return $c;
    }
}
