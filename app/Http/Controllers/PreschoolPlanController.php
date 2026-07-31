<?php

namespace App\Http\Controllers;

use App\Models\PreschoolPlan;
use App\Models\PreschoolSlot;
use App\Models\ClassTeacher;
use App\Models\SchoolClass;
use App\Traits\SchoolSession;
use App\Interfaces\SchoolSessionInterface;
use Illuminate\Http\Request;

class PreschoolPlanController extends Controller
{
    use SchoolSession;

    protected $schoolSessionRepository;

    public function __construct(SchoolSessionInterface $schoolSessionRepository)
    {
        $this->schoolSessionRepository = $schoolSessionRepository;
    }

    public function index()
    {
        $user      = auth()->user();
        $sessionId = $this->getSchoolCurrentSession();

        if ($user->role === 'admin') {
            $classes = SchoolClass::whereHas('sections', function ($q) use ($sessionId) {
                $q->where('session_id', $sessionId);
            })->where(function ($q) {
                $q->whereRaw('LOWER(class_name) LIKE ?', ['%nursery%'])
                  ->orWhereRaw('LOWER(class_name) LIKE ?', ['%kg%']);
            })
              ->orderBy('id')
              ->get();

            $selClassId   = request('class_id');
            $selSectionId = request('section_id');
            $plans        = collect();

            if ($selClassId && $selSectionId) {
                $plans = PreschoolPlan::with(['teacher', 'schoolClass', 'section'])
                    ->withCount('slots')
                    ->where('session_id', $sessionId)
                    ->where('class_id', $selClassId)
                    ->where('section_id', $selSectionId)
                    ->orderByDesc('plan_date')
                    ->get();
            }

            return view('preschool-plans.admin-index', compact(
                'classes', 'plans', 'selClassId', 'selSectionId', 'sessionId'
            ));
        }

        // CT teacher view — a teacher may be CT for more than one pre-school
        // class (e.g. one teacher covering both Nursery and Lower KG).
        $ctAssignments = ClassTeacher::with(['schoolClass', 'section'])
            ->where('teacher_id', $user->id)
            ->where('session_id', $sessionId)
            ->whereHas('schoolClass', function ($q) {
                $q->whereRaw('LOWER(class_name) LIKE ?', ['%nursery%'])
                  ->orWhereRaw('LOWER(class_name) LIKE ?', ['%kg%']);
            })
            ->get();

        if ($ctAssignments->isEmpty()) {
            return view('preschool-plans.index', [
                'ctAssignment'   => null,
                'ctAssignments'  => collect(),
                'plans'          => collect(),
            ]);
        }

        $selClassId   = request('class_id');
        $selSectionId = request('section_id');

        $ctAssignment = $selClassId && $selSectionId
            ? $ctAssignments->first(fn($a) => (int)$a->class_id === (int)$selClassId && (int)$a->section_id === (int)$selSectionId)
            : ($ctAssignments->count() === 1 ? $ctAssignments->first() : null);

        if (!$ctAssignment) {
            // Multiple classes and none selected (or an invalid one requested) — let them pick.
            return view('preschool-plans.index', [
                'ctAssignment'  => null,
                'ctAssignments' => $ctAssignments,
                'plans'         => collect(),
            ]);
        }

        $plans = PreschoolPlan::withCount('slots')
            ->where('session_id', $sessionId)
            ->where('class_id', $ctAssignment->class_id)
            ->where('section_id', $ctAssignment->section_id)
            ->orderByDesc('plan_date')
            ->get();

        return view('preschool-plans.index', [
            'ctAssignment'  => $ctAssignment,
            'ctAssignments' => $ctAssignments,
            'plans'         => $plans,
        ]);
    }

    public function create(Request $request)
    {
        $user      = auth()->user();
        $sessionId = $this->getSchoolCurrentSession();

        $ctAssignments = ClassTeacher::with(['schoolClass', 'section'])
            ->where('teacher_id', $user->id)
            ->where('session_id', $sessionId)
            ->whereHas('schoolClass', function ($q) {
                $q->whereRaw('LOWER(class_name) LIKE ?', ['%nursery%'])
                  ->orWhereRaw('LOWER(class_name) LIKE ?', ['%kg%']);
            })
            ->get();

        $selClassId   = $request->query('class_id');
        $selSectionId = $request->query('section_id');

        $ctAssignment = $selClassId && $selSectionId
            ? $ctAssignments->first(fn($a) => (int)$a->class_id === (int)$selClassId && (int)$a->section_id === (int)$selSectionId)
            : ($ctAssignments->count() === 1 ? $ctAssignments->first() : null);

        if (!$ctAssignment) {
            abort(404);
        }

        return view('preschool-plans.create', compact('ctAssignment', 'sessionId'));
    }

    public function store(Request $request)
    {
        $user      = auth()->user();
        $sessionId = $this->getSchoolCurrentSession();

        $ctAssignments = ClassTeacher::where('teacher_id', $user->id)
            ->where('session_id', $sessionId)
            ->whereHas('schoolClass', function ($q) {
                $q->whereRaw('LOWER(class_name) LIKE ?', ['%nursery%'])
                  ->orWhereRaw('LOWER(class_name) LIKE ?', ['%kg%']);
            })
            ->get();

        $ctAssignment = $request->filled(['class_id', 'section_id'])
            ? $ctAssignments->first(fn($a) => (int)$a->class_id === (int)$request->class_id && (int)$a->section_id === (int)$request->section_id)
            : ($ctAssignments->count() === 1 ? $ctAssignments->first() : null);

        if (!$ctAssignment) {
            abort(403, 'You are not the Class Teacher for that class.');
        }

        $data = $request->validate([
            'plan_date'                       => 'required|date',
            'slots'                           => 'nullable|array',
            'slots.*.activity_name'           => 'required|string|max:200',
            'slots.*.material'                => 'nullable|string',
            'slots.*.objective'               => 'nullable|string',
            'slots.*.actual_teach'            => 'nullable|string',
            'slots.*.assessment'              => 'nullable|string',
        ]);

        $plan = PreschoolPlan::create([
            'session_id' => $sessionId,
            'teacher_id' => $user->id,
            'class_id'   => $ctAssignment->class_id,
            'section_id' => $ctAssignment->section_id,
            'plan_date'  => $data['plan_date'],
        ]);

        if (!empty($data['slots'])) {
            foreach ($data['slots'] as $index => $slot) {
                PreschoolSlot::create([
                    'plan_id'       => $plan->id,
                    'sort_order'    => $index,
                    'activity_name' => $slot['activity_name'],
                    'material'      => $slot['material'] ?? null,
                    'objective'     => $slot['objective'] ?? null,
                    'actual_teach'  => $slot['actual_teach'] ?? null,
                    'assessment'    => $slot['assessment'] ?? null,
                ]);
            }
        }

        return redirect()->route('preschool-plans.index', [
                'class_id'   => $ctAssignment->class_id,
                'section_id' => $ctAssignment->section_id,
            ])
            ->with('status', 'Daily plan created successfully.');
    }

    public function edit($id)
    {
        $user = auth()->user();
        $plan = PreschoolPlan::with(['slots', 'schoolClass', 'section'])->findOrFail($id);

        if ($user->role !== 'admin' && $plan->teacher_id !== $user->id) {
            abort(403, 'You can only edit your own plans.');
        }

        return view('preschool-plans.edit', compact('plan'));
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $plan = PreschoolPlan::findOrFail($id);

        if ($user->role !== 'admin' && $plan->teacher_id !== $user->id) {
            abort(403);
        }

        $data = $request->validate([
            'plan_date'                       => 'required|date',
            'slots'                           => 'nullable|array',
            'slots.*.activity_name'           => 'required|string|max:200',
            'slots.*.material'                => 'nullable|string',
            'slots.*.objective'               => 'nullable|string',
            'slots.*.actual_teach'            => 'nullable|string',
            'slots.*.assessment'              => 'nullable|string',
        ]);

        $plan->update(['plan_date' => $data['plan_date']]);

        // Replace all slots
        $plan->slots()->delete();

        if (!empty($data['slots'])) {
            foreach ($data['slots'] as $index => $slot) {
                PreschoolSlot::create([
                    'plan_id'       => $plan->id,
                    'sort_order'    => $index,
                    'activity_name' => $slot['activity_name'],
                    'material'      => $slot['material'] ?? null,
                    'objective'     => $slot['objective'] ?? null,
                    'actual_teach'  => $slot['actual_teach'] ?? null,
                    'assessment'    => $slot['assessment'] ?? null,
                ]);
            }
        }

        return redirect()->route('preschool-plans.index', [
                'class_id'   => $plan->class_id,
                'section_id' => $plan->section_id,
            ])
            ->with('status', 'Daily plan updated successfully.');
    }

    public function destroy($id)
    {
        $user = auth()->user();
        $plan = PreschoolPlan::findOrFail($id);

        if ($user->role !== 'admin' && $plan->teacher_id !== $user->id) {
            abort(403);
        }

        $plan->delete(); // cascades to slots

        return back()->with('status', 'Daily plan deleted.');
    }

    public function printView($id)
    {
        $user = auth()->user();
        $plan = PreschoolPlan::with(['slots', 'schoolClass', 'section', 'teacher'])->findOrFail($id);

        if ($user->role !== 'admin' && $plan->teacher_id !== $user->id) {
            abort(403);
        }

        $session = $this->schoolSessionRepository->getLatestSession();

        return view('preschool-plans.print', compact('plan', 'session'));
    }
}
