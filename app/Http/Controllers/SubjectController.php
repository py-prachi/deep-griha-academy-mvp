<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\ClassSubject;
use App\Models\SchoolClass;
use App\Models\SchoolSession;
use App\Models\ClassTeacher;
use App\Models\SubjectTeacher;
use App\Models\User;
use App\Models\Section;
use App\Traits\SchoolSession as SchoolSessionTrait;
use App\Interfaces\SchoolSessionInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubjectController extends Controller
{
    use SchoolSessionTrait;

    protected $schoolSessionRepository;

    public function __construct(SchoolSessionInterface $schoolSessionRepository)
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (auth()->user()->role !== 'admin') abort(403);
            return $next($request);
        });
        $this->schoolSessionRepository = $schoolSessionRepository;
    }

    // ── SUBJECTS MANAGEMENT ───────────────────────────────────────────────

    public function index()
    {
        $subjects      = Subject::orderBy('sort_order')->orderBy('name')->get();
        $session       = $this->schoolSessionRepository->getLatestSession();
        $sessionId     = $session->id;
        $schoolClasses = SchoolClass::where('session_id', $sessionId)->orderBy('id')->get();

        // For each subject, which class_ids are assigned in current session
        $classSubjectMap = ClassSubject::where('session_id', $sessionId)
            ->get()
            ->groupBy('subject_id')
            ->map(fn($rows) => $rows->pluck('class_id')->toArray());

        return view('academics.subjects', compact('subjects', 'schoolClasses', 'sessionId', 'classSubjectMap', 'session'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:10',
        ]);

        Subject::create([
            'name'       => $request->name,
            'code'       => $request->code ?? null,
            'sort_order' => Subject::max('sort_order') + 1,
            'is_active'  => true,
        ]);

        return redirect()->route('subjects.index')->with('status', 'Subject "' . $request->name . '" added.');
    }

    public function update(Request $request, Subject $subject)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:10',
        ]);

        $subject->update([
            'name'      => $request->name,
            'code'      => $request->code ?? null,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('subjects.index')->with('status', 'Subject updated.');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();
        return redirect()->route('subjects.index')->with('status', 'Subject removed.');
    }

    // ── CLASS-SUBJECT ASSIGNMENT ──────────────────────────────────────────

    public function saveClassSubjects(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'session_id' => 'required|exists:school_sessions,id',
            'class_ids'  => 'nullable|array',
            'class_ids.*'=> 'exists:school_classes,id',
        ]);

        $subjectId = $request->subject_id;
        $sessionId = $request->session_id;
        $classIds  = $request->class_ids ?? [];

        DB::transaction(function () use ($subjectId, $sessionId, $classIds) {
            ClassSubject::where('subject_id', $subjectId)->where('session_id', $sessionId)->delete();
            foreach ($classIds as $classId) {
                ClassSubject::create([
                    'subject_id' => $subjectId,
                    'class_id'   => $classId,
                    'session_id' => $sessionId,
                ]);
            }
        });

        return redirect()->route('subjects.index')->with('status', 'Class assignments saved.');
    }

    // ── TEACHER ASSIGNMENTS ───────────────────────────────────────────────

    public function teacherAssignments()
    {
        $session       = $this->schoolSessionRepository->getLatestSession();
        $sessionId     = $session->id;
        $schoolClasses = SchoolClass::where('session_id', $sessionId)->orderBy('id')->get();
        $teachers      = User::where('role', 'teacher')->orderBy('first_name')->get();
        $subjects      = Subject::active()->orderBy('sort_order')->orderBy('name')->get();

        // Existing class teacher assignments
        $classTeachers = ClassTeacher::with(['teacher', 'schoolClass', 'section'])
            ->where('session_id', $sessionId)->get();

        // Existing subject teacher assignments
        $subjectTeachers = SubjectTeacher::with(['teacher', 'subject', 'schoolClass', 'section'])
            ->where('session_id', $sessionId)->get();

        return view('academics.teacher-assignments', compact(
            'session', 'sessionId', 'schoolClasses', 'teachers', 'subjects',
            'classTeachers', 'subjectTeachers'
        ));
    }

    public function saveClassTeacher(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'class_id'   => 'required|exists:school_classes,id',
            'section_id' => 'required|exists:sections,id',
            'session_id' => 'required|exists:school_sessions,id',
        ]);

        ClassTeacher::updateOrCreate(
            [
                'class_id'   => $request->class_id,
                'section_id' => $request->section_id,
                'session_id' => $request->session_id,
            ],
            ['teacher_id' => $request->teacher_id]
        );

        $teacher = User::find($request->teacher_id);
        return redirect()->route('academics.teacher-assignments')
            ->with('status', $teacher->first_name . ' assigned as class teacher.');
    }

    public function removeClassTeacher(ClassTeacher $classTeacher)
    {
        $classTeacher->delete();
        return redirect()->route('academics.teacher-assignments')->with('status', 'Class teacher removed.');
    }

    public function saveSubjectTeacher(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
            'class_id'   => 'required|exists:school_classes,id',
            'section_id' => 'required|exists:sections,id',
            'session_id' => 'required|exists:school_sessions,id',
        ]);

        SubjectTeacher::firstOrCreate([
            'teacher_id' => $request->teacher_id,
            'subject_id' => $request->subject_id,
            'class_id'   => $request->class_id,
            'section_id' => $request->section_id,
            'session_id' => $request->session_id,
        ]);

        $teacher = User::find($request->teacher_id);
        $subject = Subject::find($request->subject_id);
        return redirect()->route('academics.teacher-assignments')
            ->with('status', $teacher->first_name . ' assigned for ' . $subject->name . '.');
    }

    public function removeSubjectTeacher(SubjectTeacher $subjectTeacher)
    {
        $subjectTeacher->delete();
        return redirect()->route('academics.teacher-assignments')->with('status', 'Subject teacher removed.');
    }
}
