<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SchoolSession;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\ClassSubject;
use App\Models\FeeStructure;
use Illuminate\Support\Facades\DB;

class SessionSetupController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (auth()->user()->role !== 'admin') {
                abort(403);
            }
            return $next($request);
        });
    }

    /**
     * Clone all classes and sections from a source session into a target session.
     * POST /session/clone-classes
     */
    public function cloneClasses(Request $request)
    {
        $request->validate([
            'source_session_id' => 'required|exists:school_sessions,id',
            'target_session_id' => 'required|exists:school_sessions,id',
        ]);

        $sourceId = $request->source_session_id;
        $targetId = $request->target_session_id;

        if ($sourceId == $targetId) {
            return back()->withError('Source and target session must be different.');
        }

        $targetSession = SchoolSession::findOrFail($targetId);

        // Check nothing already exists in target
        $existingClasses = SchoolClass::where('session_id', $targetId)->count();
        if ($existingClasses > 0) {
            return back()->withError(
                'Session "' . $targetSession->session_name . '" already has ' . $existingClasses . ' class(es). Clear them first or use an empty session.'
            );
        }

        DB::beginTransaction();
        try {
            $sourceClasses = SchoolClass::where('session_id', $sourceId)
                ->orderBy('id')
                ->get();

            $clonedClasses   = 0;
            $clonedSections  = 0;
            $clonedSubjects  = 0;
            $clonedFees      = 0;

            // Map old class_id → new class_id for subject + fee cloning
            $classIdMap = [];

            foreach ($sourceClasses as $sourceClass) {
                // Clone class
                $newClass = SchoolClass::create([
                    'class_name' => $sourceClass->class_name,
                    'session_id' => $targetId,
                ]);
                $clonedClasses++;
                $classIdMap[$sourceClass->id] = $newClass->id;

                // Clone sections
                $sourceSections = Section::where('class_id', $sourceClass->id)
                    ->where('session_id', $sourceId)
                    ->get();

                foreach ($sourceSections as $sourceSection) {
                    Section::create([
                        'section_name' => $sourceSection->section_name,
                        'room_no'      => $sourceSection->room_no ?? '—',
                        'class_id'     => $newClass->id,
                        'session_id'   => $targetId,
                    ]);
                    $clonedSections++;
                }

                // Clone class-subject assignments
                $sourceSubjects = ClassSubject::where('class_id', $sourceClass->id)
                    ->where('session_id', $sourceId)
                    ->get();

                foreach ($sourceSubjects as $cs) {
                    ClassSubject::create([
                        'subject_id' => $cs->subject_id,
                        'class_id'   => $newClass->id,
                        'session_id' => $targetId,
                    ]);
                    $clonedSubjects++;
                }

                // Clone fee structures
                $sourceFees = FeeStructure::where('class_id', $sourceClass->id)
                    ->where('session_id', $sourceId)
                    ->get();

                foreach ($sourceFees as $fee) {
                    FeeStructure::create([
                        'class_id'          => $newClass->id,
                        'session_id'        => $targetId,
                        'academic_year'     => $targetSession->session_name,
                        'fee_category'      => $fee->fee_category,
                        'admission_fee'     => $fee->admission_fee,
                        'tuition_fee'       => $fee->tuition_fee,
                        'girls_tuition_fee' => $fee->girls_tuition_fee,
                        'transport_fee'     => $fee->transport_fee,
                        'other_fee'         => $fee->other_fee,
                        // total_fee auto-calculated by model boot
                    ]);
                    $clonedFees++;
                }
            }

            DB::commit();

            $sourceName = SchoolSession::find($sourceId)->session_name;
            return back()->with('status',
                'Cloned from "' . $sourceName . '" into "' . $targetSession->session_name . '": ' .
                $clonedClasses . ' classes, ' . $clonedSections . ' sections, ' .
                $clonedSubjects . ' subject assignments, ' . $clonedFees . ' fee structures. ' .
                'Review fee structures for any changes, then run Promotions.'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withError('Clone failed: ' . $e->getMessage());
        }
    }
}
