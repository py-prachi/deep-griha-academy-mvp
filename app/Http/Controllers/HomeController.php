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

            return view('home', [
                'notices'    => $notices,
                'promotion'  => $promotion,
                'present'    => $presentCount,
                'total_days' => $totalCount,
                'isStudent'  => true,
            ]);
        }

        // ── TEACHER DASHBOARD ──
        if ($user->role === 'teacher') {
            $ct = ClassTeacher::with(['schoolClass', 'section'])
                ->where('teacher_id', $user->id)
                ->where('session_id', $current_school_session_id)
                ->first();

            $promotionRepository = new PromotionRepository();
            $students = collect();
            $attendanceToday = collect();
            $ppType = null;
            $marksStatus = null;

            if ($ct) {
                $ppType = PrePrimaryController::getPrePrimaryType($ct->schoolClass->class_name ?? '');

                // Students in CT's class
                $students = $promotionRepository->getAll($current_school_session_id, $ct->class_id, $ct->section_id)
                    ->sortBy('roll_number');

                // Today's attendance for the class
                $attRepo = new AttendanceRepository();
                $attendanceToday = $attRepo->getSectionAttendance($ct->class_id, $ct->section_id, $current_school_session_id)
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
                }
            }

            return view('home', [
                'notices'        => $notices,
                'isStudent'      => false,
                'isTeacher'      => true,
                'ct'             => $ct,
                'ppType'         => $ppType,
                'students'       => $students,
                'attendanceToday'=> $attendanceToday,
                'marksStatus'    => $marksStatus,
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
