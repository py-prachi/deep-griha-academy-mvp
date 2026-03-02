<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Interfaces\AdmissionInterface;
use App\Interfaces\SchoolClassInterface;
use App\Interfaces\SectionInterface;
use App\Interfaces\SchoolSessionInterface;
use App\Models\Admission;

class AdmissionController extends Controller
{
    use SchoolSession;

    protected $admissionRepository;
    protected $schoolClassRepository;
    protected $schoolSectionRepository;
    protected $schoolSessionRepository;

    public function __construct(
        AdmissionInterface      $admissionRepository,
        SchoolClassInterface    $schoolClassRepository,
        SectionInterface        $schoolSectionRepository,
        SchoolSessionInterface  $schoolSessionRepository
    ) {
        // Only admin can access admissions
        $this->middleware(function ($request, $next) {
            if (auth()->user()->role !== 'admin') {
                abort(403);
            }
            return $next($request);
        });

        $this->admissionRepository     = $admissionRepository;
        $this->schoolClassRepository   = $schoolClassRepository;
        $this->schoolSectionRepository = $schoolSectionRepository;
        $this->schoolSessionRepository = $schoolSessionRepository;
    }

    // ── LIST ALL ADMISSIONS ───────────────────────────────────────────────
    public function index(Request $request)
    {
        $filters = [
            'status'       => $request->query('status'),
            'class_id'     => $request->query('class_id'),
            'academic_year'=> $request->query('academic_year'),
            'search'       => $request->query('search'),
        ];

        $admissions    = $this->admissionRepository->getAll($filters);
        $current_session_id = $this->getSchoolCurrentSession();
        $school_classes = $this->schoolClassRepository->getAllBySession($current_session_id);

        return view('admissions.index', [
            'admissions'     => $admissions,
            'school_classes' => $school_classes,
            'filters'        => $filters,
            'statuses'       => [
                'inquiry'   => 'Inquiry',
                'pending'   => 'Pending',
                'confirmed' => 'Confirmed',
            ],
        ]);
    }

    // ── SHOW NEW ADMISSION FORM ───────────────────────────────────────────
    public function create()
    {
        $current_session_id = $this->getSchoolCurrentSession();
        $school_classes     = $this->schoolClassRepository->getAllBySession($current_session_id);
        $sessions           = $this->schoolSessionRepository->getAll();

        return view('admissions.create', [
            'school_classes' => $school_classes,
            'sessions'       => $sessions,
            'current_session_id' => $current_session_id,
            'academic_year'  => '2025-2026',
        ]);
    }

    // ── STORE NEW ADMISSION ───────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'student_name'   => 'required|string|max:255',
            'contact_mobile' => 'required|string|max:15',
            'academic_year'  => 'required|string',
            'class_id'       => 'required|exists:school_classes,id',
        ]);

        try {
            $this->admissionRepository->create($request->all());
            return redirect()->route('admissions.index')
                             ->with('status', 'Admission inquiry created successfully!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage())->withInput();
        }
    }

    // ── SHOW ADMISSION DETAIL ─────────────────────────────────────────────
    public function show($id)
    {
        $admission      = $this->admissionRepository->findById($id);
        $current_session_id = $this->getSchoolCurrentSession();
        $school_classes = $this->schoolClassRepository->getAllBySession($current_session_id);
        $sections       = $admission->class_id
                            ? $this->schoolSectionRepository->getAllByClassSession($admission->class_id, $current_session_id)
                            : collect();

        return view('admissions.show', [
            'admission'      => $admission,
            'school_classes' => $school_classes,
            'sections'       => $sections,
            'doc_labels'     => \App\Models\AdmissionDocument::typeLabels(),
        ]);
    }

    // ── SHOW EDIT FORM ────────────────────────────────────────────────────
    public function edit($id)
    {
        $admission      = $this->admissionRepository->findById($id);
        $current_session_id = $this->getSchoolCurrentSession();
        $school_classes = $this->schoolClassRepository->getAllBySession($current_session_id);
        $sections       = $admission->class_id
                            ? $this->schoolSectionRepository->getAllByClassSession($admission->class_id, $current_session_id)
                            : collect();

        return view('admissions.edit', [
            'admission'      => $admission,
            'school_classes' => $school_classes,
            'sections'       => $sections,
        ]);
    }

    // ── UPDATE ADMISSION ──────────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        try {
            $this->admissionRepository->update($id, $request->all());
            return redirect()->route('admissions.show', $id)
                             ->with('status', 'Admission updated successfully!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage())->withInput();
        }
    }

    // ── CHANGE STATUS TO PENDING ──────────────────────────────────────────
    public function markPending($id)
    {
        try {
            $this->admissionRepository->updateStatus($id, Admission::STATUS_PENDING);
            return redirect()->route('admissions.show', $id)
                             ->with('status', 'Admission marked as Pending.');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    // ── CONFIRM ADMISSION ─────────────────────────────────────────────────
    public function confirm(Request $request, $id)
    {
        $request->validate([
            'fee_category' => 'required|in:general,rte,coc,discount',
            'section_id'   => 'required|exists:sections,id',
        ]);

        try {
            $this->admissionRepository->confirm($id, $request->all());
            return redirect()->route('admissions.show', $id)
                             ->with('status', 'Admission confirmed! Student account created.');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    // ── CANCEL ADMISSION ──────────────────────────────────────────────────
    public function cancel(Request $request, $id)
    {
        $request->validate([
            'cancel_reason' => 'required|string|min:5',
        ]);

        try {
            $this->admissionRepository->cancel($id, $request->cancel_reason);
            return redirect()->route('admissions.index')
                             ->with('status', 'Admission cancelled and archived.');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    // ── UPDATE DOCUMENT STATUS ────────────────────────────────────────────
    public function updateDocument(Request $request, $admission_id, $document_id)
    {
        try {
            \App\Models\AdmissionDocument::findOrFail($document_id)->update([
                'status'        => $request->status,
                'received_date' => $request->status === 'received' ? now()->toDateString() : null,
            ]);
            return back()->with('status', 'Document status updated.');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    // ── VIEW CANCELLED ADMISSIONS ─────────────────────────────────────────
    public function cancelled()
    {
        $admissions = $this->admissionRepository->getCancelled();
        return view('admissions.cancelled', [
            'admissions' => $admissions,
        ]);
    }
}
