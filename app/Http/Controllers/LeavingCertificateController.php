<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Interfaces\LeavingCertificateInterface;
use App\Models\Admission;
use App\Models\LeavingCertificate;
use Barryvdh\DomPDF\Facade\Pdf;

class LeavingCertificateController extends Controller
{
    protected LeavingCertificateInterface $lcRepo;

    public function __construct(LeavingCertificateInterface $lcRepo)
    {
        $this->lcRepo = $lcRepo;
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!in_array(auth()->user()->role, ['admin'])) {
                abort(403);
            }
            return $next($request);
        });
    }

    // ── Index ──────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'from_date', 'to_date']);
        $lcs     = $this->lcRepo->getAll($filters);

        return view('lc.index', compact('lcs', 'filters'));
    }

    // ── Create ─────────────────────────────────────────────────────────────────

    public function create(Request $request)
    {
        $admission = null;
        $feeCheck  = ['has_due' => false, 'amount' => 0];

        if ($request->filled('admission_id')) {
            $admission = Admission::with(['schoolClass', 'section', 'session'])
                ->where('status', Admission::STATUS_CONFIRMED)
                ->find($request->admission_id);

            if ($admission) {
                $feeCheck = $this->lcRepo->checkFeesDue($admission->id);
            }
        }

        // Only confirmed admissions can get an LC
        $admissions = Admission::with(['schoolClass', 'section'])
            ->where('status', Admission::STATUS_CONFIRMED)
            ->orderBy('student_name')
            ->get();

        $nextLcNumber = LeavingCertificate::generateLcNumber();

        return view('lc.create', compact('admission', 'admissions', 'feeCheck', 'nextLcNumber'));
    }

    // ── Store ──────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'admission_id'         => 'required|exists:admissions,id',
            'issue_date'           => 'required|date',
            'issue_place'          => 'nullable|string|max:100',
            'pupil_name'           => 'nullable|string|max:200',
            'mother_name'          => 'nullable|string|max:200',
            'race_and_caste'       => 'nullable|string|max:100',
            'nationality'          => 'nullable|string|max:100',
            'place_of_birth'       => 'nullable|string|max:150',
            'date_of_birth'        => 'nullable|date',
            'last_school_attended' => 'nullable|string|max:255',
            'date_of_admission'    => 'nullable|date',
            'progress'             => 'nullable|string|max:150',
            'conduct'              => 'required|string|max:100',
            'date_of_leaving'      => 'required|date',
            'standard_studying'    => 'nullable|string|max:100',
            'studying_since'       => 'nullable|date',
            'reason_for_leaving'   => 'nullable|string|max:255',
            'remarks'              => 'nullable|string|max:500',
        ]);

        // Guard: one LC per admission
        $existing = $this->lcRepo->findByAdmission($validated['admission_id'])->first();
        if ($existing) {
            return back()
                ->withInput()
                ->withErrors(['admission_id' => 'A Leaving Certificate (' . $existing->lc_number . ') already exists for this student.']);
        }

        // Fee snapshot
        $feeCheck = $this->lcRepo->checkFeesDue($validated['admission_id']);

        $validated['lc_number']    = LeavingCertificate::generateLcNumber();
        $validated['fees_cleared'] = !$feeCheck['has_due'];
        $validated['fees_due']     = $feeCheck['amount'];
        $validated['issued_by']    = auth()->id();

        $lc = $this->lcRepo->create($validated);

        return redirect()
            ->route('lc.show', $lc->id)
            ->with('success', 'Leaving Certificate ' . $lc->lc_number . ' issued successfully.');
    }

    // ── Show ───────────────────────────────────────────────────────────────────

    public function show(int $id)
    {
        $lc = $this->lcRepo->findById($id);
        return view('lc.show', compact('lc'));
    }

    // ── PDF ────────────────────────────────────────────────────────────────────

    public function pdf(int $id)
    {
        $lc = $this->lcRepo->findById($id);

        $pdf = Pdf::loadView('lc.pdf', compact('lc'))
            ->setPaper('a4', 'portrait');

        $filename = 'LC-' . $lc->lc_number . '-' . str_replace(' ', '_', $lc->pupil_name ?? $lc->admission_id) . '.pdf';

        return $pdf->download($filename);
    }

    // ── AJAX: admission info for create form ──────────────────────────────────

    public function studentInfo(Request $request)
    {
        $request->validate(['admission_id' => 'required|exists:admissions,id']);

        $admission = Admission::with(['schoolClass', 'section'])
            ->findOrFail($request->admission_id);
        $admission->load('session');

        $feeCheck = $this->lcRepo->checkFeesDue($admission->id);
        $existing = $this->lcRepo->findByAdmission($admission->id)->first();

        $classLabel = ($admission->schoolClass->class_name ?? '') .
                      ($admission->section ? ' - ' . $admission->section->section_name : '');

        return response()->json([
            'admission' => [
                'id'                   => $admission->id,
                'student_name'         => $admission->student_name,
                'dga_admission_no'     => $admission->dga_admission_no,
                'general_id'           => $admission->general_id,
                'mother_name'          => $admission->mother_name ?? '',
                'father_name'          => $admission->father_name ?? '',
                'date_of_birth'        => $admission->date_of_birth ? $admission->date_of_birth->format('Y-m-d') : null,
                'place_of_birth'       => $admission->place_of_birth ?? '',
                'nationality'          => $admission->nationality ?? 'Indian',
                'caste'                => $admission->caste ?? '',
                'class_label'          => $classLabel,
                'previous_school'      => $admission->previous_school ?? '',
                'confirmed_date'       => $admission->confirmed_date ? $admission->confirmed_date->format('Y-m-d') : null,
                'session_name'         => $admission->session ? $admission->session->session_name : null,
            ],
            'fee_check' => $feeCheck,
            'has_lc'    => (bool) $existing,
            'lc_number' => $existing ? $existing->lc_number : null,
        ]);
    }
}
