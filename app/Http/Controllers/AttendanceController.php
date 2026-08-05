<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassTeacher;
use Illuminate\Http\Request;
use App\Interfaces\UserInterface;
use App\Interfaces\SchoolClassInterface;
use App\Interfaces\SchoolSessionInterface;
use App\Interfaces\AcademicSettingInterface;
use App\Http\Requests\AttendanceStoreRequest;
use App\Interfaces\SectionInterface;
use App\Repositories\AttendanceRepository;
use App\Repositories\CourseRepository;
use App\Traits\SchoolSession;
use App\Traits\WorkingDays;
use App\Models\Promotion;
use App\Models\SchoolSession as SchoolSessionModel;
use App\Models\Semester;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    use SchoolSession;
    use WorkingDays;
    protected $academicSettingRepository;
    protected $schoolSessionRepository;
    protected $schoolClassRepository;
    protected $sectionRepository;
    protected $userRepository;

    public function __construct(
        UserInterface $userRepository,
        AcademicSettingInterface $academicSettingRepository,
        SchoolSessionInterface $schoolSessionRepository,
        SchoolClassInterface $schoolClassRepository,
        SectionInterface $sectionRepository
    ) {
        // $this->middleware(['can:view attendances']);
        $this->middleware('can:view attendances')
        ->except(['showStudentAttendance', 'report']);


        $this->userRepository = $userRepository;
        $this->academicSettingRepository = $academicSettingRepository;
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository = $schoolClassRepository;
        $this->sectionRepository = $sectionRepository;
    }
    /**
     * Display a listing of the resource.
     * Admin: shows all classes/sections.
     * CT (teacher): auto-redirects to their assigned section's Take Attendance page.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $current_school_session_id = $this->getSchoolCurrentSession();
        $user = auth()->user();

        // Class teacher: redirect straight to their section — unless they're CT
        // for more than one class/section (e.g. one teacher covering both
        // Nursery and Lower KG), in which case show a chooser.
        if ($user->role === 'teacher') {
            $ctAssignments = ClassTeacher::with(['schoolClass', 'section'])
                ->where('teacher_id', $user->id)
                ->where('session_id', $current_school_session_id)
                ->get();

            if ($ctAssignments->count() === 1) {
                $ct = $ctAssignments->first();
                return redirect()->route('attendance.create.show', [
                    'class_id'   => $ct->class_id,
                    'section_id' => $ct->section_id,
                ]);
            }

            if ($ctAssignments->count() > 1) {
                return view('attendances.index', [
                    'classes_and_sections' => ['school_classes' => collect(), 'school_sections' => collect()],
                    'academic_setting'     => $this->academicSettingRepository->getAcademicSetting(),
                    'courses'              => collect(),
                    'is_ct'                => false,
                    'not_assigned'         => false,
                    'ct_assignments'       => $ctAssignments,
                ]);
            }

            // Teacher exists but not assigned as CT — show message
            return view('attendances.index', [
                'classes_and_sections' => ['school_classes' => collect(), 'school_sections' => collect()],
                'academic_setting'     => $this->academicSettingRepository->getAcademicSetting(),
                'courses'              => collect(),
                'is_ct'                => false,
                'not_assigned'         => true,
            ]);
        }

        // Admin: show all classes and sections
        $school_classes  = $this->schoolClassRepository->getAllBySession($current_school_session_id);
        $school_sections = $this->sectionRepository->getAllBySession($current_school_session_id);
        $academic_setting = $this->academicSettingRepository->getAcademicSetting();

        $courses = collect();
        if (($academic_setting->attendance_type ?? 'section') === 'course') {
            $courseRepository = new CourseRepository();
            $courses = $courseRepository->getAll($current_school_session_id);
        }

        return view('attendances.index', [
            'classes_and_sections' => [
                'school_classes'  => $school_classes,
                'school_sections' => $school_sections,
            ],
            'academic_setting' => $academic_setting,
            'courses'          => $courses,
            'is_ct'            => false,
            'not_assigned'     => false,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
public function create(Request $request)
{
    if (auth()->user()->role === 'admin') {
        abort(403, 'Admin can only view attendance. Taking attendance is done by the Class Teacher.');
    }

    if ($request->query('class_id') === null) {
        abort(404);
    }

    try {
        // ✅ FETCH FIRST
        $date = $request->query('date', Carbon::today()->toDateString());
        $academic_setting = $this->academicSettingRepository->getAcademicSetting();
        $attendance_type = $academic_setting->attendance_type ?? 'section';

        $current_school_session_id = $this->getSchoolCurrentSession();

        $class_id   = $request->query('class_id');
        $section_id = $request->query('section_id', 0);
        $course_id  = $request->query('course_id');

        // Only the Class Teacher for this class/section may take attendance —
        // not just any subject teacher.
        $isCt = ClassTeacher::where('teacher_id', auth()->id())
            ->where('session_id', $current_school_session_id)
            ->where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->exists();
        if (!$isCt) {
            abort(403, 'Only the Class Teacher for this class/section can take attendance.');
        }

        // ✅ STUDENTS
        $student_list = $this->userRepository
            ->getAllStudents($current_school_session_id, $class_id, $section_id);

        $school_class   = $this->schoolClassRepository->findById($class_id);
        $school_section = $this->sectionRepository->findById($section_id);

        $attendanceRepository = new AttendanceRepository();

        // ✅ CORRECTLY HANDLE SECTION VS COURSE
        if ($attendance_type === 'section') {
            $attendance_count = $attendanceRepository
                ->getSectionAttendance($class_id, $section_id, $current_school_session_id, $date)
                ->count();
        } else {
            $attendance_count = $attendanceRepository
                ->getCourseAttendance($class_id, $course_id, $current_school_session_id)
                ->count();
        }

        return view('attendances.take', [
            'current_school_session_id' => $current_school_session_id,
            'academic_setting'          => $academic_setting,
            'student_list'              => $student_list,
            'school_class'              => $school_class,
            'school_section'            => $school_section,
            'attendance_count'          => $attendance_count,
        ]);
    } catch (\Exception $e) {
        return back()->withError($e->getMessage());
    }
}



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\AttendanceStoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AttendanceStoreRequest $request)
    {
        if (auth()->user()->role === 'admin') {
            abort(403, 'Admin can only view attendance. Taking attendance is done by the Class Teacher.');
        }

        $isCt = ClassTeacher::where('teacher_id', auth()->id())
            ->where('session_id', $request->input('session_id'))
            ->where('class_id', $request->input('class_id'))
            ->where('section_id', $request->input('section_id'))
            ->exists();
        if (!$isCt) {
            abort(403, 'Only the Class Teacher for this class/section can take attendance.');
        }

        try {
            $attendanceRepository = new AttendanceRepository();
            $attendanceRepository->saveAttendance($request->validated());

            return back()->with('status', 'Attendance save was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $date = $request->query('date', Carbon::today()->toDateString());
        if($request->query('class_id') == null){
            return abort(404);
        }

        $current_school_session_id = $this->getSchoolCurrentSession();

        $class_id = $request->query('class_id');
        $section_id = $request->query('section_id');
        $course_id = $request->query('course_id');

        $attendanceRepository = new AttendanceRepository();

        try {
            $academic_setting = $this->academicSettingRepository->getAcademicSetting();
            $attendance_type = $academic_setting->attendance_type ?? 'section';

            if ($attendance_type === 'section') {
                $attendances = $attendanceRepository
                    ->getSectionAttendance($class_id, $section_id, $current_school_session_id, $date);
            } else {
                $attendances = $attendanceRepository
                    ->getCourseAttendance($class_id, $course_id, $current_school_session_id, $date);
            }

            $data = ['attendances' => $attendances];
            
            return view('attendances.view', [
            'attendances' => $attendances,
            'academic_setting' => $academic_setting,
            'selected_date' => $date,

]);

        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }


// public function showStudentAttendance(Request $request, $id)
// {
//     // if (auth()->user()->role === "student" && auth()->user()->id != $id) {
//     //     abort(404);
//     // }
//     $user = auth()->user();

//     // ✅ Student: can only see own attendance
//     if ($user->role === "student") {
//         if ($user->id != $id) {
//             abort(404);
//         }
//         // student is allowed → continue
//     }
//     // 🔐 Teacher/Admin: must have permission
//     else {
//         abort_unless($user->can('view attendances'), 403);
//     }


//     $current_school_session_id = $this->getSchoolCurrentSession();
//     $selected_date = $request->query('date');

//     $attendanceRepository = new AttendanceRepository();

//     // ✅ FULL HISTORY — unchanged
//     $attendances = $attendanceRepository
//         ->getStudentAttendance($current_school_session_id, $id);

//     // ✅ Attendance for selected date
//     $attendance_for_date = null;
//     if ($selected_date) {
//         $attendance_for_date = Attendance::where('student_id', $id)
//             ->where('session_id', $current_school_session_id)
//             ->whereDate('created_at', $selected_date)
//             ->first();
//     }

//     $student = $this->userRepository->findStudent($id);

//     // 🔹 NEW: get attendance type
//     $academic_setting = $this->academicSettingRepository->getAcademicSetting();
//     $attendance_type = $academic_setting->attendance_type ?? 'section';

//     // ✅ Derive class / section / course from existing attendance records
//     $firstAttendance = $attendances->first();

//     $class_id = $firstAttendance ? (int) $firstAttendance->class_id : 0;
//     $section_id = $firstAttendance ? (int) $firstAttendance->section_id : 0;
//     $course_id = $firstAttendance ? (int) $firstAttendance->course_id : 0;


//     return view('attendances.attendance', [
//         'attendances'               => $attendances,
//         'student'                   => $student,
//         'selected_date'             => $selected_date,
//         'attendance_for_date'       => $attendance_for_date,
//         'current_school_session_id' => $current_school_session_id,

//         // 🔹 NEW: pass IDs + mode
//         'attendance_type' => $attendance_type,
//         'class_id'        => $class_id,
//         'section_id'      => $section_id,
//         'course_id'       => $course_id,
//     ]);
// }
public function showStudentAttendance(Request $request, $id)
{
    $user = auth()->user();

    if ($user->role === 'student') {
        if ($user->id != $id) abort(404);
    } else {
        abort_unless($user->can('view attendances'), 403);
    }

    $current_school_session_id = $this->getSchoolCurrentSession();
    $selected_date             = $request->query('date');

    $attendanceRepository = new AttendanceRepository();

    $attendances = $attendanceRepository->getStudentAttendance($current_school_session_id, $id);

    $attendance_for_date = null;
    if ($selected_date) {
        $attendance_for_date = Attendance::where('student_id', $id)
            ->where('session_id', $current_school_session_id)
            ->whereDate('created_at', $selected_date)
            ->first();
    }

    $student          = $this->userRepository->findStudent($id);
    $academic_setting = $this->academicSettingRepository->getAcademicSetting();
    $attendance_type  = $academic_setting->attendance_type ?? 'section';

    // ── FIX: get class/section from PROMOTION record, not attendance history.
    // Attendance history is unreliable: new students have none, deleted
    // courses/sections leave orphaned records. Promotion is the source of truth.
    $promotion = \App\Models\Promotion::where('student_id', $id)
        ->where('session_id', $current_school_session_id)
        ->first();

    $class_id      = $promotion ? (int) $promotion->class_id   : 0;
    $section_id    = $promotion ? (int) $promotion->section_id : 0;
    $course_id     = 0; // not stored on promotion; only needed for course-based mark form
    $has_promotion = $promotion !== null;

    $school_class   = $class_id   ? $this->schoolClassRepository->findById($class_id)   : null;
    $school_section = $section_id ? $this->sectionRepository->findById($section_id)     : null;

    // Teachers may only edit attendance for their own CT class/section.
    $isCt = $user->role === 'teacher' && ClassTeacher::where('teacher_id', $user->id)
        ->where('session_id', $current_school_session_id)
        ->where('class_id', $class_id)
        ->where('section_id', $section_id)
        ->exists();

    // Editable window for teachers: today and yesterday only. Admin: unrestricted.
    $editableFrom = Carbon::today()->subDay();

    return view('attendances.attendance', [
        'attendances'               => $attendances,
        'student'                   => $student,
        'selected_date'             => $selected_date,
        'attendance_for_date'       => $attendance_for_date,
        'current_school_session_id' => $current_school_session_id,
        'attendance_type'           => $attendance_type,
        'class_id'                  => $class_id,
        'section_id'                => $section_id,
        'course_id'                 => $course_id,
        'has_promotion'             => $has_promotion,
        'school_class'              => $school_class,
        'school_section'            => $school_section,
        'isCt'                      => $isCt,
        'editableFrom'              => $editableFrom,
    ]);
}



    /**
     * Per-student attendance report: month-wise + overall totals, based on
     * actual school working days (Mon-Fri, minus recorded holidays) rather
     * than just days attendance happened to be marked.
     */
    public function report(Request $request, $id)
    {
        $user = auth()->user();

        if ($user->role === 'student') {
            if ($user->id != $id) abort(404);
        } else {
            abort_unless($user->can('view attendances'), 403);
        }

        $sessionId = $this->getSchoolCurrentSession();

        $student   = $this->userRepository->findStudent($id);
        $promotion = Promotion::where('student_id', $id)
            ->where('session_id', $sessionId)
            ->first();

        if (!$promotion) {
            abort(404, 'Student has no active enrolment in the current session.');
        }

        $class_id   = (int) $promotion->class_id;
        $section_id = (int) $promotion->section_id;

        $school_class   = $this->schoolClassRepository->findById($class_id);
        $school_section = $this->sectionRepository->findById($section_id);

        // Anchor the range to the school year's start — every student uses the
        // same start date, regardless of when they individually joined.
        $from = $this->resolveSessionStart($sessionId, $class_id, $section_id);

        // "Till now" — capped at the session's end date if that's already
        // passed (so a report pulled after the year ends doesn't run past it).
        $sessionRow = SchoolSessionModel::find($sessionId);
        $to = Carbon::today();
        if ($sessionRow && $sessionRow->end_date && Carbon::parse($sessionRow->end_date)->lt($to)) {
            $to = Carbon::parse($sessionRow->end_date)->startOfDay();
        }

        $workingDays = $this->getWorkingDays($from, $to, $sessionId);

        $presentDates = Attendance::where('student_id', $id)
            ->where('session_id', $sessionId)
            ->where('status', 'on')
            ->whereDate('created_at', '>=', $from->toDateString())
            ->whereDate('created_at', '<=', $to->toDateString())
            ->pluck('created_at')
            ->map(fn($d) => Carbon::parse($d)->toDateString())
            ->flip();

        // Month-wise breakdown
        $months = [];
        foreach ($workingDays as $day) {
            $key = $day->format('Y-m');
            if (!isset($months[$key])) {
                $months[$key] = [
                    'label'   => $day->format('F Y'),
                    'total'   => 0,
                    'present' => 0,
                ];
            }
            $months[$key]['total']++;
            if ($presentDates->has($day->toDateString())) {
                $months[$key]['present']++;
            }
        }
        foreach ($months as &$m) {
            $m['percentage'] = $m['total'] > 0 ? round($m['present'] / $m['total'] * 100) : null;
        }
        unset($m);

        // Sum from the month buckets (not a raw count of "on" records) so the
        // overall total always agrees with the month-wise breakdown — a
        // present mark on a day that isn't an actual working day (e.g.
        // logged on a Saturday) doesn't count toward either.
        $totalWorkingDays = $workingDays->count();
        $totalPresent     = array_sum(array_column($months, 'present'));
        $overallPercentage = $totalWorkingDays > 0 ? round($totalPresent / $totalWorkingDays * 100) : null;

        return view('attendances.report', [
            'student'            => $student,
            'school_class'       => $school_class,
            'school_section'     => $school_section,
            'from'               => $from,
            'to'                 => $to,
            'months'             => $months,
            'totalWorkingDays'   => $totalWorkingDays,
            'totalPresent'       => $totalPresent,
            'overallPercentage'  => $overallPercentage,
        ]);
    }

    // Best available anchor for "when this session's attendance began":
    // the admin-entered School Year start date (Academic Settings) if set,
    // else Semester 1's start date, else the class's earliest attendance
    // record, else the session record's own creation date.
    private function resolveSessionStart($sessionId, $class_id, $section_id): Carbon
    {
        $session = SchoolSessionModel::find($sessionId);
        if ($session && $session->start_date) {
            return Carbon::parse($session->start_date)->startOfDay();
        }

        $semester = Semester::where('session_id', $sessionId)
            ->orderBy('start_date')
            ->first();
        if ($semester && $semester->start_date) {
            return Carbon::parse($semester->start_date)->startOfDay();
        }

        $earliestAttendance = Attendance::where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->where('session_id', $sessionId)
            ->orderBy('created_at')
            ->first();
        if ($earliestAttendance) {
            return Carbon::parse($earliestAttendance->created_at)->startOfDay();
        }

        if ($session) {
            return Carbon::parse($session->created_at)->startOfDay();
        }

        return Carbon::today();
    }

    /**
     * Class-wide attendance history over a date range.
     * CT: locked to their own class/section. Admin: pick any class/section via query params.
     */
    public function history(Request $request)
    {
        $user = auth()->user();
        $current_school_session_id = $this->getSchoolCurrentSession();

        $class_id   = $request->query('class_id');
        $section_id = $request->query('section_id');

        if ($user->role === 'teacher') {
            $ctAssignments = ClassTeacher::where('teacher_id', $user->id)
                ->where('session_id', $current_school_session_id)
                ->get();

            if ($ctAssignments->isEmpty()) {
                abort(403, 'Only a Class Teacher can view class attendance history.');
            }

            if ($class_id && $section_id) {
                // Requested a specific class — must be one of this teacher's own assignments.
                $ct = $ctAssignments->first(fn($a) => (int)$a->class_id === (int)$class_id && (int)$a->section_id === (int)$section_id);
                if (!$ct) {
                    abort(403, 'You can only view attendance history for your own class.');
                }
            } elseif ($ctAssignments->count() === 1) {
                $class_id   = $ctAssignments->first()->class_id;
                $section_id = $ctAssignments->first()->section_id;
            } else {
                // Multiple classes and none specified — send them to pick one.
                return redirect()->route('attendance.index');
            }
        } elseif (!$class_id) {
            abort(404);
        }

        $today = Carbon::today();
        $range = $request->query('range', '7days');

        if ($request->query('from') && $request->query('to')) {
            $from  = Carbon::parse($request->query('from'))->startOfDay();
            $to    = Carbon::parse($request->query('to'))->startOfDay();
            $range = 'custom';
        } elseif ($range === 'week') {
            $from = $today->copy()->startOfWeek();
            $to   = $today->copy()->endOfWeek();
        } elseif ($range === 'month') {
            $from = $today->copy()->startOfMonth();
            $to   = $today->copy();
        } else {
            $range = '7days';
            $from  = $today->copy()->subDays(6);
            $to    = $today->copy();
        }

        // Cap the range so the grid can't grow unbounded
        if ($from->diffInDays($to) > 62) {
            $from = $to->copy()->subDays(62);
        }
        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        $school_class   = $this->schoolClassRepository->findById($class_id);
        $school_section = $section_id ? $this->sectionRepository->findById($section_id) : null;

        $student_list = $this->userRepository
            ->getAllStudents($current_school_session_id, $class_id, $section_id)
            ->sortBy('roll_number')
            ->values();

        $attendanceRepository = new AttendanceRepository();
        $attendances = $attendanceRepository->getSectionAttendanceRange(
            $class_id, $section_id, $current_school_session_id,
            $from->toDateString(), $to->toDateString()
        );

        // student_id => 'Y-m-d' => status
        $grid = [];
        foreach ($attendances as $att) {
            $grid[$att->student_id][$att->created_at->format('Y-m-d')] = $att->status;
        }

        $dates = [];
        for ($cursor = $from->copy(); $cursor->lte($to); $cursor->addDay()) {
            $dates[] = $cursor->copy();
        }

        return view('attendances.history', [
            'school_class'   => $school_class,
            'school_section' => $school_section,
            'student_list'   => $student_list,
            'dates'          => $dates,
            'grid'           => $grid,
            'from'           => $from,
            'to'             => $to,
            'range'          => $range,
            'class_id'       => $class_id,
            'section_id'     => $section_id,
            'is_ct'          => $user->role === 'teacher',
        ]);
    }

    public function update(Request $request, $attendance_id)
    {
        $user = auth()->user();

        if (!in_array($user->role, ['admin', 'teacher'])) {
            abort(403);
        }

        $attendance = Attendance::findOrFail($attendance_id);

        // Teachers may only edit attendance for their own CT class/section,
        // and only for today or yesterday. Older records must go through Admin.
        if ($user->role === 'teacher') {
            $current_school_session_id = $this->getSchoolCurrentSession();

            $isCt = ClassTeacher::where('teacher_id', $user->id)
                ->where('session_id', $current_school_session_id)
                ->where('class_id', $attendance->class_id)
                ->where('section_id', $attendance->section_id)
                ->exists();
            if (!$isCt) {
                abort(403, 'Only the Class Teacher for this class/section can edit this attendance.');
            }

            $recordDate = Carbon::parse($attendance->created_at)->startOfDay();
            $earliestEditable = Carbon::today()->subDay();
            if ($recordDate->lt($earliestEditable)) {
                abort(403, 'Attendance older than yesterday can only be corrected by an Admin. Please contact Admin.');
            }
        }

        $attendanceRepository = new AttendanceRepository();

        $status = $request->has('present') ? 'on' : 'off';

        $attendanceRepository->updateAttendance(
            $attendance_id,
            $status
        );

        return back()->with('status', 'Attendance updated successfully');
    }


}
