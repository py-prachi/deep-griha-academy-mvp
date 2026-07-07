<?php

namespace App\Http\Controllers;

use App\Models\MonthlyOutcome;
use App\Models\SubjectTeacher;
use App\Models\ClassTeacher;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Traits\SchoolSession;
use App\Interfaces\SchoolSessionInterface;
use Illuminate\Http\Request;

class MonthlyOutcomeController extends Controller
{
    use SchoolSession;

    protected $schoolSessionRepository;

    public function __construct(SchoolSessionInterface $schoolSessionRepository)
    {
        $this->schoolSessionRepository = $schoolSessionRepository;
    }

    private function monthOrder(): array
    {
        return array_keys(MonthlyOutcome::MONTHS);
    }

    public function index()
    {
        $user      = auth()->user();
        $sessionId = $this->getSchoolCurrentSession();

        if ($user->role === 'admin') {
            $classes = SchoolClass::whereHas('sections', fn($q) => $q->where('session_id', $sessionId))
                ->orderBy('id')
                ->get();

            $outcomes = MonthlyOutcome::with(['subject', 'schoolClass', 'section', 'teacher'])
                ->where('session_id', $sessionId)
                ->get()
                ->sortBy(fn($o) => [
                    $o->class_id,
                    $o->subject->sort_order ?? 0,
                    array_search($o->month, $this->monthOrder()),
                ])
                ->groupBy('class_id');

            return view('monthly-outcomes.admin-index', compact('outcomes', 'classes', 'sessionId'));
        }

        // Subject teacher: their own assignments + outcomes they've entered
        $subjectAssignments = SubjectTeacher::with(['subject', 'schoolClass', 'section'])
            ->where('teacher_id', $user->id)
            ->where('session_id', $sessionId)
            ->get()
            ->sortBy(fn($st) => [$st->class_id, $st->subject->sort_order ?? 0]);

        $myOutcomes = MonthlyOutcome::where('session_id', $sessionId)
            ->where('teacher_id', $user->id)
            ->get()
            ->sortBy(fn($o) => array_search($o->month, $this->monthOrder()))
            ->groupBy(fn($o) => $o->class_id . '_' . $o->subject_id);

        // CT: view outcomes for their class (all subjects, all teachers)
        $ctAssignment = ClassTeacher::with(['schoolClass', 'section'])
            ->where('teacher_id', $user->id)
            ->where('session_id', $sessionId)
            ->first();

        $ctOutcomes = null;
        if ($ctAssignment) {
            $ctOutcomes = MonthlyOutcome::with(['subject', 'teacher'])
                ->where('session_id', $sessionId)
                ->where('class_id', $ctAssignment->class_id)
                ->where('section_id', $ctAssignment->section_id)
                ->get()
                ->sortBy(fn($o) => [$o->subject->sort_order ?? 0, array_search($o->month, $this->monthOrder())])
                ->groupBy('subject_id');
        }

        return view('monthly-outcomes.index', compact(
            'subjectAssignments', 'myOutcomes', 'ctAssignment', 'ctOutcomes', 'sessionId'
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

        return view('monthly-outcomes.create', compact('assignment', 'sessionId'));
    }

    public function store(Request $request)
    {
        $user      = auth()->user();
        $sessionId = $this->getSchoolCurrentSession();

        $data = $request->validate([
            'class_id'         => 'required|integer',
            'section_id'       => 'required|integer',
            'subject_id'       => 'required|integer',
            'month'            => 'required|in:' . implode(',', array_keys(MonthlyOutcome::MONTHS)),
            'number_of_days'   => 'required|integer|min:1|max:31',
            'chapter_number'   => 'nullable|string|max:20',
            'chapter_name'     => 'required|string|max:255',
            'learning_outcomes'=> 'nullable|string',
            'start_date'       => 'nullable|date',
            'end_date'         => 'nullable|date|after_or_equal:start_date',
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

        MonthlyOutcome::create(array_merge($data, [
            'teacher_id' => $user->id,
            'session_id' => $sessionId,
        ]));

        return redirect()->route('monthly-outcomes.index')
            ->with('status', 'Learning Outcomes entry added successfully.');
    }

    public function edit($id)
    {
        $user    = auth()->user();
        $outcome = MonthlyOutcome::with(['subject', 'schoolClass', 'section'])->findOrFail($id);

        if ($user->role !== 'admin' && $outcome->teacher_id !== $user->id) {
            abort(403, 'You can only edit your own entries.');
        }

        return view('monthly-outcomes.edit', ['outcome' => $outcome]);
    }

    public function update(Request $request, $id)
    {
        $user    = auth()->user();
        $outcome = MonthlyOutcome::findOrFail($id);

        if ($user->role !== 'admin' && $outcome->teacher_id !== $user->id) {
            abort(403);
        }

        $data = $request->validate([
            'month'            => 'required|in:' . implode(',', array_keys(MonthlyOutcome::MONTHS)),
            'number_of_days'   => 'required|integer|min:1|max:31',
            'chapter_number'   => 'nullable|string|max:20',
            'chapter_name'     => 'required|string|max:255',
            'learning_outcomes'=> 'nullable|string',
            'start_date'       => 'nullable|date',
            'end_date'         => 'nullable|date|after_or_equal:start_date',
        ]);

        $outcome->update($data);

        return redirect()->route('monthly-outcomes.index')
            ->with('status', 'Learning Outcomes entry updated.');
    }

    public function destroy($id)
    {
        $user    = auth()->user();
        $outcome = MonthlyOutcome::findOrFail($id);

        if ($user->role !== 'admin' && $outcome->teacher_id !== $user->id) {
            abort(403);
        }

        $outcome->delete();

        return back()->with('status', 'Entry deleted.');
    }

    public function printView(Request $request)
    {
        $user      = auth()->user();
        $sessionId = $this->getSchoolCurrentSession();

        $teacherId = ($user->role === 'admin' && $request->filled('teacher_id'))
            ? $request->teacher_id
            : $user->id;

        $classId   = $request->class_id;
        $subjectId = $request->subject_id;

        $query = MonthlyOutcome::with(['subject', 'schoolClass', 'section', 'teacher'])
            ->where('session_id', $sessionId)
            ->where('teacher_id', $teacherId);

        if ($classId)   $query->where('class_id',   $classId);
        if ($subjectId) $query->where('subject_id', $subjectId);

        $outcomes = $query->get()->sortBy(fn($o) => [
            $o->class_id,
            $o->subject_id,
            array_search($o->month, $this->monthOrder()),
        ])->groupBy(fn($o) => $o->class_id . '_' . $o->subject_id);

        $teacher = \App\Models\User::find($teacherId);
        $session = $this->schoolSessionRepository->getLatestSession();

        return view('monthly-outcomes.print', compact('outcomes', 'teacher', 'session'));
    }
}
