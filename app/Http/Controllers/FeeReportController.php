<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Interfaces\FeePaymentInterface;
use App\Interfaces\SchoolSessionInterface;
use App\Interfaces\SchoolClassInterface;
use App\Models\Admission;
use App\Models\User;
use App\Models\Promotion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class FeeReportController extends Controller
{
    use SchoolSession;

    protected $feePaymentRepository;
    protected $schoolSessionRepository;
    protected $schoolClassRepository;

    public function __construct(
        FeePaymentInterface $feePaymentRepository,
        SchoolSessionInterface $schoolSessionRepository,
        SchoolClassInterface $schoolClassRepository
    ) {
        $this->middleware(function ($request, $next) {
            if (!in_array(auth()->user()->role, ['admin'])) {
                abort(403);
            }
            return $next($request);
        });
        $this->feePaymentRepository    = $feePaymentRepository;
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository   = $schoolClassRepository;
    }

    public function daily(Request $request)
    {
        $date     = $request->get('date', today()->toDateString());
        $payments = $this->feePaymentRepository->getDailyCollection($date);
        $total    = $payments->sum('amount_paid');
        if ($request->get('pdf')) {
            $pdf = Pdf::loadView('reports.fees.daily-pdf', compact('payments', 'total', 'date'))->setPaper('a4', 'portrait');
            return $pdf->download('daily-collection-' . $date . '.pdf');
        }
        return view('reports.fees.daily', compact('payments', 'total', 'date'));
    }

    public function dateRange(Request $request)
    {
        $from     = $request->get('from', today()->startOfMonth()->toDateString());
        $to       = $request->get('to', today()->toDateString());
        $payments = $this->feePaymentRepository->getByDateRange($from, $to);
        $total    = $payments->sum('amount_paid');
        if ($request->get('pdf')) {
            $pdf = Pdf::loadView('reports.fees.date-range-pdf', compact('payments', 'total', 'from', 'to'))->setPaper('a4', 'portrait');
            return $pdf->download('collection-' . $from . '-to-' . $to . '.pdf');
        }
        return view('reports.fees.date-range', compact('payments', 'total', 'from', 'to'));
    }

    public function defaulters(Request $request)
    {
        $sessions = $this->schoolSessionRepository->getAll();
        $selectedSessionId = $request->get('session_id', $this->getSchoolCurrentSession());
        $selectedSession = $sessions->firstWhere('id', $selectedSessionId);
        $defaulters = $this->feePaymentRepository->getDefaulters($selectedSessionId);
        if ($request->get('pdf')) {
            $pdf = Pdf::loadView('reports.fees.defaulters-pdf', compact('defaulters'))->setPaper('a4', 'portrait');
            return $pdf->download('defaulters.pdf');
        }
        return view('reports.fees.defaulters', compact('defaulters', 'sessions', 'selectedSessionId', 'selectedSession'));
    }

    public function categorySummary(Request $request)
    {
        $sessions = $this->schoolSessionRepository->getAll();
        $selectedSessionId = $request->get('session_id', $this->getSchoolCurrentSession());
        $selectedSession = $sessions->firstWhere('id', $selectedSessionId);
        $feeStructureCount = \App\Models\FeeStructure::where('session_id', $selectedSessionId)->count();
        $summary = $this->feePaymentRepository->getCategoryWiseSummary($selectedSessionId);
        if ($request->get('pdf')) {
            $pdf = Pdf::loadView('reports.fees.category-summary-pdf', compact('summary'))->setPaper('a4', 'portrait');
            return $pdf->download('category-summary.pdf');
        }
        return view('reports.fees.category-summary', compact('summary', 'sessions', 'selectedSessionId', 'selectedSession', 'feeStructureCount'));
    }

    public function admissions(Request $request)
    {
        $sessions        = $this->schoolSessionRepository->getAll();
        $latestSession   = $this->schoolSessionRepository->getLatestSession();
        $selectedSessionId = $request->get('session_id', $latestSession->id);
        $selectedSession = $sessions->firstWhere('id', $selectedSessionId);
        $academic_year   = $selectedSession ? $selectedSession->session_name : $latestSession->session_name;

        // Students active in this session via promotions
        $promotedStudentIds = Promotion::where('session_id', $selectedSessionId)->pluck('student_id');

        // New admissions created this academic year (inquiry/pending/cancelled)
        $newAdmissionIds = Admission::withTrashed()
            ->where('academic_year', $academic_year)
            ->pluck('id');

        // Admissions linked to promoted students in this session
        $promotedAdmissionIds = Admission::whereIn('student_user_id', $promotedStudentIds)->pluck('id');

        // Union: all relevant admission IDs for this session
        $allIds = $newAdmissionIds->merge($promotedAdmissionIds)->unique();

        $summary = [
            'inquiry'   => Admission::whereIn('id', $allIds)->where('status', 'inquiry')->count(),
            'pending'   => Admission::whereIn('id', $allIds)->where('status', 'pending')->count(),
            'confirmed' => Admission::whereIn('id', $allIds)->where('status', 'confirmed')->count(),
            'cancelled' => Admission::withTrashed()->whereIn('id', $allIds)->where('status', 'cancelled')->count(),
            'exited'    => Admission::whereIn('id', $allIds)->where('status', 'exited')->count(),
            'graduated' => Admission::whereIn('id', $allIds)->where('status', 'graduated')->count(),
        ];

        $statusFilter = $request->get('status');
        $classFilter  = $request->get('class_id');
        $query = Admission::with('schoolClass')->withTrashed()->whereIn('id', $allIds);
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }
        if ($classFilter) {
            $query->where('class_id', $classFilter);
        }
        $admissions = $query->orderBy('status')->orderBy('created_at', 'desc')->paginate(50)->withQueryString();

        $schoolClasses = \App\Models\SchoolClass::where('session_id', $selectedSessionId)->orderBy('id')->get();

        if ($request->get('pdf')) {
            $allForPdf = Admission::with('schoolClass')->withTrashed()->whereIn('id', $allIds)
                ->when($statusFilter, fn($q) => $q->where('status', $statusFilter))
                ->when($classFilter,  fn($q) => $q->where('class_id', $classFilter))
                ->orderBy('status')->orderBy('created_at', 'desc')->get();
            $pdf = Pdf::loadView('reports.admissions-pdf', ['summary' => $summary, 'admissions' => $allForPdf, 'academic_year' => $academic_year])->setPaper('a4', 'portrait');
            return $pdf->download('admissions-report.pdf');
        }
        return view('reports.admissions', compact('summary', 'admissions', 'academic_year', 'statusFilter', 'classFilter', 'schoolClasses', 'sessions', 'selectedSessionId', 'selectedSession'));
    }

    public function classStrength(Request $request)
    {
        $current_school_session_id = $this->getSchoolCurrentSession();
        $strength = DB::select("
            SELECT sc.class_name, s.section_name,
                COUNT(p.student_id) as total,
                SUM(CASE WHEN u.fee_category = 'general'  THEN 1 ELSE 0 END) as general,
                SUM(CASE WHEN u.fee_category = 'rte'      THEN 1 ELSE 0 END) as rte,
                SUM(CASE WHEN u.fee_category = 'coc'      THEN 1 ELSE 0 END) as coc,
                SUM(CASE WHEN u.fee_category = 'discount' THEN 1 ELSE 0 END) as discount
            FROM promotions p
            JOIN users u ON u.id = p.student_id
            JOIN school_classes sc ON sc.id = p.class_id
            JOIN sections s ON s.id = p.section_id
            WHERE p.session_id = ? AND u.role = 'student'
            GROUP BY sc.id, sc.class_name, s.section_name
            ORDER BY sc.id, s.section_name
        ", [$current_school_session_id]);
        if ($request->get('pdf')) {
            $pdf = Pdf::loadView('reports.class-strength-pdf', compact('strength'))->setPaper('a4', 'portrait');
            return $pdf->download('class-strength.pdf');
        }
        return view('reports.class-strength', compact('strength'));
    }

    public function rte(Request $request)
    {
        $sessions = $this->schoolSessionRepository->getAll();
        $selectedSessionId = $request->get('session_id', $this->getSchoolCurrentSession());
        $selectedSession = $sessions->firstWhere('id', $selectedSessionId);
        $students = User::with(['admission'])
            ->join('promotions', 'promotions.student_id', '=', 'users.id')
            ->join('school_classes', 'school_classes.id', '=', 'promotions.class_id')
            ->join('sections', 'sections.id', '=', 'promotions.section_id')
            ->where('promotions.session_id', $selectedSessionId)
            ->where('users.fee_category', 'rte')
            ->where('users.role', 'student')
            ->select('users.*', 'school_classes.class_name', 'sections.section_name')
            ->orderBy('school_classes.id')
            ->get();
        if ($request->get('pdf')) {
            $pdf = Pdf::loadView('reports.rte-pdf', compact('students'))->setPaper('a4', 'portrait');
            return $pdf->download('rte-students.pdf');
        }
        return view('reports.rte', compact('students', 'sessions', 'selectedSessionId', 'selectedSession'));
    }

        public function miscSales(Request $request)
    {
        $from = $request->get('from', today()->startOfMonth()->toDateString());
        $to   = $request->get('to',   today()->toDateString());

        $payments = $this->feePaymentRepository->getMiscByDateRange($from, $to);

        $summary = [];
        $labels  = \App\Models\FeeLineItem::miscLabels();
        foreach ($payments as $payment) {
            foreach ($payment->lineItems as $item) {
                $key = $item->description;
                if (!isset($summary[$key])) {
                    $summary[$key] = ['label' => $labels[$key] ?? $key, 'total' => 0, 'count' => 0];
                }
                $summary[$key]['total'] += $item->amount;
                $summary[$key]['count'] += 1;
            }
        }

        $grandTotal = array_sum(array_column($summary, 'total'));

        if ($request->get('pdf')) {
            $pdf = Pdf::loadView('reports.misc-sales-pdf', compact('payments', 'summary', 'grandTotal', 'from', 'to'))
                ->setPaper('a4', 'portrait');
            return $pdf->download('misc-sales-' . $from . '-to-' . $to . '.pdf');
        }

        return view('reports.misc-sales', compact('payments', 'summary', 'grandTotal', 'from', 'to'));
    }
}
