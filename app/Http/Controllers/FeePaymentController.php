<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Interfaces\FeePaymentInterface;
use App\Interfaces\FeeStructureInterface;
use App\Interfaces\SchoolSessionInterface;
use App\Models\User;
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
        $this->feePaymentRepository  = $feePaymentRepository;
        $this->feeStructureRepository = $feeStructureRepository;
        $this->schoolSessionRepository = $schoolSessionRepository;
    }

    // Student fee ledger — shows total due, paid, balance, payment history
    public function ledger($student_id)
    {
        $student = User::with('admission')->findOrFail($student_id);
        $current_school_session_id = $this->getSchoolCurrentSession();
        $session = $this->schoolSessionRepository->getLatestSession();

        // Get fee structure for this student's class and category
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

        $payments = $this->feePaymentRepository->getByStudent($student_id);
        $totalPaid = $payments->sum('amount_paid');
        $totalDue  = $feeStructure ? $feeStructure->total_fee : 0;
        $balance   = $totalDue - $totalPaid;

        return view('fees.ledger', [
            'student'      => $student,
            'promotion'    => $promotion,
            'feeStructure' => $feeStructure,
            'payments'     => $payments,
            'totalDue'     => $totalDue,
            'totalPaid'    => $totalPaid,
            'balance'      => $balance,
        ]);
    }

    // Record payment form
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

        $payments  = $this->feePaymentRepository->getByStudent($student_id);
        $totalPaid = $payments->sum('amount_paid');
        $totalDue  = $feeStructure ? $feeStructure->total_fee : 0;
        $balance   = $totalDue - $totalPaid;

        return view('fees.create', [
            'student'      => $student,
            'promotion'    => $promotion,
            'feeStructure' => $feeStructure,
            'totalDue'     => $totalDue,
            'totalPaid'    => $totalPaid,
            'balance'      => $balance,
            'lineItemLabels' => FeeLineItem::descriptionLabels(),
        ]);
    }

    // Store payment
    public function store(Request $request, $student_id)
    {
        $request->validate([
            'payment_date' => 'required|date',
            'amount_paid'  => 'required|numeric|min:1',
            'payment_mode' => 'required|in:cash,cheque,qr',
            'cheque_no'    => 'nullable|required_if:payment_mode,cheque',
            'cheque_date'  => 'nullable|required_if:payment_mode,cheque|date',
            'bank_name'    => 'nullable|required_if:payment_mode,cheque',
            'transaction_ref' => 'nullable|required_if:payment_mode,qr',
        ]);

        try {
            $payment = $this->feePaymentRepository->store([
                'student_user_id'      => $student_id,
                'payment_date'         => $request->payment_date,
                'amount_paid'          => $request->amount_paid,
                'payment_mode'         => $request->payment_mode,
                'cheque_no'            => $request->cheque_no,
                'cheque_date'          => $request->cheque_date,
                'bank_name'            => $request->bank_name,
                'transaction_ref'      => $request->transaction_ref,
                'is_internal_transfer' => $request->is_internal_transfer ?? false,
                'recorded_by'          => auth()->id(),
                'notes'                => $request->notes,
                'line_items'           => $request->line_items ?? [],
            ]);

            return redirect()->route('fees.challan', $payment->id)
                ->with('status', 'Payment recorded successfully!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage())->withInput();
        }
    }

    // Challan view
    public function challan($payment_id)
    {
        $payment = $this->feePaymentRepository->findById($payment_id);
        $student = $payment->student;

        $current_school_session_id = $this->getSchoolCurrentSession();
        $promotion = \App\Models\Promotion::where('student_id', $student->id)
            ->where('session_id', $current_school_session_id)
            ->with('section.schoolClass')
            ->first();

        return view('fees.challan', [
            'payment'   => $payment,
            'student'   => $student,
            'promotion' => $promotion,
        ]);
    }

    // Challan PDF download
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
