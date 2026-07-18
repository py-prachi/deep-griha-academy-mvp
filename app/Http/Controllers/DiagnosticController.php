<?php

namespace App\Http\Controllers;

use App\Models\DiagnosticResult;
use App\Models\SubjectTeacher;
use App\Models\ClassTeacher;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Repositories\PromotionRepository;
use App\Traits\SchoolSession;
use App\Interfaces\SchoolSessionInterface;
use Illuminate\Http\Request;

class DiagnosticController extends Controller
{
    use SchoolSession;

    protected $schoolSessionRepository;

    public function __construct(SchoolSessionInterface $schoolSessionRepository)
    {
        $this->schoolSessionRepository = $schoolSessionRepository;
    }

    public function index()
    {
        $user      = auth()->user();
        $sessionId = $this->getSchoolCurrentSession();

        if ($user->role === 'admin') {
            $classes = SchoolClass::whereHas('sections', fn($q) => $q->where('session_id', $sessionId))
                ->with(['sections' => fn($q) => $q->where('session_id', $sessionId)])
                ->orderBy('id')
                ->get();

            return view('diagnostics.admin-index', compact('classes', 'sessionId'));
        }

        // Subject teacher: their assignments + completion counts
        $subjectAssignments = SubjectTeacher::with(['subject', 'schoolClass', 'section'])
            ->where('teacher_id', $user->id)
            ->where('session_id', $sessionId)
            ->get()
            ->sortBy(fn($st) => [$st->class_id, $st->subject->sort_order ?? 0]);

        // CT: all subjects for their class
        $ctAssignment = ClassTeacher::with(['schoolClass', 'section'])
            ->where('teacher_id', $user->id)
            ->where('session_id', $sessionId)
            ->first();

        return view('diagnostics.index', compact('subjectAssignments', 'ctAssignment', 'sessionId'));
    }

    public function entry(Request $request)
    {
        $user      = auth()->user();
        $sessionId = $this->getSchoolCurrentSession();

        $classId        = $request->class_id;
        $sectionId      = $request->section_id;
        $subjectId      = $request->subject_id;
        $assessmentType = $request->assessment_type;

        if (!$classId || !$sectionId || !$subjectId || !$assessmentType) {
            abort(400, 'Missing required parameters.');
        }

        if (!array_key_exists($assessmentType, DiagnosticResult::ASSESSMENT_TYPES)) {
            abort(400, 'Invalid assessment type.');
        }

        // Verify teacher is assigned to this subject (skip for admin)
        if ($user->role !== 'admin') {
            $assigned = SubjectTeacher::where('teacher_id', $user->id)
                ->where('session_id', $sessionId)
                ->where('class_id', $classId)
                ->where('section_id', $sectionId)
                ->where('subject_id', $subjectId)
                ->exists();

            if (!$assigned) {
                abort(403, 'You are not assigned to teach this subject for this class.');
            }
        }

        $promotionRepo = new PromotionRepository();
        $promotions    = $promotionRepo->getAllActive($sessionId, $classId, $sectionId)
            ->sortBy('roll_number')
            ->values();

        $subject  = Subject::findOrFail($subjectId);
        $class    = SchoolClass::findOrFail($classId);
        $section  = Section::findOrFail($sectionId);

        // Existing results keyed by student_id
        $existing = DiagnosticResult::where('session_id', $sessionId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->where('subject_id', $subjectId)
            ->where('assessment_type', $assessmentType)
            ->get()
            ->keyBy('student_id');

        return view('diagnostics.entry', compact(
            'promotions', 'subject', 'class', 'section',
            'assessmentType', 'existing', 'sessionId'
        ));
    }

    public function save(Request $request)
    {
        $user      = auth()->user();
        $sessionId = $this->getSchoolCurrentSession();

        $classId        = $request->class_id;
        $sectionId      = $request->section_id;
        $subjectId      = $request->subject_id;
        $assessmentType = $request->assessment_type;

        $request->validate([
            'class_id'        => 'required|integer',
            'section_id'      => 'required|integer',
            'subject_id'      => 'required|integer',
            'assessment_type' => 'required|in:' . implode(',', array_keys(DiagnosticResult::ASSESSMENT_TYPES)),
            'results'         => 'nullable|array',
        ]);

        if ($user->role !== 'admin') {
            $assigned = SubjectTeacher::where('teacher_id', $user->id)
                ->where('session_id', $sessionId)
                ->where('class_id', $classId)
                ->where('section_id', $sectionId)
                ->where('subject_id', $subjectId)
                ->exists();

            if (!$assigned) abort(403);
        }

        $results = $request->input('results', []);

        foreach ($results as $studentId => $data) {
            $marks = trim($data['marks_obtained'] ?? '');
            $needs = trim($data['needs'] ?? '');
            $notes = trim($data['notes'] ?? '');

            // Skip completely blank rows
            if ($marks === '' && $needs === '' && $notes === '') {
                DiagnosticResult::where([
                    'session_id'      => $sessionId,
                    'class_id'        => $classId,
                    'section_id'      => $sectionId,
                    'subject_id'      => $subjectId,
                    'student_id'      => $studentId,
                    'assessment_type' => $assessmentType,
                ])->delete();
                continue;
            }

            DiagnosticResult::updateOrCreate(
                [
                    'session_id'      => $sessionId,
                    'class_id'        => $classId,
                    'section_id'      => $sectionId,
                    'subject_id'      => $subjectId,
                    'student_id'      => $studentId,
                    'assessment_type' => $assessmentType,
                ],
                [
                    'teacher_id'      => $user->id,
                    'marks_obtained'  => $marks ?: null,
                    'needs'           => $needs ?: null,
                    'notes'           => $notes ?: null,
                ]
            );
        }

        return redirect()->route('diagnostics.index')
            ->with('status', 'Results saved for ' . DiagnosticResult::ASSESSMENT_TYPES[$assessmentType] . '.');
    }

    public function view(Request $request)
    {
        $user      = auth()->user();
        $sessionId = $this->getSchoolCurrentSession();

        $classId        = $request->class_id;
        $sectionId      = $request->section_id;
        $subjectId      = $request->subject_id;
        $assessmentType = $request->assessment_type;

        if (!$classId || !$sectionId || !$subjectId || !$assessmentType) {
            abort(400);
        }

        // CT can view their own class only
        if ($user->role === 'teacher') {
            $ctAssignment = ClassTeacher::where('teacher_id', $user->id)
                ->where('session_id', $sessionId)
                ->where('class_id', $classId)
                ->where('section_id', $sectionId)
                ->first();

            // Or they are the subject teacher
            $isSubjectTeacher = SubjectTeacher::where('teacher_id', $user->id)
                ->where('session_id', $sessionId)
                ->where('class_id', $classId)
                ->where('section_id', $sectionId)
                ->where('subject_id', $subjectId)
                ->exists();

            if (!$ctAssignment && !$isSubjectTeacher) {
                abort(403);
            }
        }

        $promotionRepo = new PromotionRepository();
        $promotions    = $promotionRepo->getAllActive($sessionId, $classId, $sectionId)
            ->sortBy('roll_number')
            ->values();

        $subject  = Subject::findOrFail($subjectId);
        $class    = SchoolClass::findOrFail($classId);
        $section  = Section::findOrFail($sectionId);

        $results = DiagnosticResult::where('session_id', $sessionId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->where('subject_id', $subjectId)
            ->where('assessment_type', $assessmentType)
            ->get()
            ->keyBy('student_id');

        // All subjects for this class (for the subject switcher tabs)
        $subjects = Subject::whereHas('subjectTeachers', fn($q) =>
            $q->where('session_id', $sessionId)
              ->where('class_id', $classId)
              ->where('section_id', $sectionId)
        )->orderBy('sort_order')->get();

        return view('diagnostics.view', compact(
            'promotions', 'subject', 'subjects', 'class', 'section',
            'assessmentType', 'results', 'sessionId'
        ));
    }
}
