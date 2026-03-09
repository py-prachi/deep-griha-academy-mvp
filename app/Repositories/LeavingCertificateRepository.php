<?php

namespace App\Repositories;

use App\Interfaces\LeavingCertificateInterface;
use App\Models\LeavingCertificate;
use Illuminate\Support\Facades\DB;

class LeavingCertificateRepository implements LeavingCertificateInterface
{
    public function getAll(array $filters = [])
    {
        $query = LeavingCertificate::with(['admission', 'issuedBy'])
            ->orderBy('id', 'desc');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('lc_number', 'like', "%{$search}%")
                  ->orWhere('pupil_name', 'like', "%{$search}%")
                  ->orWhereHas('admission', function ($sq) use ($search) {
                      $sq->where('student_name', 'like', "%{$search}%")
                         ->orWhere('dga_admission_no', 'like', "%{$search}%");
                  });
            });
        }

        if (!empty($filters['from_date'])) {
            $query->whereDate('issue_date', '>=', $filters['from_date']);
        }

        if (!empty($filters['to_date'])) {
            $query->whereDate('issue_date', '<=', $filters['to_date']);
        }

        return $query->paginate(20)->withQueryString();
    }

    public function findById(int $id)
    {
        return LeavingCertificate::with(['admission', 'issuedBy'])->findOrFail($id);
    }

    public function findByAdmission(int $admissionId)
    {
        return LeavingCertificate::where('admission_id', $admissionId)
            ->orderBy('id', 'desc')
            ->get();
    }

    public function create(array $data): LeavingCertificate
    {
        return LeavingCertificate::create($data);
    }

    public function update(int $id, array $data): bool
    {
        return LeavingCertificate::where('id', $id)->update($data) > 0;
    }

    public function delete(int $id): bool
    {
        return LeavingCertificate::destroy($id) > 0;
    }

    /**
     * Check outstanding fees for a confirmed admission.
     */
    public function checkFeesDue(int $admissionId): array
    {
        $admission = DB::table('admissions')->where('id', $admissionId)->first();
        if (!$admission || !$admission->student_user_id) {
            return ['has_due' => false, 'amount' => 0];
        }
        $studentUserId = $admission->student_user_id;

        $promotion = DB::table('promotions')
            ->where('student_id', $studentUserId)
            ->orderByDesc('id')
            ->first();

        $totalDue = 0;
        if ($promotion) {
            $student = DB::table('users')->where('id', $studentUserId)->first();
            $feeCategory = $student->fee_category ?? 'general';
            $feeStructure = DB::table('fee_structures')
                ->where('class_id', $promotion->class_id)
                ->where('fee_category', $feeCategory)
                ->orderByDesc('id')
                ->first();
            $totalDue = $feeStructure ? (float) $feeStructure->total_fee : 0;
        }

        $totalPaid = (float) DB::table('fee_payments')
            ->where('student_user_id', $studentUserId)
            ->sum('amount_paid');

        $balance = max(0, $totalDue - $totalPaid);
        return [
            'has_due' => $balance > 0,
            'amount'  => $balance,
        ];
    }
}
