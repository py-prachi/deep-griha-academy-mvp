<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Interfaces\FeeStructureInterface;
use App\Interfaces\SchoolSessionInterface;
use App\Interfaces\SchoolClassInterface;
use App\Models\FeeStructure;

class FeeStructureController extends Controller
{
    use SchoolSession;

    protected $feeStructureRepository;
    protected $schoolSessionRepository;
    protected $schoolClassRepository;

    public function __construct(
        FeeStructureInterface $feeStructureRepository,
        SchoolSessionInterface $schoolSessionRepository,
        SchoolClassInterface $schoolClassRepository
    ) {
        $this->middleware(function ($request, $next) {
            if (!in_array(auth()->user()->role, ['admin'])) {
                abort(403);
            }
            return $next($request);
        });
        $this->feeStructureRepository  = $feeStructureRepository;
        $this->schoolSessionRepository = $schoolSessionRepository;
        $this->schoolClassRepository   = $schoolClassRepository;
    }

    public function index()
    {
        $current_school_session_id = $this->getSchoolCurrentSession();
        $feeStructures = $this->feeStructureRepository->getAll($current_school_session_id);
        $school_classes = $this->schoolClassRepository->getAllBySession($current_school_session_id);
        return view('fee-structures.index', [
            'feeStructures'  => $feeStructures,
            'school_classes' => $school_classes,
            'categories'     => FeeStructure::CATEGORY_LABELS,
        ]);
    }

    public function create()
    {
        $current_school_session_id = $this->getSchoolCurrentSession();
        $school_classes = $this->schoolClassRepository->getAllBySession($current_school_session_id);
        $session = $this->schoolSessionRepository->getLatestSession();
        return view('fee-structures.create', [
            'school_classes' => $school_classes,
            'categories'     => FeeStructure::CATEGORY_LABELS,
            'session'        => $session,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'class_id'          => 'required|exists:school_classes,id',
            'fee_category'      => 'required|in:general,rte,coc,discount',
            'academic_year'     => 'required|string',
            'admission_fee'     => 'nullable|numeric|min:0',
            'tuition_fee'       => 'nullable|numeric|min:0',
            'girls_tuition_fee' => 'nullable|numeric|min:0',
            'transport_fee'     => 'nullable|numeric|min:0',
            'other_fee'         => 'nullable|numeric|min:0',
        ]);

        try {
            $current_school_session_id = $this->getSchoolCurrentSession();
            $this->feeStructureRepository->store(array_merge(
                $request->all(),
                ['session_id' => $current_school_session_id]
            ));
            return redirect()->route('fee-structures.index')
                ->with('status', 'Fee structure saved successfully!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $current_school_session_id = $this->getSchoolCurrentSession();
        $feeStructure = \App\Models\FeeStructure::findOrFail($id);
        $school_classes = $this->schoolClassRepository->getAllBySession($current_school_session_id);
        $session = $this->schoolSessionRepository->getLatestSession();
        return view('fee-structures.edit', [
            'feeStructure'   => $feeStructure,
            'school_classes' => $school_classes,
            'categories'     => FeeStructure::CATEGORY_LABELS,
            'session'        => $session,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'admission_fee'     => 'nullable|numeric|min:0',
            'tuition_fee'       => 'nullable|numeric|min:0',
            'girls_tuition_fee' => 'nullable|numeric|min:0',
            'transport_fee'     => 'nullable|numeric|min:0',
            'other_fee'         => 'nullable|numeric|min:0',
        ]);

        try {
            $this->feeStructureRepository->update($id, $request->only([
                'admission_fee', 'tuition_fee', 'girls_tuition_fee', 'transport_fee', 'other_fee'
            ]));
            return redirect()->route('fee-structures.index')
                ->with('status', 'Fee structure updated successfully!');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $this->feeStructureRepository->delete($id);
            return redirect()->route('fee-structures.index')
                ->with('status', 'Fee structure deleted.');
        } catch (\Exception $e) {
            return back()->withError($e->getMessage());
        }
    }
}
