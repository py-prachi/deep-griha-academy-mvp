<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Interfaces\StudentExitInterface;
use App\Models\Admission;
use App\Models\LeavingCertificate;

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
     * List all exited students.
     */
    public function index()
    {
        $exits = $this->exitRepo->getAll();
        return view('exits.index', compact('exits'));
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

        // Store exit form
        $exit = $this->exitRepo->store([
            'admission_id'      => $admission->id,
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

        return redirect()->route('exits.show', $exit->id)
            ->with('success', $admission->student_name . ' has been marked as exited. Please issue a Leaving Certificate if required.');
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
