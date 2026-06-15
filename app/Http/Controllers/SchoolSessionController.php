<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Interfaces\SchoolSessionInterface;
use App\Http\Requests\SchoolSessionStoreRequest;
use App\Http\Requests\SchoolSessionBrowseRequest;
use App\Traits\SchoolSession;
use App\Http\Controllers\YearEndController;

class SchoolSessionController extends Controller
{
    use SchoolSession;

    protected $schoolSessionRepository;

    /**
    * Create a new Controller instance
    * 
    * @param SchoolSessionInterface $schoolSessionRepository
    * @return void
    */
    public function __construct(SchoolSessionInterface $schoolSessionRepository) {
        $this->schoolSessionRepository = $schoolSessionRepository;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  SchoolSessionStoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SchoolSessionStoreRequest $request)
    {
        // Block if any students have unsettled outstanding balances
        $session_id = $this->getSchoolCurrentSession();
        $unsettled  = YearEndController::unsettledCount($session_id);
        if ($unsettled > 0) {
            return back()->withError(
                $unsettled . ' student(s) have outstanding fee balances that have not been settled. ' .
                'Please go to Year-End Settlement and resolve all balances before creating a new session.'
            );
        }

        try {
            $this->schoolSessionRepository->create($request->validated());
            return back()->with('status', 'Session created successfully!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    /**
     * Save the selected school session as current session for
     * browsing.
     *
     * @param  SchoolSessionBrowseRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function browse(SchoolSessionBrowseRequest $request)
    {
        try {
            $this->schoolSessionRepository->browse($request->validated());

            return back()->with('status', 'Browsing session set was successful!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
        
    }
}
