<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BulkFeeReceipt;
use App\Models\FeeSettlement;
use App\Models\SchoolSession as SchoolSessionModel;
use App\Models\ClassTeacher;
use App\Models\Promotion;
use App\Models\User;
use App\Traits\SchoolSession;
use App\Interfaces\SchoolSessionInterface;
use App\Repositories\FeePaymentRepository;

class YearEndController extends Controller
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
        $session    = SchoolSessionModel::find($session_id);

        $rteBulkSettled = self::isCategoryBulkSettled($session_id, 'rte');
        $cocBulkSettled = self::isCategoryBulkSettled($session_id, 'coc');

        // Remove bulk-settled government categories — they don't need per-student action
        $defaulters = collect(app(FeePaymentRepository::class)->getDefaulters($session_id, 'all'))
            ->filter(function ($d) use ($rteBulkSettled, $cocBulkSettled) {
                if ($rteBulkSettled && $d->fee_category === 'rte') return false;
                if ($cocBulkSettled && $d->fee_category === 'coc') return false;
                return true;
            })
            ->values()
            ->all();

        $settledMap = FeeSettlement::where('session_id', $session_id)
            ->get()
            ->keyBy('student_user_id');

        return view('academics.year-end', compact(
            'defaulters', 'settledMap', 'session',
            'rteBulkSettled', 'cocBulkSettled'
        ));
    }

    public function settle(Request $request, $student_id)
    {
        $request->validate([
            'settlement_type'    => 'required|in:waived,paid_offline,carried_forward,other',
            'remark'             => 'required|string|max:500',
            'outstanding_amount' => 'required|numeric',
        ]);

        $session_id = $this->getSchoolCurrentSession();

        FeeSettlement::updateOrCreate(
            ['student_user_id' => $student_id, 'session_id' => $session_id],
            [
                'outstanding_amount' => $request->outstanding_amount,
                'settlement_type'    => $request->settlement_type,
                'remark'             => $request->remark,
                'settled_by'         => auth()->id(),
            ]
        );

        return back()->with('status', 'Settlement recorded.');
    }

    public function unsettle($student_id)
    {
        $session_id = $this->getSchoolCurrentSession();
        FeeSettlement::where('student_user_id', $student_id)
            ->where('session_id', $session_id)
            ->delete();

        return back()->with('status', 'Settlement removed.');
    }

    // ── Static helpers used by settings page and session creation block ──

    public static function unsettledCount($session_id): int
    {
        $defaulters = app(FeePaymentRepository::class)->getDefaulters($session_id, 'all');
        $settledIds = FeeSettlement::where('session_id', $session_id)
            ->pluck('student_user_id')
            ->toArray();

        $rteSettled = static::isCategoryBulkSettled($session_id, 'rte');
        $cocSettled = static::isCategoryBulkSettled($session_id, 'coc');

        return collect($defaulters)
            ->filter(function ($d) use ($settledIds, $rteSettled, $cocSettled) {
                if (in_array($d->student_id, $settledIds)) return false;
                if ($rteSettled && $d->fee_category === 'rte') return false;
                if ($cocSettled && $d->fee_category === 'coc') return false;
                return true;
            })
            ->count();
    }

    // Returns true when the category's bulk receipts cover the total fees due —
    // using the same calculation as FeeReportController::categoryReceipts().
    public static function isCategoryBulkSettled(int $session_id, string $category): bool
    {
        $feeRepo = app(FeePaymentRepository::class);

        if ($category === 'rte') {
            $totalDue      = collect($feeRepo->getRteWithFees($session_id))->sum('total_due');
            $totalReceived = BulkFeeReceipt::where('session_id', $session_id)
                ->where('fee_category', 'rte')
                ->sum('amount_received');
            return $totalDue > 0 && (float) $totalReceived >= (float) $totalDue;
        }

        if ($category === 'coc') {
            $cocCount = User::join('promotions', 'promotions.student_id', '=', 'users.id')
                ->where('promotions.session_id', $session_id)
                ->where('users.fee_category', 'coc')
                ->where('users.role', 'student')
                ->count();
            $totalDue      = $cocCount * 16200; // mirrors the hardcoded rate in categoryReceipts()
            $totalReceived = BulkFeeReceipt::where('session_id', $session_id)
                ->where('fee_category', 'coc')
                ->sum('amount_received');
            return $totalDue > 0 && (float) $totalReceived >= (float) $totalDue;
        }

        return false;
    }

    public static function checklistStatus($session_id, $latest_session_id): array
    {
        $unsettled = self::unsettledCount($session_id);

        $latestHasClasses    = \App\Models\SchoolClass::where('session_id', $latest_session_id)->exists();
        $latestHasPromotions = Promotion::where('session_id', $latest_session_id)->exists();
        $latestHasTeachers   = ClassTeacher::where('session_id', $latest_session_id)->exists();
        $latestHasRollNos    = Promotion::where('session_id', $latest_session_id)
            ->whereNotNull('roll_number')->exists();

        return [
            'fees_settled'      => $unsettled === 0,
            'unsettled_count'   => $unsettled,
            'classes_cloned'    => $latestHasClasses,
            'students_promoted' => $latestHasPromotions,
            'teachers_assigned' => $latestHasTeachers,
            'roll_nos_assigned' => $latestHasRollNos,
        ];
    }
}
