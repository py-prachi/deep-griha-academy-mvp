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
use App\Models\SubjectTeacher;
use App\Models\ReportCardPublished;
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

            $ppTypes             = [];
            $studentsByCt        = [];
            $attendanceByCt      = [];
            $marksStatusByCt     = [];
            $subjectTeachersByCt = [];
            $activeTermByCt      = [];

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

                    // Subject teachers for this class — who teaches what, plus a quick
                    // entered/total glance for whichever term is still open (not yet
                    // published), so the CT can see at a glance who to follow up with by
                    // name instead of just the aggregate count above.
                    $publishedTermsForClass = ReportCardPublished::where('class_id', $ct->class_id)
                        ->where('section_id', $ct->section_id)
                        ->where('session_id', $current_school_session_id)
                        ->pluck('term')
                        ->toArray();
                    $activeTerm = !in_array(1, $publishedTermsForClass)
                        ? 1
                        : (!in_array(2, $publishedTermsForClass) ? 2 : null);
                    $activeTermByCt[$ct->id] = $activeTerm;

                    $subjectTeacherAssignments = SubjectTeacher::with(['teacher'])
                        ->where('class_id', $ct->class_id)
                        ->where('section_id', $ct->section_id)
                        ->where('session_id', $current_school_session_id)
                        ->get()
                        ->filter(fn($st) => $st->teacher);

                    $subjectTeachersByCt[$ct->id] = $subjects->map(function ($subject) use ($subjectTeacherAssignments, $allMarks, $activeTerm, $totalStudents, $studentIds) {
                        $assignment = $subjectTeacherAssignments->firstWhere('subject_id', $subject->id);
                        // Only count entries among currently-active students — a mark
                        // row can outlive a student leaving the section (exit, transfer,
                        // promotion mid-year), and that history shouldn't inflate this
                        // count past the current roster size.
                        $entered = $activeTerm
                            ? $allMarks->where('subject_id', $subject->id)->where('term', $activeTerm)
                                ->pluck('student_id')->unique()
                                ->intersect($studentIds)->count()
                            : null;
                        return (object) [
                            'subject_name' => $subject->name,
                            'teacher_name' => $assignment ? trim($assignment->teacher->first_name . ' ' . $assignment->teacher->last_name) : null,
                            'entered'      => $entered,
                            'total'        => $totalStudents,
                        ];
                    })->values();
                }
            }

            // Subject-teacher assignments across ALL classes, regardless of CT status
            // elsewhere — the cards above only cover a teacher's own CT class(es), which
            // left subject-teaching work in other classes invisible, and left a teacher
            // who isn't CT of anything with an empty dashboard entirely.
            $subjectAssignments = SubjectTeacher::with(['subject', 'schoolClass', 'section'])
                ->where('teacher_id', $user->id)
                ->where('session_id', $current_school_session_id)
                ->get()
                ->filter(fn($a) => $a->subject && $a->schoolClass)
                ->sortBy(fn($a) => ($a->schoolClass->class_name ?? '') . '-' . (optional($a->section)->section_name ?? ''))
                ->values();

            // Today's timetable across those assignments — same class_id+subject_id to
            // ClassSubject(course) resolution as TimetableController::teacherView(),
            // just narrowed to today's weekday for a quick glance here.
            $todayWeekdayForTeacher = Carbon::today()->isoWeekday();
            $todayTeacherRoutines   = collect();
            $todayTeacherPeriods    = collect();
            if ($subjectAssignments->isNotEmpty()) {
                $classSubjectIds = [];
                foreach ($subjectAssignments as $assignment) {
                    $cs = ClassSubject::where('class_id', $assignment->class_id)
                        ->where('subject_id', $assignment->subject_id)
                        ->where('session_id', $current_school_session_id)
                        ->first();
                    if ($cs) {
                        $classSubjectIds[] = $cs->id;
                    }
                }
                if (!empty($classSubjectIds)) {
                    $todayTeacherRoutines = Routine::with(['period', 'course.subject', 'schoolClass', 'section'])
                        ->whereIn('course_id', $classSubjectIds)
                        ->where('session_id', $current_school_session_id)
                        ->where('weekday', $todayWeekdayForTeacher)
                        ->get()
                        ->keyBy('period_id');
                    $todayTeacherPeriods = TimetablePeriod::getForDay($todayWeekdayForTeacher);
                }
            }

            return view('home', [
                'notices'                => $notices,
                'isStudent'              => false,
                'isTeacher'              => true,
                'ctAssignments'          => $ctAssignments,
                'ppTypes'                => $ppTypes,
                'studentsByCt'           => $studentsByCt,
                'attendanceByCt'         => $attendanceByCt,
                'marksStatusByCt'        => $marksStatusByCt,
                'subjectTeachersByCt'    => $subjectTeachersByCt,
                'activeTermByCt'         => $activeTermByCt,
                'subjectAssignments'     => $subjectAssignments,
                'todayTeacherRoutines'   => $todayTeacherRoutines,
                'todayTeacherPeriods'    => $todayTeacherPeriods,
                'todayWeekdayForTeacher' => $todayWeekdayForTeacher,
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
