<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Models\ClassTeacher;
use App\Models\StudentObservation;
use App\Models\PrePrimarySkillGrade;
use App\Repositories\PromotionRepository;
use App\Interfaces\SchoolSessionInterface;
use App\Interfaces\SchoolClassInterface;
use App\Interfaces\SectionInterface;

class PrePrimaryController extends Controller
{
    use SchoolSession;

    protected $schoolSessionRepository;
    protected $schoolClassRepository;
    protected $sectionRepository;

    const SKILLS = [
        'nursery' => [
            'Language and Communication' => [
                'lang_1'  => 'Likes to look at books and pictures',
                'lang_2'  => 'Has a sense of left to right progression',
                'lang_3'  => 'Can recite alphabet',
                'lang_4'  => 'Can recognise letters: A-Z',
                'lang_5'  => 'Knows the sound of letters: A-Z',
                'lang_6'  => 'Can recognise own name',
                'lang_7'  => 'Knows basic colours',
                'lang_8'  => 'Knows days of the week',
                'lang_9'  => 'Knows months of the year',
                'lang_10' => 'Can follow simple directions',
                'lang_11' => 'Can join in songs and nursery rhymes',
                'lang_12' => 'Can sit and listen to stories',
                'lang_13' => 'Can write vertical / horizontal / zig zag lines',
            ],
            'Cognitive Development' => [
                'cog_1' => 'Can count to 10',
                'cog_2' => 'Can recognise numbers: 1-10',
                'cog_3' => 'Knows basic shapes',
                'cog_4' => 'Can sort objects by shape, colour, size',
                'cog_5' => 'Can identify parts of the body',
            ],
            'Personal, Social and Emotional Development' => [
                'ps_1' => 'Enjoys playing with others',
                'ps_2' => 'Can work cooperatively with others',
                'ps_3' => 'Can control behaviour',
                'ps_4' => 'Can share toys',
                'ps_5' => 'Can follow directions',
                'ps_6' => 'Can wait turn',
                'ps_7' => 'Can express opinions',
                'ps_8' => 'Can tidy up after play',
                'ps_9' => 'Is willing to help classmates',
            ],
            'Physical Development' => [
                'pd_1'  => 'Can throw a ball',
                'pd_2'  => 'Can catch a ball',
                'pd_3'  => 'Can jump / skip / hop on one leg',
                'pd_4'  => 'Can run in straight and curved line',
                'pd_5'  => 'Can hold a pencil / crayon properly',
                'pd_6'  => 'Can use scissors correctly',
                'pd_7'  => 'Can colour within lines',
                'pd_8'  => 'Can trace along vertical and horizontal line',
                'pd_9'  => 'Can use glue',
                'pd_10' => 'Can put together a three piece jigsaw',
                'pd_11' => 'Can build a tower of three or more blocks',
            ],
        ],
        'lkg' => [
            'Language and Communication' => [
                'lang_1'  => 'Can listen to and enjoy stories',
                'lang_2'  => 'Can join in action songs and nursery rhymes',
                'lang_3'  => 'Can communicate needs',
                'lang_4'  => 'Can recognise letters: A-Z',
                'lang_5'  => 'Knows the sound of letters: A-Z',
                'lang_6'  => 'Can write these letters: A-Z',
                'lang_7'  => 'Can respond to questions',
                'lang_8'  => 'Can handle a book properly',
                'lang_9'  => 'Knows days of the week / months of the year',
                'lang_10' => 'Knows basic colours',
                'lang_11' => 'Can follow simple directions',
                'lang_12' => 'Can draw simple pictures',
            ],
            'Cognitive Development' => [
                'cog_1'  => 'Can count to 20',
                'cog_2'  => 'Can recognise numbers: 1-20',
                'cog_3'  => 'Can write numbers: 1-10',
                'cog_4'  => 'Can add two groups of objects together',
                'cog_5'  => 'Know the value of numbers 1-10',
                'cog_6'  => 'Can sort objects by shape, colour, size',
                'cog_7'  => 'Can classify objects',
                'cog_8'  => 'Known and can recognise basic shapes',
                'cog_9'  => 'Understands the concept of before and after',
                'cog_10' => 'Understands the concept of more and less',
            ],
            'Personal, Social and Emotional Development' => [
                'ps_1'  => 'Enjoys playing with others',
                'ps_2'  => 'Can work cooperatively with others',
                'ps_3'  => 'Can share with others',
                'ps_4'  => 'Can control behaviour',
                'ps_5'  => 'Can follow directions',
                'ps_6'  => 'Can wait turn',
                'ps_7'  => 'Can express opinions and preferences',
                'ps_8'  => 'Can tidy up after play',
                'ps_9'  => 'Is willing to help classmates',
                'ps_10' => 'Show curiosity and interest in new things',
                'ps_11' => 'Cries if teased / hurt',
            ],
            'Physical Development' => [
                'pd_1'  => 'Can throw a ball - short / long distance',
                'pd_2'  => 'Can catch a ball - short / long distance',
                'pd_3'  => 'Can jump / skip / hop on one leg',
                'pd_4'  => 'Can run in straight and curved line',
                'pd_5'  => 'Can adjust speed when walking or running',
                'pd_6'  => 'Can hold a pencil / crayon properly',
                'pd_7'  => 'Can use scissors correctly',
                'pd_8'  => 'Can colour within lines',
                'pd_9'  => 'Can trace along vertical and horizontal line',
                'pd_10' => 'Can use glue',
                'pd_11' => 'Demonstrates good habits of personal hygiene',
                'pd_12' => 'Can take care of personal needs independently',
                'pd_13' => 'Can tie own shoe laces',
            ],
        ],
        'ukg' => [
            'Language and Communication' => [
                'lang_1'  => 'Can sit and listen to stories',
                'lang_2'  => 'Can respond to questions and greetings',
                'lang_3'  => 'Can match simple words to pictures',
                'lang_4'  => 'Can write capital and small letters',
                'lang_5'  => 'Can say the sounds of all the letters',
                'lang_6'  => 'Can read 3 letter words',
                'lang_7'  => 'Can read sight words',
                'lang_8'  => 'Can read simple sentences',
                'lang_9'  => 'Can describe a picture',
                'lang_10' => 'Can retell a story or incident',
                'lang_11' => 'Can express opinions and feelings',
                'lang_12' => 'Handles a book properly',
                'lang_13' => 'Can describe people and things',
                'lang_14' => 'Can write and recognise own name',
                'lang_15' => 'Can follow directions',
                'lang_16' => 'Can give meaning to drawings, paintings etc',
            ],
            'Cognitive Development' => [
                'cog_1' => 'Can count to 50',
                'cog_2' => 'Can recognise and write numbers 0-20',
                'cog_3' => 'Can sort, organise and match objects by colour, size, shape',
                'cog_4' => 'Can add two sets of objects together',
                'cog_5' => 'Can subtract one from a group of objects',
                'cog_6' => 'Can differentiate mathematical concepts',
                'cog_7' => 'Can differentiate scientific concepts',
            ],
            'Personal, Social and Emotional Development' => [
                'ps_1'  => 'Enjoys playing with others',
                'ps_2'  => 'Can work cooperatively with others',
                'ps_3'  => 'Can control behaviour',
                'ps_4'  => 'Can share with others',
                'ps_5'  => 'Can follow directions',
                'ps_6'  => 'Can wait turn',
                'ps_7'  => 'Can express opinions and feelings confidently',
                'ps_8'  => 'Can make decisions',
                'ps_9'  => 'Can tidy up after play',
                'ps_10' => 'Is willing to help classmates',
            ],
            'Physical Development' => [
                'pd_1'  => 'Can throw and catch a ball',
                'pd_2'  => 'Can run in straight and curved line',
                'pd_3'  => 'Can jump / skip / hop on one leg',
                'pd_4'  => 'Can adjust speed and direction to avoid obstacles',
                'pd_5'  => 'Can hold a pencil / crayon properly',
                'pd_6'  => 'Can use scissors correctly',
                'pd_7'  => 'Can colour within lines',
                'pd_8'  => 'Can trace along vertical and horizontal line',
                'pd_9'  => 'Can apply glue',
                'pd_10' => 'Demonstrates good habits of personal hygiene',
                'pd_11' => 'Can take care of personal needs',
            ],
        ],
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
        });
    }

    /**
     * Detect pre-primary type from class name.
     * Returns 'nursery', 'lkg', 'ukg', or null.
     */
    public static function getPrePrimaryType($class_name)
    {
        $lower = strtolower($class_name);
        if (strpos($lower, 'nursery') !== false)  return 'nursery';
        if (strpos($lower, 'upper kg') !== false) return 'ukg';
        if (strpos($lower, 'lower kg') !== false) return 'lkg';
        if (strpos($lower, 'ukg') !== false)      return 'ukg';
        if (strpos($lower, 'lkg') !== false)      return 'lkg';
        return null;
    }

    /**
     * GET /preprimary/entry
     * Grade entry form — rows = skills, columns = students.
     */
    public function entry(Request $request)
    {
        $session_id = $this->getSchoolCurrentSession();
        $user       = auth()->user();

        $class_id   = $request->query('class_id');
        $section_id = $request->query('section_id');
        $term       = (int) $request->query('term', 1);

        // CT auto-detects their class
        if ($user->role === 'teacher' && (!$class_id || !$section_id)) {
            $ct = ClassTeacher::where('teacher_id', $user->id)
                ->where('session_id', $session_id)
                ->first();
            if ($ct) {
                $class_id   = $ct->class_id;
                $section_id = $ct->section_id;
            }
        }

        if (!$class_id || !$section_id) {
            // Show a class/section picker for admin (teachers should always have a CT assignment)
            $allClasses = $this->schoolClassRepository->getAllBySession($session_id);
            $ppClasses  = $allClasses->filter(function ($c) {
                return self::getPrePrimaryType($c->class_name) !== null;
            })->values();

            return view('preprimary.pick', [
                'ppClasses'  => $ppClasses,
                'term'       => $term,
                'session_id' => $session_id,
            ]);
        }

        $schoolClass = $this->schoolClassRepository->findById($class_id);
        $section     = $this->sectionRepository->findById($section_id);

        $ppType = self::getPrePrimaryType($schoolClass->class_name ?? '');
        if (!$ppType) {
            return redirect()->route('marks.index')->with('error', 'This class is not a pre-primary class.');
        }

        // Authorization for teachers
        if ($user->role === 'teacher') {
            $isCT = ClassTeacher::where('teacher_id', $user->id)
                ->where('class_id', $class_id)
                ->where('section_id', $section_id)
                ->where('session_id', $session_id)
                ->exists();
            if (!$isCT) {
                abort(403, 'You are not the class teacher for this class.');
            }
        }

        $promotionRepository = new PromotionRepository();
        $allPromotions = $promotionRepository->getAll($session_id, $class_id, $section_id)
            ->sortBy('roll_number')
            ->values();

        // Paginate students — 10 per page
        $perPage    = 10;
        $page       = max(1, (int) $request->query('page', 1));
        $totalPages = max(1, (int) ceil($allPromotions->count() / $perPage));
        $page       = min($page, $totalPages);
        $promotions = $allPromotions->forPage($page, $perPage);

        // Load existing grades keyed by "student_id.skill_code"
        $rawGrades = PrePrimarySkillGrade::where('session_id', $session_id)
            ->where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->where('term', $term)
            ->get();

        $existingGrades = [];
        foreach ($rawGrades as $g) {
            $existingGrades[$g->student_id . '.' . $g->skill_code] = $g->grade;
        }

        $skills = self::SKILLS[$ppType];

        // CT name
        $ctRecord = ClassTeacher::with('teacher')
            ->where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->where('session_id', $session_id)
            ->first();
        $ctName = $ctRecord && $ctRecord->teacher
            ? ($ctRecord->teacher->first_name . ' ' . $ctRecord->teacher->last_name)
            : '';

        return view('preprimary.entry', [
            'schoolClass'    => $schoolClass,
            'section'        => $section,
            'class_id'       => $class_id,
            'section_id'     => $section_id,
            'term'           => $term,
            'ppType'         => $ppType,
            'allPromotions'  => $allPromotions,
            'promotions'     => $promotions,
            'existingGrades' => $existingGrades,
            'skills'         => $skills,
            'session_id'     => $session_id,
            'ctName'         => $ctName,
            'page'           => $page,
            'totalPages'     => $totalPages,
            'perPage'        => $perPage,
        ]);
    }

    /**
     * POST /preprimary/entry
     * Save all skill grades for the class.
     */
    public function store(Request $request)
    {
        $session_id = $this->getSchoolCurrentSession();
        $user       = auth()->user();

        $class_id   = $request->input('class_id');
        $section_id = $request->input('section_id');
        $term       = (int) $request->input('term', 1);

        // Authorization for teachers
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
        $ppType = self::getPrePrimaryType($schoolClass->class_name ?? '');
        if (!$ppType) {
            abort(400, 'Not a pre-primary class.');
        }

        $allSkillCodes = [];
        foreach (self::SKILLS[$ppType] as $section => $skills) {
            foreach ($skills as $code => $label) {
                $allSkillCodes[] = $code;
            }
        }

        $gradesInput = $request->input('grades', []);

        foreach ($gradesInput as $student_id => $skillGrades) {
            foreach ($allSkillCodes as $skill_code) {
                $grade = isset($skillGrades[$skill_code]) ? $skillGrades[$skill_code] : '';

                if ($grade === '' || $grade === null) {
                    // Delete if blank
                    PrePrimarySkillGrade::where('student_id', $student_id)
                        ->where('session_id', $session_id)
                        ->where('term', $term)
                        ->where('skill_code', $skill_code)
                        ->delete();
                    continue;
                }

                if (!in_array($grade, ['E', 'S', 'I', 'D'])) {
                    continue;
                }

                PrePrimarySkillGrade::updateOrCreate(
                    [
                        'student_id' => $student_id,
                        'session_id' => $session_id,
                        'term'       => $term,
                        'skill_code' => $skill_code,
                    ],
                    [
                        'class_id'   => $class_id,
                        'section_id' => $section_id,
                        'grade'      => $grade,
                        'created_by' => $user->id,
                    ]
                );
            }
        }

        return redirect()->route('preprimary.entry', [
            'class_id'   => $class_id,
            'section_id' => $section_id,
            'term'       => $term,
        ])->with('status', 'Grades saved successfully.');
    }

    /**
     * GET /preprimary/narratives
     * Narrative text areas per student for a term.
     */
    public function narratives(Request $request)
    {
        $session_id = $this->getSchoolCurrentSession();
        $user       = auth()->user();

        $class_id   = $request->query('class_id');
        $section_id = $request->query('section_id');
        $term       = (int) $request->query('term', 1);

        // CT auto-detects their class
        if ($user->role === 'teacher' && (!$class_id || !$section_id)) {
            $ct = ClassTeacher::where('teacher_id', $user->id)
                ->where('session_id', $session_id)
                ->first();
            if ($ct) {
                $class_id   = $ct->class_id;
                $section_id = $ct->section_id;
            }
        }

        if (!$class_id || !$section_id) {
            $allClasses = $this->schoolClassRepository->getAllBySession($session_id);
            $ppClasses  = $allClasses->filter(function ($c) {
                return self::getPrePrimaryType($c->class_name) !== null;
            })->values();

            return view('preprimary.pick', [
                'ppClasses'  => $ppClasses,
                'term'       => $term,
                'session_id' => $session_id,
            ]);
        }

        // Authorization for teachers
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

        $ppType = self::getPrePrimaryType($schoolClass->class_name ?? '');
        if (!$ppType) {
            return redirect()->route('marks.index')->with('error', 'This class is not a pre-primary class.');
        }

        $promotionRepository = new PromotionRepository();
        $promotions = $promotionRepository->getAll($session_id, $class_id, $section_id)
            ->sortBy('roll_number');

        $rawNarratives = StudentObservation::where('session_id', $session_id)
            ->where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->where('term', $term)
            ->get()
            ->keyBy('student_id');

        return view('preprimary.narratives', [
            'schoolClass'   => $schoolClass,
            'section'       => $section,
            'class_id'      => $class_id,
            'section_id'    => $section_id,
            'term'          => $term,
            'ppType'        => $ppType,
            'promotions'    => $promotions,
            'narratives'    => $rawNarratives,
            'session_id'    => $session_id,
        ]);
    }

    /**
     * POST /preprimary/narratives
     * Save narratives for all students.
     */
    public function saveNarratives(Request $request)
    {
        $session_id = $this->getSchoolCurrentSession();
        $user       = auth()->user();

        $class_id   = $request->input('class_id');
        $section_id = $request->input('section_id');
        $term       = (int) $request->input('term', 1);

        // Authorization for teachers
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

        $doesWellIn      = $request->input('does_well_in', []);
        $needsImprovement = $request->input('needs_improvement', []);
        $remarks         = $request->input('remarks', []);

        $studentIds = array_unique(array_merge(
            array_keys($doesWellIn),
            array_keys($needsImprovement),
            array_keys($remarks)
        ));

        foreach ($studentIds as $student_id) {
            StudentObservation::updateOrCreate(
                [
                    'student_id' => $student_id,
                    'session_id' => $session_id,
                    'term'       => $term,
                ],
                [
                    'class_id'         => $class_id,
                    'section_id'       => $section_id,
                    'does_well_in'     => isset($doesWellIn[$student_id]) ? $doesWellIn[$student_id] : null,
                    'needs_improvement'=> isset($needsImprovement[$student_id]) ? $needsImprovement[$student_id] : null,
                    'remarks'          => isset($remarks[$student_id]) ? $remarks[$student_id] : null,
                    'created_by'       => $user->id,
                ]
            );
        }

        return redirect()->route('preprimary.narratives', [
            'class_id'   => $class_id,
            'section_id' => $section_id,
            'term'       => $term,
        ])->with('status', 'Narratives saved successfully.');
    }

    /**
     * GET /preprimary/print/{student_id}
     * Print report card for one student.
     */
    public function printReportCard(Request $request, $student_id)
    {
        $session_id = $this->getSchoolCurrentSession();
        $user       = auth()->user();

        $promotionRepository = new PromotionRepository();
        $promotion = $promotionRepository->getPromotionInfoById($session_id, $student_id);

        if (!$promotion) {
            abort(404, 'Student not found in current session.');
        }

        $class_id   = $promotion->class_id;
        $section_id = $promotion->section_id;

        // Authorization for teachers
        if ($user->role === 'teacher') {
            $isCT = ClassTeacher::where('teacher_id', $user->id)
                ->where('class_id', $class_id)
                ->where('section_id', $section_id)
                ->where('session_id', $session_id)
                ->exists();
            if (!$isCT) {
                abort(403, 'You are not the class teacher for this student\'s class.');
            }
        }

        $schoolClass = $this->schoolClassRepository->findById($class_id);
        $section     = $this->sectionRepository->findById($section_id);

        $ppType = self::getPrePrimaryType($schoolClass->class_name ?? '');
        if (!$ppType) {
            abort(400, 'Not a pre-primary class.');
        }

        $skills = self::SKILLS[$ppType];

        // Load grades for both terms
        $rawGrades = PrePrimarySkillGrade::where('student_id', $student_id)
            ->where('session_id', $session_id)
            ->get();

        $grades = []; // grades[term][skill_code] = grade
        foreach ($rawGrades as $g) {
            if (!isset($grades[$g->term])) {
                $grades[$g->term] = [];
            }
            $grades[$g->term][$g->skill_code] = $g->grade;
        }

        // Load narratives for both terms
        $rawNarratives = StudentObservation::where('student_id', $student_id)
            ->where('session_id', $session_id)
            ->get()
            ->keyBy('term');

        // CT name
        $ctRecord = ClassTeacher::with('teacher')
            ->where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->where('session_id', $session_id)
            ->first();
        $ctName = $ctRecord && $ctRecord->teacher
            ? ($ctRecord->teacher->first_name . ' ' . $ctRecord->teacher->last_name)
            : '';

        $student = $promotion->student;

        return view('preprimary.print', [
            'student'    => $student,
            'promotion'  => $promotion,
            'schoolClass'=> $schoolClass,
            'section'    => $section,
            'ppType'     => $ppType,
            'skills'     => $skills,
            'grades'     => $grades,
            'narratives' => $rawNarratives,
            'ctName'     => $ctName,
            'session_id' => $session_id,
        ]);
    }

    /**
     * GET /preprimary/print-class
     * Print report cards for all students in a class.
     */
    public function printClass(Request $request)
    {
        $session_id = $this->getSchoolCurrentSession();
        $user       = auth()->user();

        $class_id   = $request->query('class_id');
        $section_id = $request->query('section_id');

        // CT auto-detects their class
        if ($user->role === 'teacher' && (!$class_id || !$section_id)) {
            $ct = ClassTeacher::where('teacher_id', $user->id)
                ->where('session_id', $session_id)
                ->first();
            if ($ct) {
                $class_id   = $ct->class_id;
                $section_id = $ct->section_id;
            }
        }

        if (!$class_id || !$section_id) {
            abort(400, 'class_id and section_id are required.');
        }

        // Authorization for teachers
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

        $ppType = self::getPrePrimaryType($schoolClass->class_name ?? '');
        if (!$ppType) {
            abort(400, 'Not a pre-primary class.');
        }

        $skills = self::SKILLS[$ppType];

        $promotionRepository = new PromotionRepository();
        $promotions = $promotionRepository->getAll($session_id, $class_id, $section_id)
            ->sortBy('roll_number');

        // Load all grades for this class/section
        $allGradesRaw = PrePrimarySkillGrade::where('session_id', $session_id)
            ->where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->get();

        $allGrades = []; // allGrades[student_id][term][skill_code] = grade
        foreach ($allGradesRaw as $g) {
            if (!isset($allGrades[$g->student_id])) {
                $allGrades[$g->student_id] = [];
            }
            if (!isset($allGrades[$g->student_id][$g->term])) {
                $allGrades[$g->student_id][$g->term] = [];
            }
            $allGrades[$g->student_id][$g->term][$g->skill_code] = $g->grade;
        }

        // Load all narratives for this class/section
        $allNarrativesRaw = StudentObservation::where('session_id', $session_id)
            ->where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->get();

        $allNarratives = []; // allNarratives[student_id][term] = observation
        foreach ($allNarrativesRaw as $n) {
            if (!isset($allNarratives[$n->student_id])) {
                $allNarratives[$n->student_id] = [];
            }
            $allNarratives[$n->student_id][$n->term] = $n;
        }

        // CT name
        $ctRecord = ClassTeacher::with('teacher')
            ->where('class_id', $class_id)
            ->where('section_id', $section_id)
            ->where('session_id', $session_id)
            ->first();
        $ctName = $ctRecord && $ctRecord->teacher
            ? ($ctRecord->teacher->first_name . ' ' . $ctRecord->teacher->last_name)
            : '';

        return view('preprimary.print-class', [
            'promotions'   => $promotions,
            'schoolClass'  => $schoolClass,
            'section'      => $section,
            'ppType'       => $ppType,
            'skills'       => $skills,
            'allGrades'    => $allGrades,
            'allNarratives'=> $allNarratives,
            'ctName'       => $ctName,
            'class_id'     => $class_id,
            'section_id'   => $section_id,
            'session_id'   => $session_id,
        ]);
    }
}
