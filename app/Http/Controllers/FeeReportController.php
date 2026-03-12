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
        $current_school_session_id = $this->getSchoolCurrentSession();
        $defaulters = $this->feePaymentRepository->getDefaulters($current_school_session_id);
        if ($request->get('pdf')) {
            $pdf = Pdf::loadView('reports.fees.defaulters-pdf', compact('defaulters'))->setPaper('a4', 'portrait');
            return $pdf->download('defaulters.pdf');
        }
        return view('reports.fees.defaulters', compact('defaulters'));
    }

    public function categorySummary(Request $request)
    {
        $current_school_session_id = $this->getSchoolCurrentSession();
        $summary = $this->feePaymentRepository->getCategoryWiseSummary($current_school_session_id);
        if ($request->get('pdf')) {
            $pdf = Pdf::loadView('reports.fees.category-summary-pdf', compact('summary'))->setPaper('a4', 'portrait');
            return $pdf->download('category-summary.pdf');
        }
        return view('reports.fees.category-summary', compact('summary'));
    }

    public function admissions(Request $request)
    {
        $session       = $this->schoolSessionRepository->getLatestSession();
        $academic_year = $session->session_name;
        $summary = [
            'inquiry'   => Admission::where('academic_year', $academic_year)->where('status', 'inquiry')->count(),
            'pending'   => Admission::where('academic_year', $academic_year)->where('status', 'pending')->count(),
            'confirmed' => Admission::where('academic_year', $academic_year)->where('status', 'confirmed')->count(),
            'cancelled' => Admission::withTrashed()->where('academic_year', $academic_year)->where('status', 'cancelled')->count(),
        ];
        $admissions = Admission::with('schoolClass')
            ->where('academic_year', $academic_year)
            ->orderBy('status')->orderBy('created_at', 'desc')
            ->withTrashed()->get();
        if ($request->get('pdf')) {
            $pdf = Pdf::loadView('reports.admissions-pdf', compact('summary', 'admissions', 'academic_year'))->setPaper('a4', 'portrait');
            return $pdf->download('admissions-report.pdf');
        }
        return view('reports.admissions', compact('summary', 'admissions', 'academic_year'));
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
        $current_school_session_id = $this->getSchoolCurrentSession();
        $students = User::with(['admission'])
            ->join('promotions', 'promotions.student_id', '=', 'users.id')
            ->join('school_classes', 'school_classes.id', '=', 'promotions.class_id')
            ->join('sections', 'sections.id', '=', 'promotions.section_id')
            ->where('promotions.session_id', $current_school_session_id)
            ->where('users.fee_category', 'rte')
            ->where('users.role', 'student')
            ->select('users.*', 'school_classes.class_name', 'sections.section_name')
            ->orderBy('school_classes.id')
            ->get();
        if ($request->get('pdf')) {
            $pdf = Pdf::loadView('reports.rte-pdf', compact('students'))->setPaper('a4', 'portrait');
            return $pdf->download('rte-students.pdf');
        }
        return view('reports.rte', compact('students'));
    }
}
