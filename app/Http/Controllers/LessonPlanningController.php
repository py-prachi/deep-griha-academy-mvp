<?php

namespace App\Http\Controllers;

use App\Models\PlanModule;
use App\Models\PlanLesson;
use App\Models\SubjectTeacher;
use App\Models\ClassTeacher;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Traits\SchoolSession;
use App\Interfaces\SchoolSessionInterface;
use App\Support\RichText;
use Illuminate\Http\Request;

class LessonPlanningController extends Controller
{
    use SchoolSession;

    protected $schoolSessionRepository;

    public function __construct(SchoolSessionInterface $schoolSessionRepository)
    {
        $this->schoolSessionRepository = $schoolSessionRepository;
    }

    // The rich-text fields submit sanitized-on-the-client HTML, but the
    // client can be bypassed — sanitize again here before it ever reaches
    // the database.
    private function sanitizeRichFields(array $data, array $fields): array
    {
        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = RichText::sanitize($data[$field]);
            }
        }
        return $data;
    }

    // Pre-primary Lesson & Module Planning is off-limits for a class's own CT —
    // her own subjects are already covered by Pre-School Daily Plans, so
    // exposing them here too would just duplicate that flow. Anyone else with
    // a SubjectTeacher assignment for that class+section (PE, Agriculture, or
    // any future specialist subject) is a dedicated subject teacher instead,
    // and gets full access — no per-subject whitelist to maintain.
    private function isPrePrimaryOwnCT($teacherId, $sessionId, $classId, $sectionId): bool
    {
        return ClassTeacher::where('teacher_id', $teacherId)
            ->where('session_id', $sessionId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->exists();
    }

    // True if this class+section is pre-primary AND the given teacher is its
    // own CT — i.e. planning here is blocked, she should use Daily Plans.
    private function blocksPrePrimaryPlanning($className, $teacherId, $sessionId, $classId, $sectionId): bool
    {
        return PrePrimaryController::getPrePrimaryType($className ?? '')
            && $this->isPrePrimaryOwnCT($teacherId, $sessionId, $classId, $sectionId);
    }

    public function index()
    {
        $user      = auth()->user();
        $sessionId = $this->getSchoolCurrentSession();

        if ($user->role === 'admin') {
            // Lesson Planning is Class 1-8 for every subject; pre-primary
            // (Nursery/LKG/UKG) classes are included too but only ever have
            // entries from a dedicated (non-CT) subject teacher — e.g. PE or
            // Agriculture — since every other pre-primary subject is planned
            // by the CT herself via Pre-School Daily Plans instead.
            $classes = SchoolClass::whereHas('sections', function ($q) use ($sessionId) {
                $q->where('session_id', $sessionId);
            })->orderBy('id')
              ->get();

            $subjects = Subject::orderBy('sort_order')->get();

            $selClassId   = request('class_id');
            $selSectionId = request('section_id');
            $selSubjectId = request('subject_id');

            $modules = collect();
            $lessons = collect();

            if ($selClassId && $selSectionId && $selSubjectId) {
                $modules = PlanModule::with(['teacher'])
                    ->where('session_id', $sessionId)
                    ->where('class_id', $selClassId)
                    ->where('section_id', $selSectionId)
                    ->where('subject_id', $selSubjectId)
                    ->orderByDesc('date_written')
                    ->get();

                $lessons = PlanLesson::with(['teacher', 'module'])
                    ->where('session_id', $sessionId)
                    ->where('class_id', $selClassId)
                    ->where('section_id', $selSectionId)
                    ->where('subject_id', $selSubjectId)
                    ->orderByDesc('date_written')
                    ->get();
            }

            return view('lesson-planning.admin-index', compact(
                'classes', 'subjects', 'modules', 'lessons',
                'selClassId', 'selSectionId', 'selSubjectId', 'sessionId'
            ));
        }

        // Teacher view — Class 1-8, every subject, plus any pre-primary
        // assignment where this teacher isn't that class's own CT (i.e. a
        // dedicated specialist subject teacher — PE, Agriculture, or any
        // future subject — rather than the CT covering it via Daily Plans).
        $subjectAssignments = SubjectTeacher::with(['subject', 'schoolClass', 'section'])
            ->where('teacher_id', $user->id)
            ->where('session_id', $sessionId)
            ->where(function ($q) {
                $q->whereHas('schoolClass', function ($sub) {
                    $sub->whereRaw('LOWER(class_name) NOT LIKE ?', ['%nursery%'])
                        ->whereRaw('LOWER(class_name) NOT LIKE ?', ['%kg%']);
                })->orWhereNotExists(function ($sub) {
                    $sub->from('class_teachers')
                        ->whereColumn('class_teachers.teacher_id', 'subject_teachers.teacher_id')
                        ->whereColumn('class_teachers.class_id', 'subject_teachers.class_id')
                        ->whereColumn('class_teachers.section_id', 'subject_teachers.section_id')
                        ->whereColumn('class_teachers.session_id', 'subject_teachers.session_id');
                });
            })
            ->get()
            ->sortBy(function ($st) {
                return [$st->class_id, $st->subject->sort_order ?? 0];
            });

        // Load all modules and lessons for this teacher in one query, grouped
        $myModules = PlanModule::where('session_id', $sessionId)
            ->where('teacher_id', $user->id)
            ->orderByDesc('date_written')
            ->get()
            ->groupBy(function ($m) {
                return $m->class_id . '_' . $m->section_id . '_' . $m->subject_id;
            });

        $myLessons = PlanLesson::with(['module'])
            ->where('session_id', $sessionId)
            ->where('teacher_id', $user->id)
            ->orderByDesc('date_written')
            ->get()
            ->groupBy(function ($l) {
                return $l->class_id . '_' . $l->section_id . '_' . $l->subject_id;
            });

        // CT view (read-only) — Class 1-8 and pre-primary alike. Pre-primary CTs
        // don't plan most subjects here (that's Pre-School Daily Plans), but they
        // still need to see what the PE and Agriculture subject-teachers have
        // planned for their class. A teacher may be CT for more than one class,
        // so don't assume just one.
        $ctAssignments = ClassTeacher::with(['schoolClass', 'section'])
            ->where('teacher_id', $user->id)
            ->where('session_id', $sessionId)
            ->get();

        $ctLessonsByAssignment = [];
        foreach ($ctAssignments as $ctAssignment) {
            $ctLessonsByAssignment[$ctAssignment->id] = PlanLesson::with(['subject', 'teacher', 'module'])
                ->where('session_id', $sessionId)
                ->where('class_id', $ctAssignment->class_id)
                ->where('section_id', $ctAssignment->section_id)
                ->orderByDesc('date_written')
                ->get()
                ->groupBy('subject_id');
        }

        return view('lesson-planning.index', compact(
            'subjectAssignments', 'myModules', 'myLessons',
            'ctAssignments', 'ctLessonsByAssignment', 'sessionId'
        ));
    }

    public function createModule(Request $request)
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

        if ($this->blocksPrePrimaryPlanning(optional($assignment->schoolClass)->class_name, $user->id, $sessionId, $assignment->class_id, $assignment->section_id)) {
            abort(404, 'Preschool classes are planned via Pre-School Daily Plans, not Lesson Planning.');
        }

        return view('lesson-planning.module-create', compact('assignment', 'sessionId'));
    }

    public function storeModule(Request $request)
    {
        $user      = auth()->user();
        $sessionId = $this->getSchoolCurrentSession();

        $data = $request->validate([
            'class_id'        => 'required|integer',
            'section_id'      => 'required|integer',
            'subject_id'      => 'required|integer',
            'date_written'    => 'nullable|date',
            'topic'           => 'required|string',
            'learning_outcome'=> 'nullable|string',
            'assessment'      => 'nullable|string',
            'rubric'          => 'nullable|string',
            'objectives'      => 'nullable|string',
            'duration_and_flow' => 'nullable|string',
            'materials'         => 'nullable|string',
        ]);

        $data = $this->sanitizeRichFields($data, [
            'topic', 'learning_outcome', 'assessment', 'rubric', 'objectives', 'duration_and_flow', 'materials',
        ]);

        $assigned = SubjectTeacher::where('teacher_id', $user->id)
            ->where('session_id', $sessionId)
            ->where('class_id', $data['class_id'])
            ->where('section_id', $data['section_id'])
            ->where('subject_id', $data['subject_id'])
            ->exists();

        if (!$assigned) {
            abort(403, 'You are not assigned to teach this subject for this class.');
        }

        $moduleClass = SchoolClass::find($data['class_id']);
        if ($this->blocksPrePrimaryPlanning(optional($moduleClass)->class_name, $user->id, $sessionId, $data['class_id'], $data['section_id'])) {
            abort(404, 'Preschool classes are planned via Pre-School Daily Plans, not Lesson Planning.');
        }

        PlanModule::create(array_merge($data, [
            'teacher_id' => $user->id,
            'session_id' => $sessionId,
        ]));

        return redirect()->route('lesson-planning.index')
            ->with('status', 'Module created successfully.');
    }

    public function editModule($id)
    {
        $user   = auth()->user();
        $module = PlanModule::with(['subject', 'schoolClass', 'section'])->findOrFail($id);

        if ($user->role !== 'admin' && $module->teacher_id !== $user->id) {
            abort(403, 'You can only edit your own modules.');
        }

        return view('lesson-planning.module-edit', compact('module'));
    }

    public function updateModule(Request $request, $id)
    {
        $user   = auth()->user();
        $module = PlanModule::findOrFail($id);

        if ($user->role !== 'admin' && $module->teacher_id !== $user->id) {
            abort(403);
        }

        $data = $request->validate([
            'date_written'      => 'nullable|date',
            'topic'             => 'required|string',
            'learning_outcome'  => 'nullable|string',
            'assessment'        => 'nullable|string',
            'rubric'            => 'nullable|string',
            'objectives'        => 'nullable|string',
            'duration_and_flow' => 'nullable|string',
            'materials'         => 'nullable|string',
        ]);

        $data = $this->sanitizeRichFields($data, [
            'topic', 'learning_outcome', 'assessment', 'rubric', 'objectives', 'duration_and_flow', 'materials',
        ]);

        $module->update($data);

        return redirect()->route('lesson-planning.index')
            ->with('status', 'Module updated successfully.');
    }

    public function destroyModule($id)
    {
        $user   = auth()->user();
        $module = PlanModule::findOrFail($id);

        if ($user->role !== 'admin' && $module->teacher_id !== $user->id) {
            abort(403);
        }

        $module->delete();

        return back()->with('status', 'Module deleted.');
    }

    public function remarkModule(Request $request, $id)
    {
        $user = auth()->user();
        if ($user->role !== 'admin') {
            abort(403);
        }

        $module = PlanModule::findOrFail($id);

        $data = $request->validate([
            'admin_remark' => 'nullable|string|max:2000',
        ]);

        $module->update($data);

        return back()->with('status', 'Remark saved.');
    }

    public function createLesson(Request $request)
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

        $subjectName = strtolower(optional($assignment->subject)->name ?? '');
        $isAgri      = $subjectName === 'agriculture';
        $isSports    = $subjectName === 'physical education';

        if ($this->blocksPrePrimaryPlanning(optional($assignment->schoolClass)->class_name, $user->id, $sessionId, $assignment->class_id, $assignment->section_id)) {
            abort(404, 'Preschool classes are planned via Pre-School Daily Plans, not Lesson Planning.');
        }

        $modules = PlanModule::where('session_id', $sessionId)
            ->where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->where('subject_id', $request->subject_id)
            ->orderByDesc('date_written')
            ->get();

        $view = $isAgri ? 'lesson-planning.agri-create' : ($isSports ? 'lesson-planning.sports-create' : 'lesson-planning.lesson-create');
        return view($view, compact('assignment', 'modules', 'sessionId'));
    }

    public function storeLesson(Request $request)
    {
        $user      = auth()->user();
        $sessionId = $this->getSchoolCurrentSession();

        $data = $request->validate([
            'class_id'            => 'required|integer',
            'section_id'          => 'required|integer',
            'subject_id'          => 'required|integer',
            'module_id'           => 'nullable|integer|exists:plan_modules,id',
            'module_required'     => 'required|in:0,1',
            'date_written'        => 'nullable|date',
            'scheduled_date'      => 'nullable|date',
            'date_execution'      => 'nullable|string|max:200',
            'lesson_type'         => 'nullable|string|max:50',
            'practical_notes'     => 'nullable|string',
            'chapter_topic'       => 'nullable|string',
            'period_timing'       => 'nullable|string|max:100',
            'learning_standard'   => 'nullable|string',
            'objective'           => 'nullable|string',
            'material_needed'     => 'nullable|string',
            'training_component'  => 'nullable|string',
            'student_responses'   => 'nullable|string',
            'hook'                => 'nullable|string',
            'teach'               => 'nullable|string',
            'guided_practice'     => 'nullable|string',
            'independent_practice'=> 'nullable|string',
            'closure'             => 'nullable|string',
            'homework'            => 'nullable|string',
            'other_notes'         => 'nullable|string',
            'remark'              => 'nullable|string',
        ]);

        $data = $this->sanitizeRichFields($data, [
            'chapter_topic', 'learning_standard', 'objective', 'material_needed', 'training_component',
            'student_responses', 'hook', 'teach', 'guided_practice', 'independent_practice',
            'closure', 'homework', 'other_notes', 'remark',
        ]);

        $assigned = SubjectTeacher::where('teacher_id', $user->id)
            ->where('session_id', $sessionId)
            ->where('class_id', $data['class_id'])
            ->where('section_id', $data['section_id'])
            ->where('subject_id', $data['subject_id'])
            ->exists();

        if (!$assigned) {
            abort(403, 'You are not assigned to teach this subject for this class.');
        }

        $lessonClass = SchoolClass::find($data['class_id']);

        if ($this->blocksPrePrimaryPlanning(optional($lessonClass)->class_name, $user->id, $sessionId, $data['class_id'], $data['section_id'])) {
            abort(404, 'Preschool classes are planned via Pre-School Daily Plans, not Lesson Planning.');
        }

        PlanLesson::create(array_merge($data, [
            'teacher_id' => $user->id,
            'session_id' => $sessionId,
        ]));

        return redirect()->route('lesson-planning.index')
            ->with('status', 'Lesson plan created successfully.');
    }

    public function editLesson($id)
    {
        $user      = auth()->user();
        $sessionId = $this->getSchoolCurrentSession();

        $lesson = PlanLesson::with(['subject', 'schoolClass', 'section', 'module'])->findOrFail($id);

        if ($user->role !== 'admin' && $lesson->teacher_id !== $user->id) {
            abort(403, 'You can only edit your own lesson plans.');
        }

        $modules = PlanModule::where('session_id', $lesson->session_id)
            ->where('class_id', $lesson->class_id)
            ->where('section_id', $lesson->section_id)
            ->where('subject_id', $lesson->subject_id)
            ->orderByDesc('date_written')
            ->get();

        $subjectName = strtolower(optional($lesson->subject)->name ?? '');
        $isAgri      = $subjectName === 'agriculture';
        $isSports    = $subjectName === 'physical education';
        $view        = $isAgri ? 'lesson-planning.agri-edit' : ($isSports ? 'lesson-planning.sports-edit' : 'lesson-planning.lesson-edit');
        return view($view, compact('lesson', 'modules'));
    }

    public function updateLesson(Request $request, $id)
    {
        $user   = auth()->user();
        $lesson = PlanLesson::findOrFail($id);

        if ($user->role !== 'admin' && $lesson->teacher_id !== $user->id) {
            abort(403);
        }

        $data = $request->validate([
            'module_id'           => 'nullable|integer|exists:plan_modules,id',
            'module_required'     => 'required|in:0,1',
            'date_written'        => 'nullable|date',
            'scheduled_date'      => 'nullable|date',
            'date_execution'      => 'nullable|string|max:200',
            'lesson_type'         => 'nullable|string|max:50',
            'practical_notes'     => 'nullable|string',
            'chapter_topic'       => 'nullable|string',
            'period_timing'       => 'nullable|string|max:100',
            'learning_standard'   => 'nullable|string',
            'objective'           => 'nullable|string',
            'material_needed'     => 'nullable|string',
            'training_component'  => 'nullable|string',
            'student_responses'   => 'nullable|string',
            'hook'                => 'nullable|string',
            'teach'               => 'nullable|string',
            'guided_practice'     => 'nullable|string',
            'independent_practice'=> 'nullable|string',
            'closure'             => 'nullable|string',
            'homework'            => 'nullable|string',
            'other_notes'         => 'nullable|string',
            'remark'              => 'nullable|string',
        ]);

        $data = $this->sanitizeRichFields($data, [
            'chapter_topic', 'learning_standard', 'objective', 'material_needed', 'training_component',
            'student_responses', 'hook', 'teach', 'guided_practice', 'independent_practice',
            'closure', 'homework', 'other_notes', 'remark',
        ]);

        $lesson->update($data);

        return redirect()->route('lesson-planning.index')
            ->with('status', 'Lesson plan updated successfully.');
    }

    public function destroyLesson($id)
    {
        $user   = auth()->user();
        $lesson = PlanLesson::findOrFail($id);

        if ($user->role !== 'admin' && $lesson->teacher_id !== $user->id) {
            abort(403);
        }

        $lesson->delete();

        return back()->with('status', 'Lesson plan deleted.');
    }

    public function remarkLesson(Request $request, $id)
    {
        $user = auth()->user();
        if ($user->role !== 'admin') {
            abort(403);
        }

        $lesson = PlanLesson::findOrFail($id);

        $data = $request->validate([
            'admin_remark' => 'nullable|string|max:2000',
        ]);

        $lesson->update($data);

        return back()->with('status', 'Remark saved.');
    }

    public function markComplete($id)
    {
        $user   = auth()->user();
        $lesson = PlanLesson::findOrFail($id);

        if ($user->role !== 'admin' && $lesson->teacher_id !== $user->id) {
            abort(403);
        }

        $lesson->update(['status' => 'completed']);

        return back()->with('status', 'Lesson plan marked as completed.');
    }

    /**
     * List existing plans for a timetable slot so the teacher can link one to a date.
     */
    public function linkSlot(Request $request)
    {
        $user      = auth()->user();
        $sessionId = $this->getSchoolCurrentSession();

        $classId       = (int) $request->get('class_id');
        $sectionId     = (int) $request->get('section_id');
        $subjectId     = (int) $request->get('subject_id');
        $scheduledDate = $request->get('scheduled_date'); // YYYY-MM-DD

        $assignment = SubjectTeacher::with(['subject', 'schoolClass', 'section'])
            ->where('teacher_id', $user->id)
            ->where('session_id', $sessionId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->where('subject_id', $subjectId)
            ->first();

        if (!$assignment) {
            abort(403, 'You are not assigned to teach this subject for this class.');
        }

        // Format the target date as d/m/Y for matching against free-text date_execution
        $dateDisplay = $scheduledDate ? \Carbon\Carbon::parse($scheduledDate)->format('j/n/Y') : null;

        $plans = PlanLesson::where('teacher_id', $user->id)
            ->where('session_id', $sessionId)
            ->where('class_id', $classId)
            ->where('section_id', $sectionId)
            ->where('subject_id', $subjectId)
            ->orderByDesc('date_written')
            ->get();

        return view('lesson-planning.link-slot', compact(
            'assignment', 'plans', 'scheduledDate', 'dateDisplay'
        ));
    }

    /**
     * Set scheduled_date on an existing lesson plan (called from timetable link-slot page).
     */
    public function setScheduledDate(Request $request, $id)
    {
        $user   = auth()->user();
        $lesson = PlanLesson::findOrFail($id);

        if ($user->role !== 'admin' && $lesson->teacher_id !== $user->id) {
            abort(403);
        }

        $data = $request->validate(['scheduled_date' => 'required|date']);
        $lesson->update(['scheduled_date' => $data['scheduled_date']]);

        return redirect()->route('timetable.teacher')
            ->with('status', 'Lesson plan linked to ' . \Carbon\Carbon::parse($data['scheduled_date'])->format('d M Y') . '.');
    }

    public function printView(Request $request)
    {
        $user   = auth()->user();
        $module = null;
        $lesson = null;

        if ($request->filled('lesson_id')) {
            $lesson = PlanLesson::with(['subject', 'schoolClass', 'section', 'teacher', 'module'])->findOrFail($request->lesson_id);
            if ($user->role !== 'admin' && $lesson->teacher_id !== $user->id) {
                abort(403);
            }
        } elseif ($request->filled('module_id')) {
            $module = PlanModule::with(['subject', 'schoolClass', 'section', 'teacher'])->findOrFail($request->module_id);
            if ($user->role !== 'admin' && $module->teacher_id !== $user->id) {
                abort(403);
            }
        } else {
            abort(404);
        }

        $session = $this->schoolSessionRepository->getLatestSession();

        return view('lesson-planning.print', compact('module', 'lesson', 'session'));
    }
}
