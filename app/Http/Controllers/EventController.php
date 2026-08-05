<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Holiday;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\SchoolSession;
use App\Interfaces\SchoolSessionInterface;
use Barryvdh\DomPDF\Facade\Pdf;

class EventController extends Controller
{
    use SchoolSession;
    protected $schoolSessionRepository;

    public function __construct(SchoolSessionInterface $schoolSessionRepository) {
        $this->schoolSessionRepository = $schoolSessionRepository;
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $current_school_session_id = $this->getSchoolCurrentSession();
            $data = Event::whereDate('start', '>=', $request->start)
                ->whereDate('end', '<=', $request->end)
                ->where('session_id', $current_school_session_id)
                ->where('publish_to_calendar', true)
                ->get(['id', 'title', 'start', 'end', 'activity_type', 'grade', 'description',
                       'purpose', 'location', 'duration', 'participants', 'participant_count',
                       'skills_values', 'photo_url', 'outcome', 'created_by'])
                ->map(function ($event) {
                    $arr = $event->toArray();
                    $arr['type'] = 'activity';
                    return $arr;
                });

            // Holidays are published to everyone's calendar automatically —
            // no publish_to_calendar toggle for them, they're always visible.
            $holidays = Holiday::whereDate('date', '>=', $request->start)
                ->whereDate('date', '<=', $request->end)
                ->where('session_id', $current_school_session_id)
                ->get()
                ->map(function ($holiday) {
                    return [
                        'id'        => 'holiday-' . $holiday->id,
                        'title'     => $holiday->name,
                        'start'     => $holiday->date->toDateString(),
                        'end'       => $holiday->date->toDateString(),
                        'type'      => 'holiday',
                        'color'     => '#6c757d',
                        'textColor' => '#ffffff',
                    ];
                });

            return response()->json($data->concat($holidays)->values());
        }
        return view('events.index');
    }

    public function calendarEvents(Request $request)
    {
        $current_school_session_id = $this->getSchoolCurrentSession();
        $user = auth()->user();
        $isAdmin = $user->role === 'admin';
        $event = null;

        switch ($request->type) {
            case 'create':
                $event = Event::create([
                    'title'             => $request->activity_type ?: 'Activity',
                    'start'             => $request->start,
                    'end'               => $request->end,
                    'session_id'        => $current_school_session_id,
                    'activity_type'     => $request->activity_type,
                    'grade'             => $request->grade,
                    'description'       => $request->description,
                    'purpose'           => $request->purpose,
                    'location'          => $request->location,
                    'duration'          => $request->duration,
                    'participants'      => $request->participants,
                    'participant_count' => $request->participant_count ?: null,
                    'skills_values'     => $request->skills_values,
                    'photo_url'            => $request->photo_url ?: null,
                    'outcome'              => $request->outcome,
                    'publish_to_calendar'  => $request->boolean('publish_to_calendar'),
                    'created_by'           => $user->id,
                ]);
                break;

            case 'edit':
                $event = Event::find($request->id);
                if (!$event) return response()->json(['error' => 'Not found'], 404);
                if (!$isAdmin && $event->created_by !== $user->id) {
                    return response()->json(['error' => 'Unauthorized'], 403);
                }
                $event->update([
                    'title'             => $request->activity_type ?: $event->title,
                    'start'             => $request->start,
                    'end'               => $request->end,
                    'activity_type'     => $request->activity_type,
                    'grade'             => $request->grade,
                    'description'       => $request->description,
                    'purpose'           => $request->purpose,
                    'location'          => $request->location,
                    'duration'          => $request->duration,
                    'participants'      => $request->participants,
                    'participant_count' => $request->participant_count ?: null,
                    'skills_values'     => $request->skills_values,
                    'photo_url'            => $request->photo_url ?: $event->photo_url,
                    'outcome'              => $request->outcome,
                    'publish_to_calendar'  => $request->boolean('publish_to_calendar'),
                ]);
                $event->refresh();
                break;

            case 'delete':
                $event = Event::find($request->id);
                if (!$event) return response()->json(['error' => 'Not found'], 404);
                if (!$isAdmin && $event->created_by !== $user->id) {
                    return response()->json(['error' => 'Unauthorized'], 403);
                }
                $id = $event->id;
                $event->delete();
                return response()->json(['deleted' => true, 'id' => $id]);
        }

        return response()->json($event);
    }

    public function report(Request $request)
    {
        $user = auth()->user();

        $events = $this->buildEventQuery($request)->paginate(20)->withQueryString();
        $teachers = User::where('role', 'teacher')->orderBy('first_name')->get();

        return view('events.report', compact('events', 'teachers'));
    }

    // Everyone (admin and teachers) can browse all logged activities in view mode.
    // Pass created_by to narrow to a single teacher (e.g. the "My Activities" shortcut).
    private function buildEventQuery(Request $request)
    {
        $query = Event::with('creator');

        if ($request->filled('activity_type')) {
            $query->where('activity_type', 'like', '%' . $request->activity_type . '%');
        }
        if ($request->filled('date_from')) {
            $query->whereDate('start', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('start', '<=', $request->date_to);
        }
        if ($request->filled('created_by')) {
            $query->where('created_by', $request->created_by);
        }

        return $query->orderBy('start', 'desc');
    }

    public function reportPdf(Request $request)
    {
        $events   = $this->buildEventQuery($request)->get();
        $user     = auth()->user();
        $filters  = array_filter([
            'Activity Type' => $request->activity_type,
            'From'          => $request->date_from,
            'To'            => $request->date_to,
            'Teacher'       => $request->filled('created_by')
                ? optional(User::find($request->created_by))->full_name
                : null,
        ]);

        $pdf = Pdf::loadView('events.report-pdf', compact('events', 'user', 'filters'))
            ->setPaper('a4', 'landscape');

        $filename = 'event-report-' . now()->format('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }

    public function eventPdf($id)
    {
        $event = Event::with('creator')->findOrFail($id);
        $user  = auth()->user();

        $pdf = Pdf::loadView('events.event-pdf', compact('event', 'user'))
            ->setPaper('a4', 'portrait');

        $filename = 'event-' . \Str::slug($event->title) . '-' . \Carbon\Carbon::parse($event->start)->format('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }
}
