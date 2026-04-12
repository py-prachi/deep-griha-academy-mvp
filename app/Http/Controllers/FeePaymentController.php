<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Interfaces\FeePaymentInterface;
use App\Interfaces\FeeStructureInterface;
use App\Interfaces\SchoolSessionInterface;
use App\Models\User;
use App\Models\FeePayment;
use App\Models\FeeStructure;
use App\Models\FeeLineItem;
use Barryvdh\DomPDF\Facade\Pdf;

class FeePaymentController extends Controller
{
    use SchoolSession;

    protected $feePaymentRepository;
    protected $feeStructureRepository;
    protected $schoolSessionRepository;

    public function __construct(
        FeePaymentInterface $feePaymentRepository,
        FeeStructureInterface $feeStructureRepository,
        SchoolSessionInterface $schoolSessionRepository
    ) {
        $this->middleware(function ($request, $next) {
            if (!in_array(auth()->user()->role, ['admin'])) {
                abort(403);
            }
            return $next($request);
        });
        $this->feePaymentRepository   = $feePaymentRepository;
        $this->feeStructureRepository = $feeStructureRepository;
        $this->schoolSessionRepository = $schoolSessionRepository;
    }

    // ── HELPERS ───────────────────────────────────────────────────────────

    // Calculates balance using fee payments only (excludes misc)
    private function calculateBalance($student, $feeStructure, $student_id)
    {
        $feePayments = $this->feePaymentRepository->getFeePaymentsByStudent($student_id);
        $totalPaid   = $feePayments->sum('amount_paid');
        if ($feeStructure) {
            $effectiveTuition = $feeStructure->tuitionFeeForGender($student->gender ?? 'Male');
            $totalDue = $feeStructure->admission_fee
                      + $effectiveTuition
                      + $feeStructure->transport_fee
                      + $feeStructure->other_fee;
        } else {
            $totalDue = 0;
        }
        $balance = $totalDue - $totalPaid;
        return compact('totalPaid', 'totalDue', 'balance');
    }

    // ── COLLECT FEE (search entry point) ─────────────────────────────────

    public function collectFee(Request $request)
    {
        $search = $request->query('q');
        $students = collect();

        if ($search && strlen($search) >= 2) {
            $current_school_session_id = $this->getSchoolCurrentSession();
            $session = $this->schoolSessionRepository->getLatestSession();

            $students = User::where('role', 'student')
                ->where('student_status', 'active')
                ->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', '%' . $search . '%')
                      ->orWhere('last_name', 'like', '%' . $search . '%')
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ['%' . $search . '%']);
                })
                ->with('admission')
                ->get()
                ->map(function ($student) use ($current_school_session_id, $session) {
                    $promotion = \App\Models\Promotion::where('student_id', $student->id)
                        ->where('session_id', $current_school_session_id)
                        ->with('schoolClass', 'section')
                        ->first();

                    $admission = $student->admission;
                    $feeStructure = null;
                    if ($promotion) {
                        $feeStructure = $this->feeStructureRepository->getByClassAndCategory(
                            $promotion->class_id, $session->session_name, $student->fee_category ?? 'general'
                        );
                    } elseif ($admission) {
                        $feeStructure = $this->feeStructureRepository->getByClassAndCategory(
                            $admission->class_id, $session->session_name, $admission->fee_category ?? 'general'
                        );
                    }

                    $calc = $this->calculateBalance($student, $feeStructure, $student->id);

                    $student->_promotion    = $promotion;
                    $student->_balance      = $calc['balance'];
                    $student->_totalDue     = $calc['totalDue'];
                    $student->_totalPaid    = $calc['totalPaid'];
                    return $student;
                });
        }

        return view('fees.collect', compact('students', 'search'));
    }

    // ── LEDGER ────────────────────────────────────────────────────────────

    public function ledger($student_id)
    {
        $student = User::with('admission')->findOrFail($student_id);
        $current_school_session_id = $this->getSchoolCurrentSession();
        $session = $this->schoolSessionRepository->getLatestSession();

        $promotion = \App\Models\Promotion::where('student_id', $student_id)
            ->where('session_id', $current_school_session_id)
            ->with('section.schoolClass')
            ->first();

        $feeStructure = null;
        $admission = $student->admission;
        if ($promotion) {
            $feeStructure = $this->feeStructureRepository->getByClassAndCategory(
                $promotion->class_id,
                $session->session_name,
                $student->fee_category ?? 'general'
            );
        } elseif ($admission) {
            // Fallback for students without a promotion record
            $feeStructure = $this->feeStructureRepository->getByClassAndCategory(
                $admission->class_id,
                $session->session_name,
                $admission->fee_category ?? 'general'
            );
        }

        // All payments for history display (fee + misc)
        $payments = $this->feePaymentRepository->getByStudent($student_id);

        // Balance uses fee payments only
        $calc = $this->calculateBalance($student, $feeStructure, $student_id);

        return view('fees.ledger', [
            'student'      => $student,
            'promotion'    => $promotion,
            'feeStructure' => $feeStructure,
            'payments'     => $payments,
            'totalDue'     => $calc['totalDue'],
            'totalPaid'    => $calc['totalPaid'],
            'balance'      => $calc['balance'],
        ]);
    }

    // ── CREATE PAYMENT FORM ───────────────────────────────────────────────

    public function create($student_id)
    {
        $student = User::with('admission')->findOrFail($student_id);
        $current_school_session_id = $this->getSchoolCurrentSession();
        $session = $this->schoolSessionRepository->getLatestSession();

        $promotion = \App\Models\Promotion::where('student_id', $student_id)
            ->where('session_id', $current_school_session_id)
            ->with('section.schoolClass')
            ->first();

        $feeStructure = null;
        if ($promotion) {
            $feeStructure = $this->feeStructureRepository->getByClassAndCategory(
                $promotion->class_id,
                $session->session_name,
                $student->fee_category ?? 'general'
            );
        }

        $calc = $this->calculateBalance($student, $feeStructure, $student_id);

        return view('fees.create', [
            'student'      => $student,
            'promotion'    => $promotion,
            'feeStructure' => $feeStructure,
            'totalDue'     => $calc['totalDue'],
            'totalPaid'    => $calc['totalPaid'],
            'balance'      => $calc['balance'],
            'feeLabels'    => FeeLineItem::feeLabels(),
            'miscLabels'   => FeeLineItem::miscLabels(),
        ]);
    }

    // ── STORE PAYMENT ─────────────────────────────────────────────────────

    public function store(Request $request, $student_id)
    {
        $request->validate([
            'payment_date'    => 'required|date',
            'amount_paid'     => 'required|numeric|min:1',
            'payment_mode'    => 'required|in:cash,cheque,qr',
            'payment_category'=> 'required|in:fee,misc',
            'cheque_no'       => 'nullable|required_if:payment_mode,cheque',
            'cheque_date'     => 'nullable|required_if:payment_mode,cheque|date',
            'bank_name'       => 'nullable|required_if:payment_mode,cheque',
            'transaction_ref' => 'nullable|required_if:payment_mode,qr',
        ]);

        // Block mixed fee + misc line items
        $lineItems = $request->line_items ?? [];
        $miscKeys  = array_keys(FeeLineItem::miscLabels());
        $feeKeys   = array_keys(FeeLineItem::feeLabels());

        $hasMisc = false;
        $hasFee  = false;
        foreach ($lineItems as $description => $amount) {
            if ($amount > 0) {
                if (in_array($description, $miscKeys)) $hasMisc = true;
                if (in_array($description, $feeKeys))  $hasFee  = true;
            }
        }

        if ($hasMisc && $hasFee) {
            return back()
                ->withError('Fee and misc items cannot be mixed in one payment. Please record them as separate transactions.')
                ->withInput();
        }

        try {
            $payment = $this->feePaymentRepository->store([
                'student_user_id'      => $student_id,
                'payment_date'         => $request->payment_date,
                'amount_paid'          => $request->amount_paid,
                'payment_mode'         => $request->payment_mode,
                'payment_category'     => $request->payment_category,
                'cheque_no'            => $request->cheque_no,
                'cheque_date'          => $request->cheque_date,
                'bank_name'            => $request->bank_name,
                'transaction_ref'      => $request->transaction_ref,
                'is_internal_transfer' => $request->is_internal_transfer ?? false,
                'recorded_by'          => auth()->id(),
                'notes'                => $request->notes,
                'line_items'           => $lineItems,
            ]);

            return redirect()->route('fees.challan', $payment->id)
                ->with('status', 'Payment recorded successfully!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage())->withInput();
        }
    }

    // ── CHALLAN ───────────────────────────────────────────────────────────

    public function challan($payment_id)
    {
        $payment = $this->feePaymentRepository->findById($payment_id);
        $student = $payment->student;

        $current_school_session_id = $this->getSchoolCurrentSession();
        $session = $this->schoolSessionRepository->getLatestSession();
        $promotion = \App\Models\Promotion::where('student_id', $student->id)
            ->where('session_id', $current_school_session_id)
            ->with('section.schoolClass')
            ->first();

        $balance = null;
        if ($payment->payment_category === 'fee') {
            $feeStructure = null;
            if ($promotion) {
                $feeStructure = $this->feeStructureRepository->getByClassAndCategory(
                    $promotion->class_id, $session->session_name, $student->fee_category ?? 'general'
                );
            } elseif ($student->admission) {
                $feeStructure = $this->feeStructureRepository->getByClassAndCategory(
                    $student->admission->class_id, $session->session_name, $student->admission->fee_category ?? 'general'
                );
            }
            $calc    = $this->calculateBalance($student, $feeStructure, $student->id);
            $balance = $calc['balance'];
        }

        return view('fees.challan', [
            'payment'   => $payment,
            'student'   => $student,
            'promotion' => $promotion,
            'balance'   => $balance,
        ]);
    }

    public function challanPdf($payment_id)
    {
        $payment = $this->feePaymentRepository->findById($payment_id);
        $student = $payment->student;

        $current_school_session_id = $this->getSchoolCurrentSession();
        $promotion = \App\Models\Promotion::where('student_id', $student->id)
            ->where('session_id', $current_school_session_id)
            ->with('section.schoolClass')
            ->first();

        $pdf = Pdf::loadView('fees.challan-pdf', [
            'payment'   => $payment,
            'student'   => $student,
            'promotion' => $promotion,
        ])->setPaper('a5', 'portrait');

        return $pdf->download('challan-' . str_pad($payment->challan_no, 4, '0', STR_PAD_LEFT) . '.pdf');
    }
}
