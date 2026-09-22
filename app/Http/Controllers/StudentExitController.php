<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Interfaces\StudentExitInterface;
use App\Models\Admission;
use App\Models\LeavingCertificate;
use App\Models\StudentExit;

class StudentExitController extends Controller
{
    protected $exitRepo;

    public function __construct(StudentExitInterface $exitRepo)
    {
        $this->exitRepo = $exitRepo;
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->hasRole('admin')) {
                abort(403);
            }
            return $next($request);
        });
    }

    /**
     * List all exited students, split into Genuine / Correction / Needs Review tabs.
     */
    public function index(Request $request)
    {
        $allExits = $this->exitRepo->getAll();

        $counts = [
            'genuine'       => $allExits->filter(fn($e) => $e->exit_type === StudentExit::TYPE_GENUINE)->count(),
            'correction'    => $allExits->filter(fn($e) => $e->exit_type === StudentExit::TYPE_CORRECTION)->count(),
            'uncategorized' => $allExits->filter(fn($e) => is_null($e->exit_type))->count(),
        ];

        $tab = $request->get('tab', 'genuine');
        if (!in_array($tab, ['genuine', 'correction', 'uncategorized'])) {
            $tab = 'genuine';
        }

        if ($tab === 'correction') {
            $exits = $allExits->filter(fn($e) => $e->exit_type === StudentExit::TYPE_CORRECTION)->values();
        } elseif ($tab === 'uncategorized') {
            $exits = $allExits->filter(fn($e) => is_null($e->exit_type))->values();
        } else {
            $exits = $allExits->filter(fn($e) => $e->exit_type === StudentExit::TYPE_GENUINE)->values();
        }

        return view('exits.index', compact('exits', 'counts', 'tab'));
    }

    /**
     * Bulk-categorize previously uncategorized exit records from the
     * "Needs Review" tab. Each row's checkbox defaults to checked (genuine),
     * matching historical reality — the correction workflow is new, so old
     * exit records are overwhelmingly genuine departures.
     */
    public function categorizeBulk(Request $request)
    {
        $ids        = $request->input('exit_ids', []);
        $genuineIds = array_map('intval', $request->input('genuine', []));

        $updated = 0;
        foreach ($ids as $id) {
            $type = in_array((int) $id, $genuineIds, true) ? StudentExit::TYPE_GENUINE : StudentExit::TYPE_CORRECTION;
            $this->exitRepo->update($id, ['exit_type' => $type]);
            $updated++;
        }

        return redirect()->route('exits.index', ['tab' => 'uncategorized'])
            ->with('status', $updated . ' exit record(s) categorized.');
    }

    /**
     * Categorize (or re-categorize) a single exit record from its detail page.
     */
    public function categorize(Request $request, $id)
    {
        $type = $request->boolean('is_genuine') ? StudentExit::TYPE_GENUINE : StudentExit::TYPE_CORRECTION;
        $this->exitRepo->update($id, ['exit_type' => $type]);

        return redirect()->route('exits.show', $id)
            ->with('status', 'Exit record categorized as ' . ($type === StudentExit::TYPE_GENUINE ? 'a genuine exit.' : 'a record correction.'));
    }

    /**
     * Show exit form for a confirmed student.
     * GET /exits/create?admission_id=X
     */
    public function create(Request $request)
    {
        $admissionId = $request->get('admission_id');
        $admission   = null;

        if ($admissionId) {
            $admission = Admission::with(['schoolClass', 'section'])
                ->where('status', Admission::STATUS_CONFIRMED)
                ->findOrFail($admissionId);

            // Block if already exited
            if ($this->exitRepo->getByAdmissionId($admissionId)) {
                return redirect()->route('exits.index')
                    ->with('error', 'This student already has an exit record.');
            }
        }

        // For AJAX student lookup (same as LC create pattern)
        $students = Admission::confirmed()
            ->with('schoolClass')
            ->orderBy('student_name')
            ->get();

        return view('exits.create', compact('admission', 'students'));
    }

    /**
     * Store exit form + mark admission as exited.
     */
    public function store(Request $request)
    {
        $request->validate([
            'admission_id'      => 'required|exists:admissions,id',
            'exit_date'         => 'required|date',
            'rating'            => 'nullable|integer|min:1|max:5',
            'form_submitted_at' => 'nullable|date',
        ]);

        $admission = Admission::where('status', Admission::STATUS_CONFIRMED)
            ->findOrFail($request->admission_id);

        // Block duplicate
        if ($this->exitRepo->getByAdmissionId($admission->id)) {
            return back()->with('error', 'This student already has an exit record.');
        }

        // Genuine = student actually left (LC expected, counts as attrition).
        // Correction = exit used to remove/undo a confirmed admission created
        // in error — no LC needed. Checkbox defaults checked (genuine) on the form.
        $exitType = $request->boolean('is_genuine') ? StudentExit::TYPE_GENUINE : StudentExit::TYPE_CORRECTION;

        // Store exit form
        $exit = $this->exitRepo->store([
            'admission_id'      => $admission->id,
            'exit_type'         => $exitType,
            'exit_date'         => $request->exit_date,
            'reason_for_leaving'=> $request->reason_for_leaving,
            'liked_most'        => $request->liked_most,
            'liked_least'       => $request->liked_least,
            'suggestions'       => $request->suggestions,
            'rating'            => $request->rating,
            'parent_name'       => $request->parent_name,
            'parent_contact'    => $request->parent_contact,
            'staff_name'        => $request->staff_name,
            'form_submitted_at' => $request->form_submitted_at,
        ]);

        // Mark admission as exited
        $this->exitRepo->markAdmissionExited($admission, $request->exit_date);

        $message = $exitType === StudentExit::TYPE_GENUINE
            ? $admission->student_name . ' has been marked as exited. Please issue a Leaving Certificate if required.'
            : $admission->student_name . '\'s admission has been removed as a record correction. No Leaving Certificate is needed.';

        return redirect()->route('exits.show', $exit->id)->with('status', $message);
    }

    /**
     * Show exit record details.
     */
    public function show($id)
    {
        $exit      = $this->exitRepo->getById($id);
        $admission = $exit->admission;
        $lc        = null;
        if ($admission) {
            $lc = LeavingCertificate::where('admission_id', $admission->id)->first();
        }
        return view('exits.show', compact('exit', 'lc'));
    }

    /**
     * AJAX: return student info for the exit form (same pattern as LC).
     */
    public function studentInfo(Request $request)
    {
        $admission = Admission::with(['schoolClass', 'section'])
            ->where('status', Admission::STATUS_CONFIRMED)
            ->find($request->admission_id);

        if (!$admission) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        if ($this->exitRepo->getByAdmissionId($admission->id)) {
            return response()->json(['error' => 'This student already has an exit record.'], 422);
        }

        return response()->json([
            'student_name'  => $admission->student_name,
            'class_name'    => $admission->schoolClass ? $admission->schoolClass->class_name : '—',
            'section_name'  => $admission->section ? $admission->section->section_name : '—',
            'dob'           => $admission->date_of_birth ? $admission->date_of_birth->format('d/m/Y') : '—',
            'father_name'   => $admission->father_name ?? '—',
            'mother_name'   => $admission->mother_name ?? '—',
            'contact_mobile'=> $admission->father_phone ?? $admission->contact_mobile ?? $admission->mother_phone ?? '—',
            'confirmed_date'=> $admission->confirmed_date ? $admission->confirmed_date->format('d/m/Y') : '—',
        ]);
    }
}
