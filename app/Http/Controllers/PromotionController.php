<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Interfaces\UserInterface;
use App\Interfaces\SectionInterface;
use App\Interfaces\SchoolClassInterface;
use App\Repositories\PromotionRepository;
use App\Interfaces\SchoolSessionInterface;

class PromotionController extends Controller
{
    use SchoolSession;

    protected $schoolSessionRepository;
    protected $userRepository;
    protected $schoolClassRepository;
    protected $schoolSectionRepository;

    /**
    * Create a new Controller instance
    * 
    * @param SchoolSessionInterface $schoolSessionRepository
    * @return void
    */
    public function __construct(
        SchoolSessionInterface $schoolSessionRepository,
        UserInterface $userRepository,
        SchoolClassInterface $schoolClassRepository,
        SectionInterface $schoolSectionRepository
    ) {
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->userRepository = $userRepository;
        $this->schoolClassRepository = $schoolClassRepository;
        $this->schoolSectionRepository = $schoolSectionRepository;
    }
    /**
     * Display a listing of the resource.
     * @param  \Illuminate\Http\Request  $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $class_id = $request->query('class_id', 0);

        $promotionRepository = new PromotionRepository();
        $previousSession = $this->schoolSessionRepository->getPreviousSession();

        if(count($previousSession) < 1) {
            return redirect('academics/settings')->with('info', 'Promotions require at least two academic sessions. Create a new session here first, then return to Promotions.');
        }

        $previousSessionClasses = $promotionRepository->getClasses($previousSession['id']);

        $previousSessionSections = $promotionRepository->getSections($previousSession['id'], $class_id);

        $current_school_session_id = $this->getSchoolCurrentSession();

        // Build per-section promoted map + promoted student detail for selected class
        $promotedSectionIds    = [];
        $promotedStudentDetail = []; // section_id => [ [name, new_class, new_section, graduated] ]
        foreach ($previousSessionSections as $prevSection) {
            $secId = $prevSection->section->id;
            $sectionStudentIds = $promotionRepository->getAll(
                $previousSession['id'], $class_id, $secId
            )->pluck('student_id');

            if ($sectionStudentIds->isEmpty()) continue;

            // A section is "done" if all its students are either promoted OR graduated
            $promotedInNew = Promotion::where('session_id', $current_school_session_id)
                ->whereIn('student_id', $sectionStudentIds)->count();
            $graduatedCount = User::whereIn('id', $sectionStudentIds)
                ->where('student_status', 'graduated')->count();
            $handledCount = $promotedInNew + $graduatedCount;

            if ($handledCount >= $sectionStudentIds->count()) {
                $promotedSectionIds[] = $secId;

                // Build detail list for display
                $detail = [];
                foreach ($sectionStudentIds as $sid) {
                    $student = User::find($sid);
                    if (!$student) continue;
                    if ($student->student_status === 'graduated') {
                        $detail[] = [
                            'name'        => $student->first_name . ' ' . $student->last_name,
                            'new_class'   => 'Graduated',
                            'new_section' => '—',
                            'graduated'   => true,
                        ];
                    } else {
                        $newPromo = Promotion::with(['schoolClass', 'section'])
                            ->where('session_id', $current_school_session_id)
                            ->where('student_id', $sid)->first();
                        $detail[] = [
                            'name'        => $student->first_name . ' ' . $student->last_name,
                            'new_class'   => $newPromo && $newPromo->schoolClass ? $newPromo->schoolClass->class_name : '—',
                            'new_section' => $newPromo && $newPromo->section ? $newPromo->section->section_name : '—',
                            'graduated'   => false,
                        ];
                    }
                }
                $promotedStudentDetail[$secId] = $detail;
            }
        }

        // Build overall class summary — for each class, sections/students promoted vs total
        // Graduated students count as "done"
        $classSummary = [];
        foreach ($previousSessionClasses as $prevClass) {
            $cid = $prevClass->schoolClass->id;
            $allSections   = $promotionRepository->getSections($previousSession['id'], $cid);
            $totalSections = $allSections->count();
            $doneSections  = 0;
            $totalStudents = 0;
            $doneStudents  = 0;
            foreach ($allSections as $sec) {
                $ids = $promotionRepository->getAll($previousSession['id'], $cid, $sec->section->id)->pluck('student_id');
                $totalStudents += $ids->count();
                if ($ids->isNotEmpty()) {
                    $promoted   = Promotion::where('session_id', $current_school_session_id)->whereIn('student_id', $ids)->count();
                    $graduated  = User::whereIn('id', $ids)->where('student_status', 'graduated')->count();
                    $handled    = $promoted + $graduated;
                    $doneStudents += $handled;
                    if ($handled >= $ids->count()) {
                        $doneSections++;
                    }
                }
            }
            $classSummary[$cid] = [
                'total'         => $totalSections,
                'done'          => $doneSections,
                'totalStudents' => $totalStudents,
                'doneStudents'  => $doneStudents,
            ];
        }

        $latestSession = $this->schoolSessionRepository->getLatestSession();

        $data = [
            'previousSessionClasses'  => $previousSessionClasses,
            'class_id'                => $class_id,
            'previousSessionSections' => $previousSessionSections,
            'promotedSectionIds'      => $promotedSectionIds,
            'promotedStudentDetail'   => $promotedStudentDetail,
            'previousSessionId'       => $previousSession['id'],
            'previousSessionName'     => $previousSession['session_name'],
            'latestSessionName'       => $latestSession->session_name,
            'classSummary'            => $classSummary,
        ];

        return view('promotions.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     * @param  \Illuminate\Http\Request  $request
     * 
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $class_id = $request->query('previous_class_id');
        $section_id = $request->query('previous_section_id');
        $session_id = $request->query('previousSessionId');

        try{

            if($class_id == null || $section_id == null ||$session_id == null) {
                return abort(404);
            }

            $students = $this->userRepository->getAllStudents($session_id, $class_id, $section_id);

            $schoolClass = $this->schoolClassRepository->findById($class_id);
            $section = $this->schoolSectionRepository->findById($section_id);

            $latest_school_session = $this->schoolSessionRepository->getLatestSession();

            $school_classes = $this->schoolClassRepository->getAllBySession($latest_school_session->id);

            $previousSession = $this->schoolSessionRepository->getPreviousSession();

            $data = [
                'students'            => $students,
                'schoolClass'         => $schoolClass,
                'section'             => $section,
                'school_classes'      => $school_classes,
                'previousSessionName' => $previousSession['session_name'] ?? '',
                'latestSessionName'   => $latest_school_session->session_name,
            ];

            return view('promotions.promote', $data);
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $id_card_numbers  = $request->id_card_number ?? [];
        $classIds         = $request->class_id ?? [];
        $sectionIds       = $request->section_id ?? [];
        $graduateFlags    = $request->graduate ?? [];
        $latest_school_session = $this->schoolSessionRepository->getLatestSession();

        // Validate: every non-graduated student must have class + section selected
        $missing = [];
        foreach ($id_card_numbers as $student_id => $id_card_number) {
            if (!isset($graduateFlags[$student_id])) {
                if (empty($classIds[$student_id]) || empty($sectionIds[$student_id])) {
                    $user = User::find($student_id);
                    $missing[] = $user ? $user->first_name . ' ' . $user->last_name : 'Student #' . $student_id;
                }
            }
        }
        if (!empty($missing)) {
            return back()->withError('Please select a class and section for: ' . implode(', ', $missing));
        }

        $rows = [];
        $graduatedCount = 0;
        foreach($id_card_numbers as $student_id => $id_card_number) {
            if (isset($graduateFlags[$student_id])) {
                User::where('id', $student_id)->update(['student_status' => 'graduated']);
                Admission::where('student_user_id', $student_id)->update(['status' => Admission::STATUS_GRADUATED]);
                $graduatedCount++;
                continue;
            }

            $rows[] = [
                'student_id'    => $student_id,
                'id_card_number'=> $id_card_number,
                'class_id'      => $classIds[$student_id],
                'section_id'    => $sectionIds[$student_id],
                'session_id'    => $latest_school_session->id,
            ];
        }

        try {
            $promotionRepository = new PromotionRepository();
            if (!empty($rows)) {
                $promotionRepository->massPromotion($rows);
                foreach ($rows as $row) {
                    Admission::where('student_user_id', $row['student_id'])
                        ->update([
                            'class_id'   => $row['class_id'],
                            'section_id' => $row['section_id'],
                        ]);
                }
            }

            $promotedCount = count($rows);
            if ($graduatedCount > 0 && $promotedCount > 0) {
                $message = $promotedCount . ' student(s) promoted. ' . $graduatedCount . ' student(s) graduated.';
            } elseif ($graduatedCount > 0) {
                $message = $graduatedCount . ' student(s) marked as graduated (Class 8 passed out).';
            } else {
                $message = $promotedCount . ' student(s) promoted successfully.';
            }

            return redirect()->route('promotions.index')->with('status', $message);
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Promotion  $promotion
     * @return \Illuminate\Http\Response
     */
    public function show(Promotion $promotion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Promotion  $promotion
     * @return \Illuminate\Http\Response
     */
    public function edit(Promotion $promotion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Promotion  $promotion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Promotion $promotion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Promotion  $promotion
     * @return \Illuminate\Http\Response
     */
    public function destroy(Promotion $promotion)
    {
        //
    }
}
