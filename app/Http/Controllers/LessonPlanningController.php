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
use Illuminate\Http\Request;

class LessonPlanningController extends Controller
{
    use SchoolSession;

    protected $schoolSessionRepository;

    public function __construct(SchoolSessionInterface $schoolSessionRepository)
    {
        $this->schoolSessionRepository = $schoolSessionRepository;
    }

    private $preschoolClasses = ['Nursery', 'LKG', 'UKG'];

    public function index()
    {
        $user      = auth()->user();
        $sessionId = $this->getSchoolCurrentSession();

        if ($user->role === 'admin') {
            $classes = SchoolClass::whereHas('sections', function ($q) use ($sessionId) {
                $q->where('session_id', $sessionId);
            })->whereNotIn('class_name', ['Nursery', 'LKG', 'UKG'])
              ->orderBy('id')
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

        // Teacher view
        $subjectAssignments = SubjectTeacher::with(['subject', 'schoolClass', 'section'])
            ->where('teacher_id', $user->id)
            ->where('session_id', $sessionId)
            ->whereHas('schoolClass', function ($q) {
                $q->whereNotIn('class_name', ['Nursery', 'LKG', 'UKG']);
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

        // CT view (read-only)
        $ctAssignment = ClassTeacher::with(['schoolClass', 'section'])
            ->where('teacher_id', $user->id)
            ->where('session_id', $sessionId)
            ->whereHas('schoolClass', function ($q) {
                $q->whereNotIn('class_name', ['Nursery', 'LKG', 'UKG']);
            })
            ->first();

        $ctLessons = null;
        if ($ctAssignment) {
            $ctLessons = PlanLesson::with(['subject', 'teacher', 'module'])
                ->where('session_id', $sessionId)
                ->where('class_id', $ctAssignment->class_id)
                ->where('section_id', $ctAssignment->section_id)
                ->orderByDesc('date_written')
                ->get()
                ->groupBy('subject_id');
        }

        return view('lesson-planning.index', compact(
            'subjectAssignments', 'myModules', 'myLessons',
            'ctAssignment', 'ctLessons', 'sessionId'
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

        $assigned = SubjectTeacher::where('teacher_id', $user->id)
            ->where('session_id', $sessionId)
            ->where('class_id', $data['class_id'])
            ->where('section_id', $data['section_id'])
            ->where('subject_id', $data['subject_id'])
            ->exists();

        if (!$assigned) {
            abort(403, 'You are not assigned to teach this subject for this class.');
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

        $modules = PlanModule::where('session_id', $sessionId)
            ->where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->where('subject_id', $request->subject_id)
            ->orderByDesc('date_written')
            ->get();

        return view('lesson-planning.lesson-create', compact('assignment', 'modules', 'sessionId'));
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

        $assigned = SubjectTeacher::where('teacher_id', $user->id)
            ->where('session_id', $sessionId)
            ->where('class_id', $data['class_id'])
            ->where('section_id', $data['section_id'])
            ->where('subject_id', $data['subject_id'])
            ->exists();

        if (!$assigned) {
            abort(403, 'You are not assigned to teach this subject for this class.');
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

        return view('lesson-planning.lesson-edit', compact('lesson', 'modules'));
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
