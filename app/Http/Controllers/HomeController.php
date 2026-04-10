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

        // ── ADMIN / TEACHER DASHBOARD ──
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
        ]);
    }
}
