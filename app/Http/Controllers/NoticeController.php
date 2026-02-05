<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Repositories\NoticeRepository;
use App\Http\Requests\NoticeStoreRequest;
use App\Interfaces\SchoolSessionInterface;
use App\Interfaces\SchoolClassInterface;

class NoticeController extends Controller
{
    use SchoolSession;
    
    protected $schoolSessionRepository;
    protected $schoolClassRepository;
    protected $noticeRepository;


    public function __construct(
    NoticeRepository $noticeRepository,
    SchoolSessionInterface $schoolSessionRepository,
    SchoolClassInterface $schoolClassRepository // 👈 add
) {
    $this->noticeRepository = $noticeRepository;
    $this->schoolSessionRepository = $schoolSessionRepository;
    $this->schoolClassRepository = $schoolClassRepository; // 👈 add
}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
{
    $current_school_session_id = $this->getSchoolCurrentSession();

    // Get classes for current session
    $classes = $this->schoolClassRepository
        ->getAllBySession($current_school_session_id);

    return view('notices.create', compact(
        'current_school_session_id',
        'classes'
    ));
}


    /**
     * Store a newly created resource in storage.
     *
     * @param  NoticeStoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(NoticeStoreRequest $request)
{
    try {
        $this->noticeRepository->store($request->validated());

        return back()->with('status', 'Creating Notice was successful!');
    } catch (\Exception $e) {
        return back()->withError($e->getMessage());
    }
}


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Notice  $notice
     * @return \Illuminate\Http\Response
     */
    public function show(Notice $notice)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Notice  $notice
     * @return \Illuminate\Http\Response
     */
    public function edit(Notice $notice)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Notice  $notice
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Notice $notice)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Notice  $notice
     * @return \Illuminate\Http\Response
     */
    public function destroy(Notice $notice)
    {
        //
    }
}
