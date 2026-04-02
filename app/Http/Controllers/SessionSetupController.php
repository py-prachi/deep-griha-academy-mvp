<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SchoolSession;
use App\Models\SchoolClass;
use App\Models\Section;
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

            $clonedClasses = 0;
            $clonedSections = 0;

            foreach ($sourceClasses as $sourceClass) {
                // Clone class into new session
                $newClass = SchoolClass::create([
                    'class_name' => $sourceClass->class_name,
                    'session_id' => $targetId,
                ]);
                $clonedClasses++;

                // Clone all sections for this class
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
            }

            DB::commit();

            return back()->with('status',
                'Cloned ' . $clonedClasses . ' classes and ' . $clonedSections . ' sections from "' .
                SchoolSession::find($sourceId)->session_name . '" into "' . $targetSession->session_name . '". ' .
                'You can now run Promotions.'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withError('Clone failed: ' . $e->getMessage());
        }
    }
}
