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
        $totalDue  = DB::table('fee_payments')->where('admission_id', $admissionId)->sum('amount_due');
        $totalPaid = DB::table('fee_payments')->where('admission_id', $admissionId)->sum('amount_paid');

        $balance = max(0, (float) $totalDue - (float) $totalPaid);

        return [
            'has_due' => $balance > 0,
            'amount'  => $balance,
        ];
    }
}
