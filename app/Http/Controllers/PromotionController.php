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

        // Build per-section promoted map for selected class
        $promotedSectionIds = [];
        foreach ($previousSessionSections as $prevSection) {
            $sectionStudentIds = $promotionRepository->getAll(
                $previousSession['id'], $class_id, $prevSection->section->id
            )->pluck('student_id');

            if ($sectionStudentIds->isNotEmpty()) {
                $alreadyPromoted = Promotion::where('session_id', $current_school_session_id)
                    ->whereIn('student_id', $sectionStudentIds)
                    ->exists();
                if ($alreadyPromoted) {
                    $promotedSectionIds[] = $prevSection->section->id;
                }
            }
        }

        // Build overall class summary — for each class, how many sections promoted vs total
        $classSummary = [];
        foreach ($previousSessionClasses as $prevClass) {
            $cid = $prevClass->schoolClass->id;
            $allSections = $promotionRepository->getSections($previousSession['id'], $cid);
            $total = $allSections->count();
            $done  = 0;
            foreach ($allSections as $sec) {
                $ids = $promotionRepository->getAll($previousSession['id'], $cid, $sec->section->id)->pluck('student_id');
                if ($ids->isNotEmpty() && Promotion::where('session_id', $current_school_session_id)->whereIn('student_id', $ids)->exists()) {
                    $done++;
                }
            }
            $classSummary[$cid] = ['total' => $total, 'done' => $done];
        }

        $data = [
            'previousSessionClasses'  => $previousSessionClasses,
            'class_id'                => $class_id,
            'previousSessionSections' => $previousSessionSections,
            'promotedSectionIds'      => $promotedSectionIds,
            'previousSessionId'       => $previousSession['id'],
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

            $data = [
                'students'      => $students,
                'schoolClass'   => $schoolClass,
                'section'       => $section,
                'school_classes'=> $school_classes,
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
        $id_card_numbers = $request->id_card_number;
        $latest_school_session = $this->schoolSessionRepository->getLatestSession();
        $graduateFlags = $request->graduate ?? [];

        $rows = [];
        $graduatedCount = 0;
        $i = 0;
        foreach($id_card_numbers as $student_id => $id_card_number) {
            // If this student is marked as graduated, update their status and skip promotion
            if (isset($graduateFlags[$student_id])) {
                User::where('id', $student_id)->update(['student_status' => 'graduated']);
                Admission::where('student_user_id', $student_id)->update(['status' => Admission::STATUS_GRADUATED]);
                $graduatedCount++;
                $i++;
                continue;
            }

            $row = [
                'student_id'    => $student_id,
                'id_card_number'=> $id_card_number,
                'class_id'      => $request->class_id[$i],
                'section_id'    => $request->section_id[$i],
                'session_id'    => $latest_school_session->id,
            ];
            array_push($rows, $row);
            $i++;
        }

        try {
            $promotionRepository = new PromotionRepository();
            if (!empty($rows)) {
                $promotionRepository->massPromotion($rows);
                // Update admission record to reflect current class/section
                foreach ($rows as $row) {
                    Admission::where('student_user_id', $row['student_id'])
                        ->update([
                            'class_id'   => $row['class_id'],
                            'section_id' => $row['section_id'],
                        ]);
                }
            }

            $promotedCount = count($rows);
            $message = 'Promoting students was successful!';
            if ($graduatedCount > 0 && $promotedCount > 0) {
                $message = $promotedCount . ' student(s) promoted. ' . $graduatedCount . ' student(s) graduated (Class 8 passed out).';
            } elseif ($graduatedCount > 0) {
                $message = $graduatedCount . ' student(s) marked as graduated (Class 8 passed out). No promotions.';
            }

            return back()->with('status', $message);
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
