<?php

namespace App\Http\Controllers;

use App\Models\LessonPlan;
use App\Models\SubjectTeacher;
use App\Models\ClassTeacher;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Traits\SchoolSession;
use App\Interfaces\SchoolSessionInterface;
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

        // CT: view plans for their class (all subjects, all teachers)
        $ctAssignment = ClassTeacher::with(['schoolClass', 'section'])
            ->where('teacher_id', $user->id)
            ->where('session_id', $sessionId)
            ->first();

        $ctPlans = null;
        if ($ctAssignment) {
            $ctPlans = LessonPlan::with(['subject', 'teacher'])
                ->where('session_id', $sessionId)
                ->where('class_id', $ctAssignment->class_id)
                ->where('section_id', $ctAssignment->section_id)
                ->get()
                ->sortBy(fn($p) => [$p->subject->sort_order ?? 0, array_search($p->month, $this->monthOrder())])
                ->groupBy('subject_id');
        }

        return view('lesson-plans.index', compact(
            'subjectAssignments', 'myPlans', 'ctAssignment', 'ctPlans', 'sessionId'
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
            ->firstOrFail();

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

        // Verify the teacher is assigned to this class+subject
        $assigned = SubjectTeacher::where('teacher_id', $user->id)
            ->where('session_id', $sessionId)
            ->where('class_id', $data['class_id'])
            ->where('section_id', $data['section_id'])
            ->where('subject_id', $data['subject_id'])
            ->exists();

        if (!$assigned) {
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
        $plan = LessonPlan::with(['subject', 'schoolClass', 'section'])->findOrFail($id);

        if ($user->role !== 'admin' && $plan->teacher_id !== $user->id) {
            abort(403, 'You can only edit your own Learning Standard entries.');
        }

        return view('lesson-plans.edit', ['plan' => $plan]);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $plan = LessonPlan::findOrFail($id);

        if ($user->role !== 'admin' && $plan->teacher_id !== $user->id) {
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
        $plan = LessonPlan::findOrFail($id);

        if ($user->role !== 'admin' && $plan->teacher_id !== $user->id) {
            abort(403);
        }

        $plan->delete();

        return back()->with('status', 'Entry deleted.');
    }
}
