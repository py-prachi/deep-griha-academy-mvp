<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\SchoolSession;
use App\Interfaces\UserInterface;
use App\Repositories\NoticeRepository;
use App\Interfaces\SchoolClassInterface;
use App\Interfaces\SchoolSessionInterface;
use App\Repositories\PromotionRepository;
use App\Repositories\AttendanceRepository;
use App\Models\ClassTeacher;
use App\Models\ClassSubject;
use App\Models\Routine;
use App\Models\TimetablePeriod;
use App\Models\StudentTermMark;
use App\Models\PrePrimarySkillGrade;
use App\Http\Controllers\PrePrimaryController;
use Carbon\Carbon;

class HomeController extends Controller
{
    use SchoolSession;
    protected $schoolSessionRepository;
    protected $schoolClassRepository;
    protected $userRepository;

    public function __construct(
        UserInterface $userRepository, SchoolSessionInterface $schoolSessionRepository, SchoolClassInterface $schoolClassRepository)
    {
        $this->userRepository = $userRepository;
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository = $schoolClassRepository;
    }

    public function index()
    {
        $current_school_session_id = $this->getSchoolCurrentSession();
        $user = auth()->user();

        $noticeRepository = new NoticeRepository();
        $notices = $noticeRepository->getAll($current_school_session_id);

        // ── STUDENT DASHBOARD ──
        if ($user->role === 'student') {
            $promotionRepository = new PromotionRepository();
            $promotion = $promotionRepository->getPromotionInfoById($current_school_session_id, $user->id);

            // Attendance summary
            $attendanceRepository = new AttendanceRepository();
            $attendances = $attendanceRepository->getStudentAttendance($current_school_session_id, $user->id);
            $presentCount = $attendances->where('status', 'on')->count();
            $totalCount   = $attendances->count();

            // Today's timetable
            $todayWeekday = Carbon::today()->isoWeekday(); // 1=Mon … 6=Sat
            $todayRoutines = collect();
            $todayPeriods  = collect();
            if ($promotion) {
                $todayRoutines = Routine::with(['period', 'course.subject'])
                    ->where('class_id', $promotion->class_id)
                    ->where('section_id', $promotion->section_id)
                    ->where('session_id', $current_school_session_id)
                    ->where('weekday', $todayWeekday)
                    ->get()
                    ->keyBy('period_id');
                $todayPeriods = TimetablePeriod::getForDay($todayWeekday);
            }

            return view('home', [
                'notices'       => $notices,
                'promotion'     => $promotion,
                'present'       => $presentCount,
                'total_days'    => $totalCount,
                'isStudent'     => true,
                'todayRoutines' => $todayRoutines,
                'todayPeriods'  => $todayPeriods,
                'todayWeekday'  => $todayWeekday,
            ]);
        }

        // ── TEACHER DASHBOARD ──
        if ($user->role === 'teacher') {
            // A teacher may be CT for more than one class (e.g. one teacher
            // covering both Nursery and Lower KG) — build the dashboard data
            // per assignment so nothing gets silently dropped.
            $ctAssignments = ClassTeacher::with(['schoolClass', 'section'])
                ->where('teacher_id', $user->id)
                ->where('session_id', $current_school_session_id)
                ->get();

            $promotionRepository = new PromotionRepository();
            $attRepo = new AttendanceRepository();

            $ppTypes          = [];
            $studentsByCt     = [];
            $attendanceByCt   = [];
            $marksStatusByCt  = [];

            foreach ($ctAssignments as $ct) {
                $ppType = PrePrimaryController::getPrePrimaryType($ct->schoolClass->class_name ?? '');
                $ppTypes[$ct->id] = $ppType;

                // Students in this class
                $students = $promotionRepository->getAllActive($current_school_session_id, $ct->class_id, $ct->section_id)
                    ->sortBy('roll_number');
                $studentsByCt[$ct->id] = $students;

                // Today's attendance for the class
                $attendanceByCt[$ct->id] = $attRepo->getSectionAttendance($ct->class_id, $ct->section_id, $current_school_session_id)
                    ->keyBy('student_id');

                // Marks entry status (only for Class 1-8)
                if (!$ppType) {
                    $subjects = ClassSubject::with('subject')
                        ->where('class_id', $ct->class_id)
                        ->where('session_id', $current_school_session_id)
                        ->get()->pluck('subject')->filter();

                    $studentIds = $students->pluck('student_id')->toArray();
                    $totalStudents = count($studentIds);

                    $allMarks = StudentTermMark::where('class_id', $ct->class_id)
                        ->where('section_id', $ct->section_id)
                        ->where('session_id', $current_school_session_id)
                        ->get();

                    $marksStatus = [];
                    foreach ([1, 2] as $term) {
                        $termMarks = $allMarks->where('term', $term);
                        $enteredStudents = $termMarks->pluck('student_id')->unique()->count();
                        $marksStatus[$term] = [
                            'entered'  => $enteredStudents,
                            'total'    => $totalStudents,
                            'subjects' => $subjects->count(),
                        ];
                    }
                    $marksStatusByCt[$ct->id] = $marksStatus;
                }
            }

            return view('home', [
                'notices'         => $notices,
                'isStudent'       => false,
                'isTeacher'       => true,
                'ctAssignments'   => $ctAssignments,
                'ppTypes'         => $ppTypes,
                'studentsByCt'    => $studentsByCt,
                'attendanceByCt'  => $attendanceByCt,
                'marksStatusByCt' => $marksStatusByCt,
            ]);
        }

        // ── ADMIN DASHBOARD ──
        $classCount   = $this->schoolClassRepository->getAllBySession($current_school_session_id)->count();
        $studentCount = $this->userRepository->getAllStudentsBySessionCount($current_school_session_id);
        $promotionRepository = new PromotionRepository();
        $maleStudentsBySession = $promotionRepository->getMaleStudentsBySessionCount($current_school_session_id);
        $teacherCount = $this->userRepository->getAllTeachers()->count();

        return view('home', [
            'classCount'            => $classCount,
            'studentCount'          => $studentCount,
            'teacherCount'          => $teacherCount,
            'notices'               => $notices,
            'maleStudentsBySession' => $maleStudentsBySession,
            'isStudent'             => false,
            'isTeacher'             => false,
        ]);
    }
}
