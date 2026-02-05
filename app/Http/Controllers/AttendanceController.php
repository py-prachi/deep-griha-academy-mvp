<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
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
use Carbon\Carbon;

class AttendanceController extends Controller
{
    use SchoolSession;
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
        $this->middleware(['can:view attendances']);

        $this->userRepository = $userRepository;
        $this->academicSettingRepository = $academicSettingRepository;
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository = $schoolClassRepository;
        $this->sectionRepository = $sectionRepository;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return back();
    }

    /**
     * Show the form for creating a new resource.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
public function create(Request $request)
{
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


public function showStudentAttendance(Request $request, $id)
{
    if (auth()->user()->role === "student" && auth()->user()->id != $id) {
        abort(404);
    }

    $current_school_session_id = $this->getSchoolCurrentSession();
    $selected_date = $request->query('date');

    $attendanceRepository = new AttendanceRepository();

    // ✅ FULL HISTORY — unchanged
    $attendances = $attendanceRepository
        ->getStudentAttendance($current_school_session_id, $id);

    // ✅ Attendance for selected date
    $attendance_for_date = null;
    if ($selected_date) {
        $attendance_for_date = Attendance::where('student_id', $id)
            ->where('session_id', $current_school_session_id)
            ->whereDate('created_at', $selected_date)
            ->first();
    }

    $student = $this->userRepository->findStudent($id);

    // 🔹 NEW: get attendance type
    $academic_setting = $this->academicSettingRepository->getAcademicSetting();
    $attendance_type = $academic_setting->attendance_type ?? 'section';

    // ✅ Derive class / section / course from existing attendance records
    $firstAttendance = $attendances->first();

    $class_id = $firstAttendance ? (int) $firstAttendance->class_id : 0;
    $section_id = $firstAttendance ? (int) $firstAttendance->section_id : 0;
    $course_id = $firstAttendance ? (int) $firstAttendance->course_id : 0;


    return view('attendances.attendance', [
        'attendances'               => $attendances,
        'student'                   => $student,
        'selected_date'             => $selected_date,
        'attendance_for_date'       => $attendance_for_date,
        'current_school_session_id' => $current_school_session_id,

        // 🔹 NEW: pass IDs + mode
        'attendance_type' => $attendance_type,
        'class_id'        => $class_id,
        'section_id'      => $section_id,
        'course_id'       => $course_id,
    ]);
}



    public function update(Request $request, $attendance_id)
{
    if (!in_array(auth()->user()->role, ['admin', 'teacher'])) {
    abort(403);
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
