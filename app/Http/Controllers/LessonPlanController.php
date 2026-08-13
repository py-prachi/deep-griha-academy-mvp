<?php

namespace App\Http\Controllers;

use App\Models\LessonPlan;
use App\Models\SubjectTeacher;
use App\Models\ClassTeacher;
use App\Models\ClassSubject;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Traits\SchoolSession;
use App\Interfaces\SchoolSessionInterface;
use App\Support\RichText;
use Illuminate\Http\Request;

class LessonPlanController extends Controller
{
    use SchoolSession;

    protected $schoolSessionRepository;

    public function __construct(SchoolSessionInterface $schoolSessionRepository)
    {
        $this->schoolSessionRepository = $schoolSessionRepository;
    }

    private function monthOrder(): array
    {
        return array_keys(LessonPlan::MONTHS);
    }

    // Pre-Primary CTs typically teach every subject in their own class
    // themselves, unlike Class 1-8 where each subject has its own teacher —
    // so a Pre-Primary CT gets edit rights across all subjects in her class,
    // not just ones she has an explicit SubjectTeacher assignment for.
    private function isPrePrimaryClassTeacherOf($user, $sessionId, $classId, $sectionId): bool
    {
        $ct = ClassTeacher::with('schoolClass')
            ->where('teacher_id', $user->id)
            ->where('session_id', $sessionId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->first();

        if (!$ct) {
            return false;
        }

        return (bool) PrePrimaryController::getPrePrimaryType(optional($ct->schoolClass)->class_name ?? '');
    }

    public function index()
    {
        $user      = auth()->user();
        $sessionId = $this->getSchoolCurrentSession();

        if ($user->role === 'admin') {
            $classes = SchoolClass::whereHas('sections', fn($q) => $q->where('session_id', $sessionId))
                ->orderBy('id')
                ->get();

            $plans = LessonPlan::with(['subject', 'schoolClass', 'section', 'teacher'])
                ->where('session_id', $sessionId)
                ->get()
                ->sortBy(fn($p) => [
                    $p->class_id,
                    $p->subject->sort_order ?? 0,
                    array_search($p->month, $this->monthOrder()),
                ])
                ->groupBy('class_id');

            return view('lesson-plans.admin-index', compact('plans', 'classes', 'sessionId'));
        }

        // Subject teacher: their own assignments + plans they've entered
        $subjectAssignments = SubjectTeacher::with(['subject', 'schoolClass', 'section'])
            ->where('teacher_id', $user->id)
            ->where('session_id', $sessionId)
            ->get()
            ->sortBy(fn($st) => [$st->class_id, $st->subject->sort_order ?? 0]);

        $myPlans = LessonPlan::where('session_id', $sessionId)
            ->where('teacher_id', $user->id)
            ->get()
            ->sortBy(fn($p) => array_search($p->month, $this->monthOrder()))
            ->groupBy(fn($p) => $p->class_id . '_' . $p->subject_id);

        // CT: view plans for their class(es) — all subjects, all teachers.
        // A teacher may be CT for more than one class (e.g. one teacher
        // covering both Nursery and Lower KG), so show a block per class.
        $ctAssignments = ClassTeacher::with(['schoolClass', 'section'])
            ->where('teacher_id', $user->id)
            ->where('session_id', $sessionId)
            ->get();

        $ctPlansByAssignment    = [];
        $ctIsPrePrimary         = [];
        $ctSubjectsByAssignment = [];
        foreach ($ctAssignments as $ctAssignment) {
            $ctPlansByAssignment[$ctAssignment->id] = LessonPlan::with(['subject', 'teacher'])
                ->where('session_id', $sessionId)
                ->where('class_id', $ctAssignment->class_id)
                ->where('section_id', $ctAssignment->section_id)
                ->get()
                ->sortBy(fn($p) => [$p->subject->sort_order ?? 0, array_search($p->month, $this->monthOrder())])
                ->groupBy('subject_id');

            $isPP = (bool) PrePrimaryController::getPrePrimaryType(optional($ctAssignment->schoolClass)->class_name ?? '');
            $ctIsPrePrimary[$ctAssignment->id] = $isPP;

            if ($isPP) {
                // Pre-Primary CT: she can edit every subject in her class, so
                // list them all (not just ones with existing entries) so she
                // can add entries for subjects that don't have any yet. Skip
                // subjects she already has an explicit SubjectTeacher row for
                // — those are already fully editable in "My Learning Standard"
                // above, so listing them again here would just be a duplicate.
                $alreadyAssignedSubjectIds = $subjectAssignments
                    ->where('class_id', $ctAssignment->class_id)
                    ->pluck('subject_id');

                $ctSubjectsByAssignment[$ctAssignment->id] = ClassSubject::with('subject')
                    ->where('class_id', $ctAssignment->class_id)
                    ->where('session_id', $sessionId)
                    ->get()
                    ->pluck('subject')
                    ->filter()
                    ->reject(fn($subject) => $alreadyAssignedSubjectIds->contains($subject->id))
                    ->sortBy('sort_order')
                    ->values();
            }
        }

        return view('lesson-plans.index', compact(
            'subjectAssignments', 'myPlans', 'ctAssignments', 'ctPlansByAssignment',
            'ctIsPrePrimary', 'ctSubjectsByAssignment', 'sessionId'
        ));
    }

    public function create(Request $request)
    {
        $user      = auth()->user();
        $sessionId = $this->getSchoolCurrentSession();

        $assignment = SubjectTeacher::with(['subject', 'schoolClass', 'section'])
            ->where('teacher_id', $user->id)
            ->where('session_id', $sessionId)
            ->where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->where('subject_id', $request->subject_id)
            ->first();

        if (!$assignment) {
            // Not an explicit subject-teacher assignment — allow it anyway if
            // she's the Pre-Primary CT of this class (she teaches everything).
            if (!$this->isPrePrimaryClassTeacherOf($user, $sessionId, $request->class_id, $request->section_id)) {
                abort(404);
            }

            $classSubject = ClassSubject::with(['subject', 'schoolClass'])
                ->where('class_id', $request->class_id)
                ->where('session_id', $sessionId)
                ->where('subject_id', $request->subject_id)
                ->firstOrFail();

            $assignment = (object) [
                'class_id'    => (int) $request->class_id,
                'section_id'  => (int) $request->section_id,
                'subject_id'  => (int) $request->subject_id,
                'schoolClass' => $classSubject->schoolClass,
                'section'     => \App\Models\Section::find($request->section_id),
                'subject'     => $classSubject->subject,
            ];
        }

        return view('lesson-plans.create', compact('assignment', 'sessionId'));
    }

    public function store(Request $request)
    {
        $user      = auth()->user();
        $sessionId = $this->getSchoolCurrentSession();

        $data = $request->validate([
            'class_id'          => 'required|integer',
            'section_id'        => 'required|integer',
            'subject_id'        => 'required|integer',
            'month'             => 'required|in:' . implode(',', array_keys(LessonPlan::MONTHS)),
            'days_allocated'    => 'required|integer|min:1|max:31',
            'chapter_number'    => 'nullable|string|max:20',
            'chapter_name'      => 'required|string|max:255',
            'learning_standards'=> 'nullable|string',
            'status'            => 'required|in:planned,completed',
        ]);

        $data['learning_standards'] = RichText::sanitize($data['learning_standards'] ?? null);

        // Verify the teacher is assigned to this class+subject — or is the
        // Pre-Primary CT of this class, who teaches every subject herself.
        $assigned = SubjectTeacher::where('teacher_id', $user->id)
            ->where('session_id', $sessionId)
            ->where('class_id', $data['class_id'])
            ->where('section_id', $data['section_id'])
            ->where('subject_id', $data['subject_id'])
            ->exists();

        if (!$assigned && !$this->isPrePrimaryClassTeacherOf($user, $sessionId, $data['class_id'], $data['section_id'])) {
            abort(403, 'You are not assigned to teach this subject for this class.');
        }

        LessonPlan::create(array_merge($data, [
            'teacher_id' => $user->id,
            'session_id' => $sessionId,
        ]));

        return redirect()->route('lesson-plans.index')
            ->with('status', 'Learning Standard entry added successfully.');
    }

    public function edit($id)
    {
        $user = auth()->user();
        $sessionId = $this->getSchoolCurrentSession();
        $plan = LessonPlan::with(['subject', 'schoolClass', 'section'])->findOrFail($id);

        $canEdit = $user->role === 'admin'
            || $plan->teacher_id === $user->id
            || $this->isPrePrimaryClassTeacherOf($user, $sessionId, $plan->class_id, $plan->section_id);

        if (!$canEdit) {
            abort(403, 'You can only edit your own Learning Standard entries.');
        }

        return view('lesson-plans.edit', ['plan' => $plan]);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $sessionId = $this->getSchoolCurrentSession();
        $plan = LessonPlan::findOrFail($id);

        $canEdit = $user->role === 'admin'
            || $plan->teacher_id === $user->id
            || $this->isPrePrimaryClassTeacherOf($user, $sessionId, $plan->class_id, $plan->section_id);

        if (!$canEdit) {
            abort(403);
        }

        $data = $request->validate([
            'month'             => 'required|in:' . implode(',', array_keys(LessonPlan::MONTHS)),
            'days_allocated'    => 'required|integer|min:1|max:31',
            'chapter_number'    => 'nullable|string|max:20',
            'chapter_name'      => 'required|string|max:255',
            'learning_standards'=> 'nullable|string',
            'status'            => 'required|in:planned,completed',
        ]);

        $data['learning_standards'] = RichText::sanitize($data['learning_standards'] ?? null);

        $plan->update($data);

        return redirect()->route('lesson-plans.index')
            ->with('status', 'Learning Standard entry updated.');
    }

    public function printView(Request $request)
    {
        $user      = auth()->user();
        $sessionId = $this->getSchoolCurrentSession();

        // Admin can print any teacher's plans; teacher prints their own
        $teacherId = ($user->role === 'admin' && $request->filled('teacher_id'))
            ? $request->teacher_id
            : $user->id;

        $classId   = $request->class_id;
        $subjectId = $request->subject_id;

        $query = LessonPlan::with(['subject', 'schoolClass', 'section', 'teacher'])
            ->where('session_id', $sessionId)
            ->where('teacher_id', $teacherId);

        if ($classId)   $query->where('class_id',   $classId);
        if ($subjectId) $query->where('subject_id', $subjectId);

        $plans = $query->get()->sortBy(fn($p) => [
            $p->class_id,
            $p->subject_id,
            array_search($p->month, $this->monthOrder()),
        ])->groupBy(fn($p) => $p->class_id . '_' . $p->subject_id);

        $teacher = \App\Models\User::find($teacherId);
        $session = $this->schoolSessionRepository->getLatestSession();

        return view('lesson-plans.print', compact('plans', 'teacher', 'session'));
    }

    public function destroy($id)
    {
        $user = auth()->user();
        $sessionId = $this->getSchoolCurrentSession();
        $plan = LessonPlan::findOrFail($id);

        $canEdit = $user->role === 'admin'
            || $plan->teacher_id === $user->id
            || $this->isPrePrimaryClassTeacherOf($user, $sessionId, $plan->class_id, $plan->section_id);

        if (!$canEdit) {
            abort(403);
        }

        $plan->delete();

        return back()->with('status', 'Entry deleted.');
    }

    public function remark(Request $request, $id)
    {
        $user = auth()->user();
        if ($user->role !== 'admin') {
            abort(403);
        }

        $plan = LessonPlan::findOrFail($id);

        $data = $request->validate([
            'admin_remark' => 'nullable|string|max:2000',
        ]);

        $plan->update($data);

        return back()->with('status', 'Remark saved.');
    }
}
