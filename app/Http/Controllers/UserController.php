<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Interfaces\UserInterface;
use App\Interfaces\SectionInterface;
use App\Interfaces\SchoolClassInterface;
use App\Repositories\PromotionRepository;
use App\Http\Requests\StudentStoreRequest;
use App\Http\Requests\TeacherStoreRequest;
use App\Interfaces\SchoolSessionInterface;
use App\Repositories\StudentParentInfoRepository;
use App\Models\ClassTeacher;
use App\Models\SubjectTeacher;

class UserController extends Controller
{
    use SchoolSession;
    protected $userRepository;
    protected $schoolSessionRepository;
    protected $schoolClassRepository;
    protected $schoolSectionRepository;

    public function __construct(UserInterface $userRepository, SchoolSessionInterface $schoolSessionRepository,
    SchoolClassInterface $schoolClassRepository,
    SectionInterface $schoolSectionRepository)
    {
        $this->middleware(function ($request, $next) {
        if (!in_array(auth()->user()->role, ['admin', 'teacher'])) {
            abort(403);
        }
        return $next($request);
    });

        $this->userRepository = $userRepository;
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository = $schoolClassRepository;
        $this->schoolSectionRepository = $schoolSectionRepository;
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  TeacherStoreRequest $request
     * @return \Illuminate\Http\Response
     */
    public function storeTeacher(TeacherStoreRequest $request)
    {
        try {
            $credentials = $this->userRepository->createTeacher($request->validated());

            return back()->with('status',
                'Teacher added successfully! Login credentials — Email: ' . $credentials['email'] . ' / Password: ' . $credentials['password']
            );
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function getStudentList(Request $request) {
        $current_school_session_id = $this->getSchoolCurrentSession();
        $user = auth()->user();

        $class_id   = $request->query('class_id', 0);
        $section_id = $request->query('section_id', 0);

        try {
            $promotionRepository = new PromotionRepository();

            if ($user->role === 'teacher') {
                // 1. CT assignment
                $ctAssignment = ClassTeacher::where('teacher_id', $user->id)
                    ->where('session_id', $current_school_session_id)
                    ->first();

                // 2. Subject teacher classes
                $subjectClassIds = SubjectTeacher::where('teacher_id', $user->id)
                    ->where('session_id', $current_school_session_id)
                    ->pluck('class_id')
                    ->unique()
                    ->toArray();

                $allowedClassIds = $subjectClassIds;
                if ($ctAssignment) {
                    $allowedClassIds[] = $ctAssignment->class_id;
                }
                $allowedClassIds = array_unique($allowedClassIds);

                if (empty($allowedClassIds)) {
                    return view('students.list', [
                        'studentList'    => collect(),
                        'school_classes' => collect(),
                        'teacher_scoped' => true,
                    ]);
                }

                // Classes dropdown: only allowed classes
                $school_classes = $this->schoolClassRepository->getAllBySession($current_school_session_id)
                    ->filter(function ($c) use ($allowedClassIds) {
                        return in_array($c->id, $allowedClassIds);
                    })->values();

                // Student list: always Promotion objects (so view's ->student and ->section work)
                if ($class_id != 0 && in_array($class_id, $allowedClassIds)) {
                    if ($section_id != 0) {
                        $studentList = $promotionRepository->getAll($current_school_session_id, $class_id, $section_id);
                    } else {
                        $studentList = \App\Models\Promotion::with(['student', 'section', 'schoolClass'])
                            ->where('session_id', $current_school_session_id)
                            ->where('class_id', $class_id)
                            ->get();
                    }
                } elseif ($ctAssignment) {
                    // CT with no filter selected: default to their own class+section
                    $studentList = \App\Models\Promotion::with(['student', 'section', 'schoolClass'])
                        ->where('session_id', $current_school_session_id)
                        ->where('class_id', $ctAssignment->class_id)
                        ->where('section_id', $ctAssignment->section_id)
                        ->get();
                } else {
                    // Subject teacher with no filter: show nothing, prompt to select
                    $studentList = collect();
                }

                return view('students.list', [
                    'studentList'    => $studentList,
                    'school_classes' => $school_classes,
                    'teacher_scoped' => true,
                ]);
            }

            // Admin: full access
            $school_classes = $this->schoolClassRepository->getAllBySession($current_school_session_id);

            if ($class_id == 0 || $section_id == 0) {
                $studentList = $this->userRepository->getAllStudentsBySession($current_school_session_id);
            } else {
                $studentList = $this->userRepository->getAllStudents($current_school_session_id, $class_id, $section_id);
            }

            return view('students.list', [
                'studentList'    => $studentList,
                'school_classes' => $school_classes,
                'teacher_scoped' => false,
            ]);

        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }


    public function showStudentProfile($id) {
        $student = $this->userRepository->findStudent($id);

        $current_school_session_id = $this->getSchoolCurrentSession();
        $promotionRepository = new PromotionRepository();
        $promotion_info = $promotionRepository->getPromotionInfoById($current_school_session_id, $id);

        $data = [
            'student'           => $student,
            'promotion_info'    => $promotion_info,
        ];

        return view('students.profile', $data);
    }

    public function showTeacherProfile($id) {
        $teacher = $this->userRepository->findTeacher($id);
        $data = [
            'teacher'   => $teacher,
        ];
        return view('teachers.profile', $data);
    }


    public function createStudent() {
        $current_school_session_id = $this->getSchoolCurrentSession();

        $school_classes = $this->schoolClassRepository->getAllBySession($current_school_session_id);

        $data = [
            'current_school_session_id' => $current_school_session_id,
            'school_classes'            => $school_classes,
        ];

        return view('students.add', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StudentStoreRequest $request
     * @return \Illuminate\Http\Response
     */
    public function storeStudent(StudentStoreRequest $request)
    {
        try {
            $this->userRepository->createStudent($request->validated());

            return back()->with('status', 'Student creation was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function editStudent($student_id) 
    {
        $student = $this->userRepository->findStudent($student_id);
        $studentParentInfoRepository = new StudentParentInfoRepository();
        $parent_info = $studentParentInfoRepository->getParentInfo($student_id);
        $promotionRepository = new PromotionRepository();
        $current_school_session_id = $this->getSchoolCurrentSession();
        $promotion_info = $promotionRepository->getPromotionInfoById($current_school_session_id, $student_id);

        // ── For admission-flow students: parent_info table is empty,
        // source parent data from the admissions record instead.
        $admission = $student->admission ?? null;
        $father_name    = $parent_info->father_name    ?? $admission->father_name    ?? '';
        $mother_name    = $parent_info->mother_name    ?? $admission->mother_name    ?? '';
        $father_phone   = $parent_info->father_phone   ?? $admission->father_phone   ?? $admission->contact_mobile ?? '';
        $mother_phone   = $parent_info->mother_phone   ?? $admission->mother_phone ?? '';
        $parent_address = $parent_info->parent_address ?? $admission->full_address   ?? '';

        return view('students.edit', [
            'student'        => $student,
            'parent_info'    => $parent_info,
            'promotion_info' => $promotion_info,
            // Pre-resolved parent fields safe for both student types
            'f_father_name'    => $father_name,
            'f_mother_name'    => $mother_name,
            'f_father_phone'   => $father_phone,
            'f_mother_phone'   => $mother_phone,
            'f_parent_address' => $parent_address,
            'admission_id'     => $admission ? $admission->id : null,
        ]);
    }

    public function updateStudent(Request $request) {
        try {
            $this->userRepository->updateStudent($request->toArray());

            return back()->with('status', 'Student update was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function editTeacher($teacher_id) {
        $teacher = $this->userRepository->findTeacher($teacher_id);

        $data = [
            'teacher'   => $teacher,
        ];

        return view('teachers.edit', $data);
    }
    public function updateTeacher(Request $request) {
        try {
            $this->userRepository->updateTeacher($request->toArray());

            return back()->with('status', 'Teacher update was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function getTeacherList(){
        $teachers = $this->userRepository->getAllTeachers();

        $data = [
            'teachers' => $teachers,
        ];

        return view('teachers.list', $data);
    }
}
