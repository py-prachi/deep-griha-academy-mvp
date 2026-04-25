<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ClassSubject;
use App\Models\Promotion;
use App\Models\Routine;
use App\Models\SubjectTeacher;
use App\Models\TimetablePeriod;
use App\Traits\SchoolSession;
use App\Interfaces\SchoolClassInterface;
use App\Interfaces\SchoolSessionInterface;
use Illuminate\Http\Request;

class TimetableController extends Controller
{
    use SchoolSession;

    protected $schoolSessionRepository;
    protected $schoolClassRepository;

    public function __construct(
        SchoolSessionInterface $schoolSessionRepository,
        SchoolClassInterface $schoolClassRepository
    ) {
        $this->middleware('auth');
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository   = $schoolClassRepository;
    }

    /**
     * Admin: tabbed editor — pick class + section, edit each day's timetable.
     */
    public function edit(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $session_id     = $this->getSchoolCurrentSession();
        $school_classes = $this->schoolClassRepository->getAllBySession($session_id);

        $class_id   = $request->query('class_id');
        $section_id = $request->query('section_id');

        $classSubjects = collect();
        // grid[$weekday][$period_id] = course_id
        $grid = [];
        // periodsByDay[$weekday] = Collection of TimetablePeriod
        $periodsByDay = [];

        $days = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'];

        if ($class_id && $section_id) {
            $classSubjects = ClassSubject::with('subject')
                ->where('class_id', $class_id)
                ->where('session_id', $session_id)
                ->get();

            $routines = Routine::with(['period', 'course.subject'])
                ->where('class_id', $class_id)
                ->where('section_id', $section_id)
                ->where('session_id', $session_id)
                ->get();

            foreach ($routines as $routine) {
                if ($routine->period_id) {
                    $grid[$routine->weekday][$routine->period_id] = $routine->course_id;
                }
            }
        }

        foreach (array_keys($days) as $weekday) {
            $periodsByDay[$weekday] = TimetablePeriod::getForDay($weekday);
        }

        // Also load default periods (weekday=0) for the setup section
        $defaultPeriods = TimetablePeriod::where('weekday', 0)->orderBy('sort_order')->get();

        return view('timetable.edit', [
            'school_classes' => $school_classes,
            'class_id'       => $class_id,
            'section_id'     => $section_id,
            'session_id'     => $session_id,
            'classSubjects'  => $classSubjects,
            'grid'           => $grid,
            'periodsByDay'   => $periodsByDay,
            'defaultPeriods' => $defaultPeriods,
            'days'           => $days,
            'active_day'     => (int) $request->query('active_day', 1),
        ]);
    }

    /**
     * Admin: save one day's timetable slots.
     * POST fields: class_id, section_id, session_id, weekday, slots[period_id] = cs_id
     */
    public function save(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'class_id'   => 'required',
            'section_id' => 'required',
            'weekday'    => 'required|integer|min:1|max:6',
        ]);

        $class_id   = $request->input('class_id');
        $section_id = $request->input('section_id');
        $session_id = $request->input('session_id') ?: $this->getSchoolCurrentSession();
        $weekday    = $request->input('weekday');

        // Delete only this day's existing slots
        Routine::where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->where('session_id', $session_id)
            ->where('weekday', $weekday)
            ->delete();

        foreach ($request->input('slots', []) as $period_id => $class_subject_id) {
            if (empty($class_subject_id)) continue;

            Routine::create([
                'class_id'   => $class_id,
                'section_id' => $section_id,
                'session_id' => $session_id,
                'weekday'    => $weekday,
                'period_id'  => $period_id,
                'course_id'  => $class_subject_id,
                'start'      => '',
                'end'        => '',
            ]);
        }

        $dayNames = [1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday'];
        return redirect()->route('timetable.edit', [
            'class_id'   => $class_id,
            'section_id' => $section_id,
            'active_day' => $weekday,
        ])->with('status', ($dayNames[$weekday] ?? 'Day') . ' timetable saved.');
    }

    /**
     * Admin: full-week read-only timetable grid for a class+section.
     */
    public function show(Request $request)
    {
        if (auth()->user()->role !== 'admin') abort(403);

        $class_id   = $request->query('class_id');
        $section_id = $request->query('section_id');
        $session_id = $this->getSchoolCurrentSession();

        $routines = Routine::with(['period', 'course.subject', 'schoolClass', 'section'])
            ->where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->where('session_id', $session_id)
            ->get();

        // grid[$weekday][$period_id] = routine
        $grid = [];
        foreach ($routines as $routine) {
            if ($routine->period_id) {
                $grid[$routine->weekday][$routine->period_id] = $routine;
            }
        }

        $days = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'];

        // Load actual periods for each day (custom if set, else default)
        // periodsByDay[$weekday] = Collection of TimetablePeriod
        // periodIdMap[$weekday][$sort_order] = period_id
        $periodsByDay = [];
        $periodIdMap  = [];
        foreach (array_keys($days) as $weekday) {
            $dayPeriods = TimetablePeriod::getForDay($weekday);
            $periodsByDay[$weekday] = $dayPeriods;
            foreach ($dayPeriods as $p) {
                $periodIdMap[$weekday][$p->sort_order] = $p->id;
            }
        }

        // Unified row skeleton: collect all distinct sort_orders across all days,
        // then for each row use the default period label/time (or first day that has it).
        // We index by sort_order so rows align across days.
        $allSortOrders = collect();
        foreach ($periodsByDay as $dayPeriods) {
            foreach ($dayPeriods as $p) {
                $allSortOrders->push($p->sort_order);
            }
        }
        $allSortOrders = $allSortOrders->unique()->sort()->values();

        // Build a representative period object per sort_order (label, time, is_break)
        // Prefer default (weekday=0) period; fall back to whichever day has it.
        $defaultPeriodsBySortOrder = TimetablePeriod::where('weekday', 0)
            ->orderBy('sort_order')->get()->keyBy('sort_order');

        $allPeriods = $allSortOrders->map(function ($sortOrder) use ($defaultPeriodsBySortOrder, $periodsByDay) {
            if (isset($defaultPeriodsBySortOrder[$sortOrder])) {
                return $defaultPeriodsBySortOrder[$sortOrder];
            }
            // Fall back to first day that has this sort_order
            foreach ($periodsByDay as $dayPeriods) {
                foreach ($dayPeriods as $p) {
                    if ($p->sort_order == $sortOrder) return $p;
                }
            }
            return null;
        })->filter()->values();

        // periodObjMap[$weekday][$sort_order] = TimetablePeriod — so the view can check
        // each day's own is_break/label rather than the representative default period.
        $periodObjMap = [];
        $customDays   = []; // weekdays that have their own custom period records
        foreach ($periodsByDay as $weekday => $dayPeriods) {
            $isCustom = $dayPeriods->first() && $dayPeriods->first()->weekday != 0;
            if ($isCustom) $customDays[] = $weekday;
            foreach ($dayPeriods as $p) {
                $periodObjMap[$weekday][$p->sort_order] = $p;
            }
        }

        $classLabel   = optional(optional($routines->first())->schoolClass)->class_name;
        $sectionLabel = optional(optional($routines->first())->section)->section_name;

        return view('timetable.show', [
            'grid'          => $grid,
            'allPeriods'    => $allPeriods,
            'periodIdMap'   => $periodIdMap,
            'periodObjMap'  => $periodObjMap,
            'customDays'    => $customDays,
            'classLabel'    => $classLabel,
            'sectionLabel'  => $sectionLabel,
        ]);
    }

    /**
     * Teacher: own timetable across all assigned classes — day tabs.
     */
    public function teacherView(Request $request)
    {
        $user = auth()->user();
        if ($user->role !== 'teacher' && $user->role !== 'admin') {
            abort(403);
        }

        // Admin can view any teacher's timetable via ?teacher_id=
        $teacherId = $user->id;
        $viewingTeacher = null;
        if ($user->role === 'admin' && $request->query('teacher_id')) {
            $teacherId = (int) $request->query('teacher_id');
            $viewingTeacher = \App\Models\User::find($teacherId);
        }

        $session_id = $this->getSchoolCurrentSession();

        $assignments = SubjectTeacher::with(['subject', 'schoolClass', 'section'])
            ->where('teacher_id', $teacherId)
            ->where('session_id', $session_id)
            ->get();

        $classSubjectIds = [];
        foreach ($assignments as $assignment) {
            $cs = ClassSubject::where('class_id', $assignment->class_id)
                ->where('subject_id', $assignment->subject_id)
                ->where('session_id', $session_id)
                ->first();
            if ($cs) {
                $classSubjectIds[] = $cs->id;
            }
        }

        $routines = collect();
        if (!empty($classSubjectIds)) {
            $routines = Routine::with(['period', 'course.subject', 'schoolClass', 'section'])
                ->whereIn('course_id', $classSubjectIds)
                ->where('session_id', $session_id)
                ->get();
        }

        $days = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'];

        $grid = [];
        foreach ($routines as $routine) {
            if ($routine->period_id) {
                $grid[$routine->weekday][$routine->period_id] = $routine;
            }
        }

        $periodsByDay = [];
        foreach (array_keys($days) as $weekday) {
            $periodsByDay[$weekday] = TimetablePeriod::getForDay($weekday);
        }

        return view('timetable.teacher', [
            'days'          => $days,
            'grid'          => $grid,
            'periodsByDay'  => $periodsByDay,
            'routines'      => $routines,
            'viewingTeacher'=> $viewingTeacher,
        ]);
    }

    /**
     * Admin: update a single period field (AJAX).
     */
    public function periodUpdate(Request $request)
    {
        if (auth()->user()->role !== 'admin') abort(403);

        $period = TimetablePeriod::find($request->input('id'));
        if (!$period) return response()->json(['ok' => false, 'error' => 'Period not found']);

        $field   = $request->input('field');
        $allowed = ['label', 'start_time', 'end_time', 'is_break', 'sort_order'];
        if (!in_array($field, $allowed)) return response()->json(['ok' => false, 'error' => 'Invalid field']);

        $period->$field = $request->input('value');
        $period->save();

        return response()->json(['ok' => true]);
    }

    /**
     * Admin: delete a period (AJAX).
     */
    public function periodDelete(Request $request)
    {
        if (auth()->user()->role !== 'admin') abort(403);

        $period = TimetablePeriod::find($request->input('id'));
        if (!$period) return response()->json(['ok' => false, 'error' => 'Not found']);

        Routine::where('period_id', $period->id)->delete();
        $period->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Admin: add a new period for a specific weekday (AJAX).
     * weekday=0 means default (all days), 1-6 means day-specific.
     */
    public function periodAdd(Request $request)
    {
        if (auth()->user()->role !== 'admin') abort(403);

        $weekday = (int) $request->input('weekday', 0);
        $maxSort = TimetablePeriod::where('weekday', $weekday)->max('sort_order') ?? 0;

        $period = TimetablePeriod::create([
            'label'      => 'New Period',
            'start_time' => '08:00',
            'end_time'   => '08:45',
            'sort_order' => $maxSort + 1,
            'is_break'   => false,
            'weekday'    => $weekday,
        ]);

        return response()->json([
            'ok'         => true,
            'id'         => $period->id,
            'label'      => $period->label,
            'start_time' => $period->start_time,
            'end_time'   => $period->end_time,
            'is_break'   => $period->is_break,
        ]);
    }

    /**
     * Admin: reset a day back to default (delete its custom periods) (AJAX).
     */
    public function periodResetDay(Request $request)
    {
        if (auth()->user()->role !== 'admin') abort(403);

        $weekday = (int) $request->input('weekday');
        if ($weekday < 1 || $weekday > 6) return response()->json(['ok' => false, 'error' => 'Invalid day']);

        // Remove timetable slots that used these custom period ids
        $periodIds = TimetablePeriod::where('weekday', $weekday)->pluck('id');
        Routine::whereIn('period_id', $periodIds)->delete();

        // Delete the custom periods
        TimetablePeriod::where('weekday', $weekday)->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Admin: copy default periods (weekday=0) into a specific day (AJAX).
     * Used when a day has no custom periods and admin wants to customise it.
     */
    public function periodCopyDefaults(Request $request)
    {
        if (auth()->user()->role !== 'admin') abort(403);

        $weekday = (int) $request->input('weekday');
        if ($weekday < 1 || $weekday > 6) return response()->json(['ok' => false, 'error' => 'Invalid day']);

        // Don't copy if day already has custom periods
        if (TimetablePeriod::where('weekday', $weekday)->exists()) {
            return response()->json(['ok' => false, 'error' => 'Day already has custom periods']);
        }

        $defaults = TimetablePeriod::where('weekday', 0)->orderBy('sort_order')->get();
        $newPeriods = [];
        foreach ($defaults as $d) {
            $p = TimetablePeriod::create([
                'label'      => $d->label,
                'start_time' => $d->start_time,
                'end_time'   => $d->end_time,
                'sort_order' => $d->sort_order,
                'is_break'   => $d->is_break,
                'weekday'    => $weekday,
            ]);
            $newPeriods[] = ['id' => $p->id, 'label' => $p->label, 'start_time' => $p->start_time,
                             'end_time' => $p->end_time, 'is_break' => $p->is_break, 'sort_order' => $p->sort_order];
        }

        return response()->json(['ok' => true, 'periods' => $newPeriods]);
    }

    /**
     * Student: view own class timetable — day tabs.
     */
    public function studentView(Request $request)
    {
        $user = auth()->user();
        if ($user->role !== 'student' && $user->role !== 'admin') {
            abort(403);
        }

        $session_id = $this->getSchoolCurrentSession();

        $promotion = Promotion::with(['section', 'schoolClass'])
            ->where('student_id', $user->id)
            ->where('session_id', $session_id)
            ->first();

        $days = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'];

        if (!$promotion) {
            return view('timetable.student', [
                'promotion'    => null,
                'days'         => $days,
                'grid'         => [],
                'periodsByDay' => [],
                'error'        => 'Your class assignment is not set up yet.',
            ]);
        }

        $routines = Routine::with(['period', 'course.subject'])
            ->where('class_id', $promotion->class_id)
            ->where('section_id', $promotion->section_id)
            ->where('session_id', $session_id)
            ->get();

        $grid = [];
        foreach ($routines as $routine) {
            if ($routine->period_id) {
                $grid[$routine->weekday][$routine->period_id] = $routine;
            }
        }

        $periodsByDay = [];
        $periodIdMap  = [];
        $periodObjMap = [];
        foreach (array_keys($days) as $weekday) {
            $dayPeriods = TimetablePeriod::getForDay($weekday);
            $periodsByDay[$weekday] = $dayPeriods;
            foreach ($dayPeriods as $p) {
                $periodIdMap[$weekday][$p->sort_order]  = $p->id;
                $periodObjMap[$weekday][$p->sort_order] = $p;
            }
        }

        // Unified sort_order skeleton (same approach as admin show)
        $allSortOrders = collect();
        foreach ($periodsByDay as $dayPeriods) {
            foreach ($dayPeriods as $p) { $allSortOrders->push($p->sort_order); }
        }
        $allSortOrders = $allSortOrders->unique()->sort()->values();

        $defaultPeriodsBySortOrder = TimetablePeriod::where('weekday', 0)
            ->orderBy('sort_order')->get()->keyBy('sort_order');

        $allPeriods = $allSortOrders->map(function ($sortOrder) use ($defaultPeriodsBySortOrder, $periodsByDay) {
            if (isset($defaultPeriodsBySortOrder[$sortOrder])) {
                return $defaultPeriodsBySortOrder[$sortOrder];
            }
            foreach ($periodsByDay as $dayPeriods) {
                foreach ($dayPeriods as $p) {
                    if ($p->sort_order == $sortOrder) return $p;
                }
            }
            return null;
        })->filter()->values();

        return view('timetable.student', [
            'promotion'    => $promotion,
            'days'         => $days,
            'grid'         => $grid,
            'allPeriods'   => $allPeriods,
            'periodIdMap'  => $periodIdMap,
            'periodObjMap' => $periodObjMap,
            'error'        => null,
        ]);
    }
}
