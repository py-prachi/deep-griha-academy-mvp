<?php
namespace App\Repositories;

use App\Models\FeePayment;
use App\Models\FeeLineItem;
use App\Models\FeeStructure;
use App\Interfaces\FeePaymentInterface;
use Illuminate\Support\Facades\DB;

class FeePaymentRepository implements FeePaymentInterface
{
    public function getByStudent($student_user_id)
    {
        return FeePayment::with('lineItems', 'recordedBy')
            ->where('student_user_id', $student_user_id)
            ->orderBy('payment_date', 'desc')
            ->get();
    }

    // Fee payments only — used for balance calculation
    public function getFeePaymentsByStudent($student_user_id)
    {
        return FeePayment::with('lineItems', 'recordedBy')
            ->where('student_user_id', $student_user_id)
            ->where('payment_category', FeePayment::CATEGORY_FEE)
            ->orderBy('payment_date', 'desc')
            ->get();
    }

    public function store($data)
    {
        DB::beginTransaction();
        try {
            // Determine payment_category from line items if provided
            // If any misc line item has amount > 0, it's a misc payment
            // If any fee line item has amount > 0, it's a fee payment
            // If no line items, default to fee
            $payment_category = FeePayment::CATEGORY_FEE; // default
            if (!empty($data['line_items'])) {
                $miscKeys = array_keys(FeeLineItem::miscLabels());
                $feeKeys  = array_keys(FeeLineItem::feeLabels());

                $hasMisc = false;
                $hasFee  = false;
                foreach ($data['line_items'] as $description => $amount) {
                    if ($amount > 0) {
                        if (in_array($description, $miscKeys)) $hasMisc = true;
                        if (in_array($description, $feeKeys))  $hasFee  = true;
                    }
                }

                // This should already be blocked at controller level,
                // but double-check here as well
                if ($hasMisc && $hasFee) {
                    throw new \Exception('Cannot mix fee and misc items in one payment. Please record separately.');
                }

                if ($hasMisc) {
                    $payment_category = FeePayment::CATEGORY_MISC;
                }
            }

            // Allow caller to override category (e.g. no line items selected but intent is misc)
            if (isset($data['payment_category'])) {
                $payment_category = $data['payment_category'];
            }

            $payment = FeePayment::create([
                'student_user_id'      => $data['student_user_id'],
                'challan_no'           => FeePayment::nextChallanNo(),
                'payment_date'         => $data['payment_date'],
                'amount_paid'          => $data['amount_paid'],
                'payment_mode'         => $data['payment_mode'],
                'payment_category'     => $payment_category,
                'cheque_no'            => $data['cheque_no']             ?? null,
                'cheque_date'          => $data['cheque_date']           ?? null,
                'bank_name'            => $data['bank_name']             ?? null,
                'transaction_ref'      => $data['transaction_ref']       ?? null,
                'is_internal_transfer' => $data['is_internal_transfer']  ?? false,
                'recorded_by'          => $data['recorded_by'],
                'notes'                => $data['notes']                 ?? null,
            ]);

            // Store line items
            if (!empty($data['line_items'])) {
                foreach ($data['line_items'] as $description => $amount) {
                    if ($amount > 0) {
                        FeeLineItem::create([
                            'fee_payment_id' => $payment->id,
                            'description'    => $description,
                            'amount'         => $amount,
                        ]);
                    }
                }
            }

            DB::commit();
            return $payment;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function findById($id)
    {
        return FeePayment::with('lineItems', 'student', 'recordedBy')
            ->findOrFail($id);
    }

    public function getDailyCollection($date)
    {
        return FeePayment::with('student', 'lineItems')
            ->whereDate('payment_date', $date)
            ->where('payment_category', FeePayment::CATEGORY_FEE)
            ->orderBy('challan_no')
            ->get();
    }

    public function getByDateRange($from, $to)
    {
        return FeePayment::with('student.admission.schoolClass', 'student.admission.section', 'lineItems')
            ->whereBetween('payment_date', [$from, $to])
            ->where('payment_category', FeePayment::CATEGORY_FEE)
            ->orderBy('payment_date')
            ->get();
    }

    // Misc sales by date range — for the new misc sales report
    public function getMiscByDateRange($from, $to)
    {
        return FeePayment::with('student.admission', 'lineItems')
            ->whereBetween('payment_date', [$from, $to])
            ->where('payment_category', FeePayment::CATEGORY_MISC)
            ->orderBy('payment_date')
            ->get();
    }

    public function getDefaulters($session_id)
    {
        return DB::select("
            SELECT
                u.id AS student_id,
                u.first_name,
                u.last_name,
                u.fee_category,
                u.admission_id,
                u.dga_admission_no,
                u.general_id,
                sc.class_name,
                s.section_name,
                COALESCE(fs.total_fee, 0) AS total_due,
                COALESCE(SUM(fp.amount_paid), 0) AS total_paid,
                COALESCE(fs.total_fee, 0) - COALESCE(SUM(fp.amount_paid), 0) AS balance
            FROM users u
            JOIN promotions p ON p.student_id = u.id AND p.session_id = ?
            JOIN school_classes sc ON sc.id = p.class_id
            JOIN sections s ON s.id = p.section_id
            LEFT JOIN fee_structures fs ON fs.class_id = p.class_id
                AND fs.session_id = ?
                AND fs.fee_category = u.fee_category
            LEFT JOIN fee_payments fp ON fp.student_user_id = u.id
                AND fp.payment_category = 'fee'
            WHERE u.role = 'student'
            GROUP BY u.id, u.first_name, u.last_name, u.fee_category,
                     u.admission_id, u.dga_admission_no, u.general_id, sc.class_name, s.section_name,
                     fs.total_fee
            HAVING balance > 0
            ORDER BY sc.class_name, s.section_name, u.first_name
        ", [$session_id, $session_id]);
    }

    public function getCategoryWiseSummary($session_id)
    {
        return DB::select("
            SELECT
                u.fee_category,
                COUNT(DISTINCT u.id) AS student_count,
                COALESCE(SUM(fs.total_fee), 0) AS total_due,
                COALESCE(SUM(fp_totals.amount_paid), 0) AS total_collected,
                COALESCE(SUM(fs.total_fee), 0) - COALESCE(SUM(fp_totals.amount_paid), 0) AS total_balance
            FROM users u
            JOIN promotions p ON p.student_id = u.id AND p.session_id = ?
            LEFT JOIN fee_structures fs ON fs.class_id = p.class_id
                AND fs.session_id = ?
                AND fs.fee_category = u.fee_category
            LEFT JOIN (
                SELECT student_user_id, SUM(amount_paid) as amount_paid
                FROM fee_payments
                WHERE payment_category = 'fee'
                GROUP BY student_user_id
            ) fp_totals ON fp_totals.student_user_id = u.id
            WHERE u.role = 'student'
            GROUP BY u.fee_category
        ", [$session_id, $session_id]);
    }
}
