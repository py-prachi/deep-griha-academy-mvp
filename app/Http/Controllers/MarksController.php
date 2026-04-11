<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Models\Subject;
use App\Models\ClassSubject;
use App\Models\ClassTeacher;
use App\Models\SubjectTeacher;
use App\Models\StudentTermMark;
use App\Models\MarkExamDate;
use App\Models\ReportCardPublished;
use App\Models\StudentObservation;
use App\Repositories\PromotionRepository;
use App\Interfaces\SchoolSessionInterface;
use App\Interfaces\SchoolClassInterface;
use App\Interfaces\SectionInterface;
use Carbon\Carbon;

class MarksController extends Controller
{
    use SchoolSession;

    protected $schoolSessionRepository;
    protected $schoolClassRepository;
    protected $sectionRepository;

    // Max marks per component per class group
    // Key: class_group (derived from class_name number)
    // Components: oral_internal, activity_internal, test, hw, oral_written, activity_written, writing
    const CLASS_GROUP_CONFIG = [
        '1-2' => [
            'oral_internal'      => 15,
            'activity_internal'  => 10,
            'test'               => 15,
            'hw'                 => 10,
            'internal_max'       => 50,
            'oral_written'       => 5,
            'activity_written'   => 5,
            'writing'            => 40,
            'written_max'        => 50,
            'grand_max'          => 100,
        ],
        '3-4' => [
            'oral_internal'      => 15,
            'activity_internal'  => 15,
            'test'               => 15,
            'hw'                 => 15,
            'internal_max'       => 60,
            'oral_written'       => 5,
            'activity_written'   => 5,
            'writing'            => 30,
            'written_max'        => 40,
            'grand_max'          => 100,
        ],
        '5-6' => [
            'oral_internal'      => 15,
            'activity_internal'  => 10,
            'test'               => 15,
            'hw'                 => 10,
            'internal_max'       => 50,
            'oral_written'       => 5,
            'activity_written'   => 5,
            'writing'            => 40,
            'written_max'        => 50,
            'grand_max'          => 100,
        ],
        '7-8' => [
            'oral_internal'      => 5,
            'activity_internal'  => 10,
            'test'               => 15,
            'hw'                 => 10,
            'internal_max'       => 40,
            'oral_written'       => 5,
            'activity_written'   => 5,
            'writing'            => 50,
            'written_max'        => 60,
            'grand_max'          => 100,
        ],
    ];

    // Grading scale
    const GRADE_SCALE = [
        ['min' => 91, 'max' => 100, 'grade' => 'A1'],
        ['min' => 81, 'max' => 90,  'grade' => 'A2'],
        ['min' => 71, 'max' => 80,  'grade' => 'B1'],
        ['min' => 61, 'max' => 70,  'grade' => 'B2'],
        ['min' => 51, 'max' => 60,  'grade' => 'C1'],
        ['min' => 41, 'max' => 50,  'grade' => 'C2'],
        ['min' => 33, 'max' => 40,  'grade' => 'D'],
        ['min' => 0,  'max' => 32,  'grade' => 'E'],
    ];

    public function __construct(
        SchoolSessionInterface $schoolSessionRepository,
        SchoolClassInterface $schoolClassRepository,
        SectionInterface $sectionRepository
    ) {
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository   = $schoolClassRepository;
        $this->sectionRepository       = $sectionRepository;

        $this->middleware(function ($request, $next) {
            if (!in_array(auth()->user()->role, ['admin', 'teacher'])) {
                abort(403);
            }
            return $next($request);
        })->except(['reportCard']);
    }

    /**
     * Derive class group key from class name.
     * e.g. "Class 1", "Std 2", "1" → '1-2'
     */
    public static function getClassGroup($class_name)
    {
        preg_match('/(\d+)/', $class_name, $m);
        $n = isset($m[1]) ? (int)$m[1] : 0;
        if ($n >= 1 && $n <= 2) return '1-2';
        if ($n >= 3 && $n <= 4) return '3-4';
        if ($n >= 5 && $n <= 6) return '5-6';
        if ($n >= 7 && $n <= 8) return '7-8';
        return '7-8'; // fallback
    }

    public static function calcGrade($percentage)
    {
        foreach (self::GRADE_SCALE as $row) {
            if ($percentage >= $row['min'] && $percentage <= $row['max']) {
                return $row['grade'];
            }
        }
        return 'E';
    }

    /**
     * Subject teacher: select subject + class + section + term to enter marks.
     * Admin: can select any combination.
     */
    public function index(Request $request)
    {
        $session_id = $this->getSchoolCurrentSession();
        $user       = auth()->user();

        $ct = null;
        $assignedSubjects = collect();
        $subjectAssignments = collect(); // for subject teachers: subject→class/section map

        if ($user->role === 'teacher') {
            // Check if CT
            $ct = ClassTeacher::with(['schoolClass', 'section'])
                ->where('teacher_id', $user->id)
                ->where('session_id', $session_id)
                ->first();

            // Subject teacher assignments (always load — teacher may be BOTH CT and subject teacher)
            $subjectRows = SubjectTeacher::with(['subject', 'schoolClass', 'section'])
                ->where('teacher_id', $user->id)
                ->where('session_id', $session_id)
                ->get();

            // Build subject assignments map for subject-teacher classes
            foreach ($subjectRows as $row) {
                if (!$subjectAssignments->has($row->subject_id)) {
                    $subjectAssignments->put($row->subject_id, collect());
                }
                $className   = $row->schoolClass ? $row->schoolClass->class_name : 'Class';
                $sectionName = $row->section     ? $row->section->section_name    : '';
                $subjectAssignments->get($row->subject_id)->push([
                    'class_id'   => $row->class_id,
                    'section_id' => $row->section_id,
                    'label'      => $className . ' — ' . $sectionName,
                ]);
            }

            if ($ct) {
                // CT subjects: all subjects for their class
                $ctSubjects = ClassSubject::with('subject')
                    ->where('class_id', $ct->class_id)
                    ->where('session_id', $session_id)
                    ->get()
                    ->pluck('subject')
                    ->filter()
                    ->sortBy('sort_order')
                    ->values();

                // Subject-teacher subjects for OTHER classes (exclude CT class)
                $otherSubjectRows = $subjectRows->filter(function ($r) use ($ct) {
                    return $r->class_id != $ct->class_id;
                });
                $otherSubjects = $otherSubjectRows->pluck('subject')->unique('id')->filter()->sortBy('sort_order')->values();

                $assignedSubjects = $ctSubjects; // primary list for CT panel
            } else {
                // Pure subject teacher
                $assignedSubjects = $subjectRows->pluck('subject')->unique('id')->filter()->sortBy('sort_order')->values();
                $ct = null;
                $otherSubjects = collect();
            }
        } else {
            $assignedSubjects = Subject::where('is_active', true)->orderBy('sort_order')->get();
            $otherSubjects    = collect();
        }

        $classes = $this->schoolClassRepository->getAllBySession($session_id);

        return view('marks.index', [
            'subjects'           => $assignedSubjects,
            'classes'            => $classes,
            'session_id'         => $session_id,
            'ct'                 => $ct,
            'subjectAssignments' => $subjectAssignments,
            'otherSubjects'      => $otherSubjects ?? collect(),
            'userRole'           => $user->role,
        ]);
    }

    /**
     * Mark entry form: subject + class + section + term selected.
     */
    public function entry(Request $request)
    {
        $session_id = $this->getSchoolCurrentSession();
        $user       = auth()->user();

        $subject_id = $request->query('subject_id');
        $class_id   = $request->query('class_id');
        $section_id = $request->query('section_id');
        $term       = $request->query('term', 1);

        if (!$subject_id || !$class_id || !$section_id) {
            return redirect()->route('marks.index');
        }

        // Authorization: teacher must be CT for this class/section OR assigned as subject teacher
        if ($user->role === 'teacher') {
            $isCT = ClassTeacher::where('teacher_id', $user->id)
                ->where('class_id', $class_id)
                ->where('section_id', $section_id)
                ->where('session_id', $session_id)
                ->exists();

            if (!$isCT) {
                $isSubjectTeacher = SubjectTeacher::where('teacher_id', $user->id)
                    ->where('subject_id', $subject_id)
                    ->where('class_id', $class_id)
                    ->where('section_id', $section_id)
                    ->where('session_id', $session_id)
                    ->exists();
                if (!$isSubjectTeacher) {
                    abort(403, 'You are not assigned to this subject for this class.');
                }
            }
        }

        $subject    = Subject::findOrFail($subject_id);
        $schoolClass = $this->schoolClassRepository->findById($class_id);
        $section    = $this->sectionRepository->findById($section_id);

        $classGroup = self::getClassGroup($schoolClass->class_name);
        $config     = self::CLASS_GROUP_CONFIG[$classGroup];

        // Students in this class/section
        $promotionRepository = new PromotionRepository();
        $promotions = $promotionRepository->getAll($session_id, $class_id, $section_id);

        // Existing marks keyed by student_id
        $existingMarks = StudentTermMark::where('subject_id', $subject_id)
            ->where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->where('session_id', $session_id)
            ->where('term', $term)
            ->get()
            ->keyBy('student_id');

        // Existing exam dates keyed by component
        $existingDates = MarkExamDate::where('subject_id', $subject_id)
            ->where('class_id', $class_id)
            ->where('session_id', $session_id)
            ->where('term', $term)
            ->get()
            ->keyBy('component');

        return view('marks.entry', [
            'subject'       => $subject,
            'schoolClass'   => $schoolClass,
            'section'       => $section,
            'term'          => $term,
            'session_id'    => $session_id,
            'config'        => $config,
            'promotions'    => $promotions,
            'existingMarks' => $existingMarks,
            'existingDates' => $existingDates,
        ]);
    }

    /**
     * Save marks for a subject + class + section + term.
     */
    public function store(Request $request)
    {
        $session_id = $this->getSchoolCurrentSession();
        $user       = auth()->user();

        $subject_id = $request->input('subject_id');
        $class_id   = $request->input('class_id');
        $section_id = $request->input('section_id');
        $term       = $request->input('term');

        $subject     = Subject::findOrFail($subject_id);
        $schoolClass = $this->schoolClassRepository->findById($class_id);
        $classGroup  = self::getClassGroup($schoolClass->class_name);
        $config      = self::CLASS_GROUP_CONFIG[$classGroup];

        // Save exam dates (one per component, shared across all students)
        $examDates = $request->input('exam_dates', []);
        $dateComponents = $subject->mark_type === 'marks'
            ? ['oral_internal', 'activity_internal', 'test', 'hw', 'oral_written', 'activity_written', 'writing']
            : ['exam'];

        foreach ($dateComponents as $component) {
            if (!empty($examDates[$component])) {
                MarkExamDate::updateOrCreate(
                    [
                        'subject_id' => $subject_id,
                        'class_id'   => $class_id,
                        'session_id' => $session_id,
                        'term'       => $term,
                        'component'  => $component,
                    ],
                    ['exam_date' => $examDates[$component]]
                );
            }
        }

        $marks_data = $request->input('marks', []);

        foreach ($marks_data as $student_id => $row) {
            $grade = $row['grade'] ?? null; // for grade_only subjects

            if ($subject->mark_type === 'marks') {
                // Per-component absent flags: marks[student_id][absent][test] = 1
                $absentFlags = isset($row['absent']) && is_array($row['absent']) ? $row['absent'] : [];
                $absentComponents = array_keys(array_filter($absentFlags));

                // For absent components: value = 0. For present: use submitted value.
                $allComponents = ['oral_internal','activity_internal','test','hw','oral_written','activity_written','writing'];
                $vals = [];
                foreach ($allComponents as $comp) {
                    if (in_array($comp, $absentComponents)) {
                        $vals[$comp] = 0;
                    } else {
                        $vals[$comp] = isset($row[$comp]) && $row[$comp] !== '' ? (float)$row[$comp] : null;
                    }
                }

                // Skip students where nothing was entered and no absent flags set
                $hasAnyValue = !empty($absentComponents) || count(array_filter($vals, function($v) { return $v !== null; })) > 0;
                if (!$hasAnyValue) {
                    continue;
                }

                $internal = ($vals['oral_internal'] ?? 0) + ($vals['activity_internal'] ?? 0)
                          + ($vals['test'] ?? 0) + ($vals['hw'] ?? 0);
                $written  = ($vals['oral_written'] ?? 0) + ($vals['activity_written'] ?? 0)
                          + ($vals['writing'] ?? 0);
                $grand    = $internal + $written;
                $pct      = $config['grand_max'] > 0 ? ($grand / $config['grand_max'] * 100) : 0;

                $data = [
                    'oral_internal'      => $vals['oral_internal'],
                    'activity_internal'  => $vals['activity_internal'],
                    'test'               => $vals['test'],
                    'hw'                 => $vals['hw'],
                    'oral_written'       => $vals['oral_written'],
                    'activity_written'   => $vals['activity_written'],
                    'writing'            => $vals['writing'],
                    'internal_total'     => $internal,
                    'written_total'      => $written,
                    'grand_total'        => $grand,
                    'grade'              => self::calcGrade($pct),
                    'absent_components'  => !empty($absentComponents) ? $absentComponents : null,
                ];
            } else {
                // grade_only subject — single absent flag for the whole assessment
                $isAbsent = !empty($row['absent']) && !is_array($row['absent']);
                // Skip if no grade entered and not absent
                if (!$isAbsent && ($grade === null || $grade === '')) {
                    continue;
                }
                $data = [
                    'grade'             => $isAbsent ? 'AB' : ($grade ?? null),
                    'absent_components' => $isAbsent ? ['exam'] : null,
                ];
            }

            $data['entered_by'] = $user->id;
            $data['entered_at'] = Carbon::now();

            StudentTermMark::updateOrCreate(
                [
                    'student_id' => $student_id,
                    'subject_id' => $subject_id,
                    'session_id' => $session_id,
                    'term'       => $term,
                ],
                array_merge($data, [
                    'class_id'   => $class_id,
                    'section_id' => $section_id,
                ])
            );
        }

        return back()->with('status', 'Marks saved successfully!');
    }

    /**
     * CT review dashboard: see all subjects and mark entry status for their class/section.
     */
    public function review(Request $request)
    {
        $session_id = $this->getSchoolCurrentSession();
        $user       = auth()->user();

        // Determine class/section to review
        if ($user->role === 'teacher') {
            $ct = ClassTeacher::where('teacher_id', $user->id)
                ->where('session_id', $session_id)
                ->first();
            if (!$ct) {
                return view('marks.review', ['error' => 'You are not assigned as a Class Teacher.']);
            }
            $class_id   = $ct->class_id;
            $section_id = $ct->section_id;
        } else {
            $class_id   = $request->query('class_id');
            $section_id = $request->query('section_id');
            if (!$class_id || !$section_id) {
                $classes = $this->schoolClassRepository->getAllBySession($session_id);
                return view('marks.review', [
                    'classes'    => $classes,
                    'session_id' => $session_id,
                    'pick'       => true,
                ]);
            }
        }

        $schoolClass = $this->schoolClassRepository->findById($class_id);
        $section     = $this->sectionRepository->findById($section_id);

        // All subjects assigned to this class
        $subjects = ClassSubject::with('subject')
            ->where('class_id', $class_id)
            ->where('session_id', $session_id)
            ->get()
            ->pluck('subject')
            ->filter();

        // Students in this section
        $promotionRepository = new PromotionRepository();
        $promotions = $promotionRepository->getAll($session_id, $class_id, $section_id);
        $studentCount = $promotions->count();
        $studentIds = $promotions->pluck('student_id')->toArray();

        // Load all marks for this class/section/session at once
        $allMarks = StudentTermMark::where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->where('session_id', $session_id)
            ->get()
            ->groupBy(function ($m) {
                return $m->subject_id . '-' . $m->term . '-' . $m->student_id;
            });

        $marksComponents = ['oral_internal','activity_internal','test','hw','oral_written','activity_written','writing'];

        // Build status grid: subject_id → term → [complete, partial, total, status]
        // A student is "fully complete" if they have a saved row and every component is
        // either filled (numeric) or marked absent. A student is "partial" if a row exists
        // but some components are still null. No row = not started for that student.
        $statusGrid = [];
        foreach ([1, 2] as $term) {
            foreach ($subjects as $subject) {
                $isGradeOnly   = $subject->mark_type !== 'marks';
                $fullCount     = 0;
                $partialCount  = 0;

                foreach ($studentIds as $sid) {
                    $markCollection = $allMarks->get($subject->id . '-' . $term . '-' . $sid);
                    $mark = $markCollection ? $markCollection->first() : null;

                    if (!$mark) {
                        continue; // no row saved — not started for this student
                    }

                    if ($isGradeOnly) {
                        if ($mark->grade) {
                            $fullCount++;
                        } else {
                            $partialCount++;
                        }
                    } else {
                        $allFilled = true;
                        foreach ($marksComponents as $comp) {
                            $isAbsent = $mark->isComponentAbsent($comp);
                            $val = $mark->{$comp};
                            if (!$isAbsent && $val === null) {
                                $allFilled = false;
                                break;
                            }
                        }
                        if ($allFilled) {
                            $fullCount++;
                        } else {
                            $partialCount++;
                        }
                    }
                }

                $savedCount = $fullCount + $partialCount;

                if ($savedCount === 0) {
                    $status = 'not_started';
                } elseif ($fullCount === $studentCount) {
                    $status = 'complete';
                } else {
                    $status = 'partial';
                }

                $statusGrid[$subject->id][$term] = [
                    'status'  => $status,
                    'full'    => $fullCount,
                    'partial' => $partialCount,
                    'total'   => $studentCount,
                ];
            }
        }

        // Which terms are published for this class/section/session?
        $publishedTerms = ReportCardPublished::where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->where('session_id', $session_id)
            ->pluck('term')
            ->toArray();

        return view('marks.review', [
            'schoolClass'    => $schoolClass,
            'section'        => $section,
            'subjects'       => $subjects,
            'statusGrid'     => $statusGrid,
            'session_id'     => $session_id,
            'class_id'       => $class_id,
            'section_id'     => $section_id,
            'studentCount'   => $studentCount,
            'promotions'     => $promotions,
            'publishedTerms' => $publishedTerms,
            'userRole'       => $user->role,
            'classes'        => $user->role === 'admin'
                ? $this->schoolClassRepository->getAllBySession($session_id)
                : collect(),
        ]);
    }

    /**
     * Student's own report card — all subjects, both terms.
     */
    public function reportCard()
    {
        $user       = auth()->user();
        $session_id = $this->getSchoolCurrentSession();

        // Only students (or admin/teacher viewing their own — guard here)
        if ($user->role !== 'student') {
            abort(403);
        }

        $promotionRepo = new PromotionRepository();
        $promotion     = $promotionRepo->getPromotionInfoById($session_id, $user->id);

        if (!$promotion) {
            return view('marks.report-card', ['error' => 'You are not assigned to a class for the current session.']);
        }

        $class_id   = $promotion->class_id;
        $section_id = $promotion->section_id;

        $schoolClass = $this->schoolClassRepository->findById($class_id);
        $classGroup  = self::getClassGroup($schoolClass->class_name);
        $config      = self::CLASS_GROUP_CONFIG[$classGroup];

        // All subjects for this class
        $subjects = ClassSubject::with('subject')
            ->where('class_id', $class_id)
            ->where('session_id', $session_id)
            ->get()
            ->pluck('subject')
            ->filter()
            ->sortBy('name');

        // All marks for this student
        $marks = StudentTermMark::where('student_id', $user->id)
            ->where('session_id', $session_id)
            ->get()
            ->groupBy('subject_id');

        // Which terms are published?
        $publishedTerms = ReportCardPublished::where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->where('session_id', $session_id)
            ->pluck('term')
            ->toArray();

        // Observations for this student
        $observations = StudentObservation::where('student_id', $user->id)
            ->where('session_id', $session_id)
            ->get()
            ->keyBy('term');

        return view('marks.report-card', [
            'student'        => $user,
            'promotion'      => $promotion,
            'schoolClass'    => $schoolClass,
            'subjects'       => $subjects,
            'marks'          => $marks,
            'config'         => $config,
            'publishedTerms' => $publishedTerms,
            'observations'   => $observations,
        ]);
    }

    /**
     * Admin: toggle publish for a term.
     */
    public function publishTerm(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $class_id   = $request->input('class_id');
        $section_id = $request->input('section_id');
        $term       = $request->input('term');
        $session_id = $this->getSchoolCurrentSession();

        $existing = ReportCardPublished::where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->where('session_id', $session_id)
            ->where('term', $term)
            ->first();

        if ($existing) {
            $existing->delete();
            $msg = "Term $term report cards unpublished.";
        } else {
            ReportCardPublished::create([
                'class_id'     => $class_id,
                'section_id'   => $section_id,
                'session_id'   => $session_id,
                'term'         => $term,
                'published_at' => \Carbon\Carbon::now(),
                'published_by' => auth()->id(),
            ]);
            $msg = "Term $term report cards published! Students can now view their marks.";
        }

        return redirect()->route('marks.review', [
            'class_id'   => $class_id,
            'section_id' => $section_id,
        ])->with('status', $msg);
    }

    /**
     * CT only: observations entry form (all students for a term).
     */
    public function observationsForm(Request $request)
    {
        $session_id = $this->getSchoolCurrentSession();
        $user       = auth()->user();
        $term       = $request->query('term', 1);

        // Only class teachers may enter remarks, not admin
        if ($user->role !== 'teacher') {
            abort(403, 'Only the Class Teacher can enter remarks.');
        }

        $ct = ClassTeacher::where('teacher_id', $user->id)
            ->where('session_id', $session_id)
            ->first();
        if (!$ct) {
            abort(403, 'You are not assigned as a Class Teacher.');
        }
        $class_id   = $ct->class_id;
        $section_id = $ct->section_id;

        $schoolClass = $this->schoolClassRepository->findById($class_id);
        $section     = $this->sectionRepository->findById($section_id);

        $promotionRepo = new PromotionRepository();
        $promotions    = $promotionRepo->getAll($session_id, $class_id, $section_id);

        $existing = StudentObservation::where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->where('session_id', $session_id)
            ->where('term', $term)
            ->get()
            ->keyBy('student_id');

        return view('marks.observations', [
            'schoolClass' => $schoolClass,
            'section'     => $section,
            'promotions'  => $promotions,
            'existing'    => $existing,
            'term'        => $term,
            'class_id'    => $class_id,
            'section_id'  => $section_id,
            'session_id'  => $session_id,
        ]);
    }

    /**
     * CT/Admin: save observations for all students.
     */
    public function saveObservation(Request $request)
    {
        $session_id = $this->getSchoolCurrentSession();
        $user       = auth()->user();

        if ($user->role !== 'teacher') {
            abort(403, 'Only the Class Teacher can save remarks.');
        }

        $class_id   = $request->input('class_id');
        $section_id = $request->input('section_id');
        $term       = $request->input('term');
        $remarks    = $request->input('remarks', []);

        foreach ($remarks as $student_id => $remark) {
            if (trim($remark) === '') {
                continue;
            }
            StudentObservation::updateOrCreate(
                [
                    'student_id' => $student_id,
                    'session_id' => $session_id,
                    'term'       => $term,
                ],
                [
                    'class_id'   => $class_id,
                    'section_id' => $section_id,
                    'remarks'    => trim($remark),
                    'created_by' => $user->id,
                ]
            );
        }

        return redirect()->route('marks.observations', [
            'class_id'   => $class_id,
            'section_id' => $section_id,
            'term'       => $term,
        ])->with('status', 'Observations saved!');
    }

    /**
     * Admin/CT: printable report card for one student.
     */
    public function printReportCard(Request $request, $student_id)
    {
        $session_id = $this->getSchoolCurrentSession();
        $user       = auth()->user();

        $promotionRepo = new PromotionRepository();
        $promotion     = $promotionRepo->getPromotionInfoById($session_id, $student_id);

        if (!$promotion) {
            abort(404, 'Student not found in current session.');
        }

        $class_id   = $promotion->class_id;
        $section_id = $promotion->section_id;

        // CT: must be assigned to this class/section
        if ($user->role === 'teacher') {
            $isCT = ClassTeacher::where('teacher_id', $user->id)
                ->where('class_id', $class_id)
                ->where('section_id', $section_id)
                ->where('session_id', $session_id)
                ->exists();
            if (!$isCT) {
                abort(403);
            }
        }

        $student     = \App\Models\User::findOrFail($student_id);
        $schoolClass = $this->schoolClassRepository->findById($class_id);
        $section     = $this->sectionRepository->findById($section_id);
        $classGroup  = self::getClassGroup($schoolClass->class_name);
        $config      = self::CLASS_GROUP_CONFIG[$classGroup];

        $subjects = ClassSubject::with('subject')
            ->where('class_id', $class_id)
            ->where('session_id', $session_id)
            ->get()
            ->pluck('subject')
            ->filter()
            ->sortBy('sort_order')
            ->values();

        $marks = StudentTermMark::where('student_id', $student_id)
            ->where('session_id', $session_id)
            ->get()
            ->groupBy('subject_id');

        $observations = StudentObservation::where('student_id', $student_id)
            ->where('session_id', $session_id)
            ->get()
            ->keyBy('term');

        // Attendance counts
        $attendanceRepo = new \App\Repositories\AttendanceRepository();
        $attendances    = $attendanceRepo->getStudentAttendance($session_id, $student_id);
        $presentCount   = $attendances->where('status', 'on')->count();
        $totalWorkingDays = 220;

        // CT name
        $ctAssignment = ClassTeacher::with('teacher')
            ->where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->where('session_id', $session_id)
            ->first();
        $ctName = $ctAssignment && $ctAssignment->teacher
            ? $ctAssignment->teacher->first_name . ' ' . $ctAssignment->teacher->last_name
            : '';

        $publishedTerms = ReportCardPublished::where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->where('session_id', $session_id)
            ->pluck('term')
            ->toArray();

        return view('marks.print', [
            'student'          => $student,
            'promotion'        => $promotion,
            'schoolClass'      => $schoolClass,
            'section'          => $section,
            'subjects'         => $subjects,
            'marks'            => $marks,
            'config'           => $config,
            'observations'     => $observations,
            'presentCount'     => $presentCount,
            'totalWorkingDays' => $totalWorkingDays,
            'ctName'           => $ctName,
            'session_id'       => $session_id,
            'publishedTerms'   => $publishedTerms,
        ]);
    }

    /**
     * Admin/CT: print report cards for all students in a class/section.
     */
    public function printClass(Request $request)
    {
        $session_id = $this->getSchoolCurrentSession();
        $user       = auth()->user();

        $class_id   = $request->query('class_id');
        $section_id = $request->query('section_id');

        if (!$class_id || !$section_id) {
            abort(400, 'class_id and section_id required.');
        }

        // CT: must be assigned to this class/section
        if ($user->role === 'teacher') {
            $isCT = ClassTeacher::where('teacher_id', $user->id)
                ->where('class_id', $class_id)
                ->where('section_id', $section_id)
                ->where('session_id', $session_id)
                ->exists();
            if (!$isCT) {
                abort(403);
            }
        }

        $schoolClass = $this->schoolClassRepository->findById($class_id);
        $section     = $this->sectionRepository->findById($section_id);
        $classGroup  = self::getClassGroup($schoolClass->class_name);
        $config      = self::CLASS_GROUP_CONFIG[$classGroup];

        $subjects = ClassSubject::with('subject')
            ->where('class_id', $class_id)
            ->where('session_id', $session_id)
            ->get()
            ->pluck('subject')
            ->filter()
            ->sortBy('sort_order')
            ->values();

        $promotionRepo = new PromotionRepository();
        $promotions    = $promotionRepo->getAll($session_id, $class_id, $section_id);

        $publishedTerms = ReportCardPublished::where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->where('session_id', $session_id)
            ->pluck('term')
            ->toArray();

        $ctAssignment = ClassTeacher::with('teacher')
            ->where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->where('session_id', $session_id)
            ->first();
        $ctName = $ctAssignment && $ctAssignment->teacher
            ? $ctAssignment->teacher->first_name . ' ' . $ctAssignment->teacher->last_name
            : '';

        $attendanceRepo  = new \App\Repositories\AttendanceRepository();
        $totalWorkingDays = 220;

        // Build per-student data
        $studentCards = [];
        foreach ($promotions->sortBy('roll_number') as $p) {
            $studentId = $p->student_id;
            $student   = $p->student;

            $marks = StudentTermMark::where('student_id', $studentId)
                ->where('session_id', $session_id)
                ->get()
                ->groupBy('subject_id');

            $observations = StudentObservation::where('student_id', $studentId)
                ->where('session_id', $session_id)
                ->get()
                ->keyBy('term');

            $attendances  = $attendanceRepo->getStudentAttendance($session_id, $studentId);
            $presentCount = $attendances->where('status', 'on')->count();

            $studentCards[] = compact('student', 'p', 'marks', 'observations', 'presentCount');
        }

        return view('marks.print-class', [
            'studentCards'     => $studentCards,
            'schoolClass'      => $schoolClass,
            'section'          => $section,
            'subjects'         => $subjects,
            'config'           => $config,
            'publishedTerms'   => $publishedTerms,
            'ctName'           => $ctName,
            'totalWorkingDays' => $totalWorkingDays,
            'class_id'         => $class_id,
            'section_id'       => $section_id,
        ]);
    }
}
