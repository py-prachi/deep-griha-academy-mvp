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
            $pdf = Pdf::loadView('reports.admissions-pdf', [
                'summary'      => $summary,
                'admissions'   => $allForPdf,
                'academic_year'=> $academic_year,
                'statusFilter' => $statusFilter,
                'classFilter'  => $classFilter,
                'schoolClasses'=> $schoolClasses,
            ])->setPaper('a4', 'portrait');
            $filename = 'admissions-' . $academic_year
                . ($statusFilter ? '-' . $statusFilter : '')
                . '.pdf';
            return $pdf->download($filename);
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

        $baseQuery = function ($category) use ($selectedSessionId) {
            return User::with(['admission'])
                ->join('promotions', 'promotions.student_id', '=', 'users.id')
                ->join('school_classes', 'school_classes.id', '=', 'promotions.class_id')
                ->join('sections', 'sections.id', '=', 'promotions.section_id')
                ->where('promotions.session_id', $selectedSessionId)
                ->where('users.fee_category', $category)
                ->where('users.role', 'student')
                ->select('users.*', 'school_classes.class_name', 'sections.section_name')
                ->orderBy('school_classes.id')
                ->get();
        };

        $rteStudents      = $baseQuery('rte');
        $discountStudents = $baseQuery('discount');
        $cocStudents      = $baseQuery('coc');

        if ($request->get('pdf')) {
            $category = $request->get('category', 'rte');
            if ($category === 'discount') {
                $students = $discountStudents;
            } elseif ($category === 'coc') {
                $students = $cocStudents;
            } else {
                $students = $rteStudents;
            }
            $pdf = Pdf::loadView('reports.rte-pdf', compact('students', 'category', 'selectedSession'))
                ->setPaper('a4', 'portrait');
            return $pdf->download($category . '-students-' . ($selectedSession->session_name ?? '') . '.pdf');
        }

        return view('reports.rte', compact(
            'rteStudents', 'discountStudents', 'cocStudents',
            'sessions', 'selectedSessionId', 'selectedSession'
        ));
    }

    public function studentInfo(Request $request)
    {
        $sessions          = $this->schoolSessionRepository->getAll();
        $latestSession     = $this->schoolSessionRepository->getLatestSession();
        $selectedSessionId = $request->get('session_id', $latestSession->id);
        $selectedSession   = $sessions->firstWhere('id', $selectedSessionId);
        $schoolClasses     = \App\Models\SchoolClass::where('session_id', $selectedSessionId)->orderBy('id')->get();

        $availableFields = [
            'student_name'         => ['label' => 'Student Name',          'group' => 'Student'],
            'class_div'            => ['label' => 'Class / Division',       'group' => 'Student'],
            'admission_reg_id'     => ['label' => 'DGA / General ID',           'group' => 'Student'],
            'aadhaar_no'           => ['label' => 'Aadhaar No.',             'group' => 'Student'],
            'pen_id'               => ['label' => 'PEN ID',                  'group' => 'Student'],
            'date_of_birth'        => ['label' => 'Date of Birth',           'group' => 'Student'],
            'gender'               => ['label' => 'Gender',                  'group' => 'Student'],
            'blood_type'           => ['label' => 'Blood Group',             'group' => 'Student'],
            'caste'                => ['label' => 'Caste',                   'group' => 'Student'],
            'religion'             => ['label' => 'Religion',                'group' => 'Student'],
            'fee_category'         => ['label' => 'Fee Category',            'group' => 'Student'],
            'father_name'          => ['label' => "Father's Name",           'group' => 'Family'],
            'father_occupation'    => ['label' => "Father's Occupation",     'group' => 'Family'],
            'father_phone'         => ['label' => "Father's Phone",          'group' => 'Family'],
            'mother_name'          => ['label' => "Mother's Name",           'group' => 'Family'],
            'mother_occupation'    => ['label' => "Mother's Occupation",     'group' => 'Family'],
            'mother_phone'         => ['label' => "Mother's Phone",          'group' => 'Family'],
            'contact_emergency'    => ['label' => 'Emergency Contact',       'group' => 'Family'],
            'full_address'         => ['label' => 'Full Address',            'group' => 'Address'],
            'village'              => ['label' => 'Village / Area',          'group' => 'Address'],
            'city'                 => ['label' => 'City',                    'group' => 'Address'],
            'zip'                  => ['label' => 'PIN Code',                'group' => 'Address'],
            'distance_from_school' => ['label' => 'Distance from School',    'group' => 'Address'],
            'transport_required'   => ['label' => 'Transport Required',      'group' => 'Address'],
            'previous_school'      => ['label' => 'Previous School',         'group' => 'Other'],
        ];

        $formSubmitted  = $request->has('generate');
        $selectedFields = $request->get('fields', $formSubmitted ? [] : ['student_name', 'class_div']);
        $classFilter    = $request->get('class_id');
        $categoryFilter = $request->get('fee_category');

        $students = null;
        if ($formSubmitted || $request->get('pdf')) {
            $query = User::with(['admission'])
                ->join('promotions',    'promotions.student_id', '=', 'users.id')
                ->join('school_classes','school_classes.id',     '=', 'promotions.class_id')
                ->join('sections',      'sections.id',           '=', 'promotions.section_id')
                ->where('promotions.session_id', $selectedSessionId)
                ->where('users.role', 'student')
                ->select('users.*', 'school_classes.class_name', 'sections.section_name');

            if ($classFilter) {
                $query->where('promotions.class_id', $classFilter);
            }
            if ($categoryFilter) {
                $query->where('users.fee_category', $categoryFilter);
            }

            $students = $query->orderBy('school_classes.id')->orderBy('users.first_name')->get();
        }

        if ($request->get('pdf') && $students !== null) {
            $pdfData = compact('students', 'selectedFields', 'availableFields', 'selectedSession');
            $pdf = Pdf::loadView('reports.student-info-pdf', $pdfData)->setPaper('a4', 'landscape');
            return $pdf->download('student-info-' . ($selectedSession->session_name ?? '') . '.pdf');
        }

        return view('reports.student-info', compact(
            'sessions', 'selectedSessionId', 'selectedSession',
            'availableFields', 'selectedFields', 'students',
            'schoolClasses', 'classFilter', 'categoryFilter', 'formSubmitted'
        ));
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
