<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Traits\SchoolSession;
use App\Interfaces\SchoolSessionInterface;

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
                ->get(['id', 'title', 'start', 'end', 'activity_type', 'description',
                       'purpose', 'location', 'duration', 'participants', 'participant_count',
                       'skills_values', 'photo_url', 'outcome', 'created_by']);
            return response()->json($data);
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
                    'title'             => $request->title,
                    'start'             => $request->start,
                    'end'               => $request->end,
                    'session_id'        => $current_school_session_id,
                    'activity_type'     => $request->activity_type,
                    'description'       => $request->description,
                    'purpose'           => $request->purpose,
                    'location'          => $request->location,
                    'duration'          => $request->duration,
                    'participants'      => $request->participants,
                    'participant_count' => $request->participant_count ?: null,
                    'skills_values'     => $request->skills_values,
                    'photo_url'         => $this->handlePhotoUpload($request),
                    'outcome'           => $request->outcome,
                    'created_by'        => $user->id,
                ]);
                break;

            case 'edit':
                $event = Event::find($request->id);
                if (!$event) return response()->json(['error' => 'Not found'], 404);
                if (!$isAdmin && $event->created_by !== $user->id) {
                    return response()->json(['error' => 'Unauthorized'], 403);
                }
                $photoUrl = $this->handlePhotoUpload($request) ?? $event->photo_url;
                $event->update([
                    'title'             => $request->title,
                    'start'             => $request->start,
                    'end'               => $request->end,
                    'activity_type'     => $request->activity_type,
                    'description'       => $request->description,
                    'purpose'           => $request->purpose,
                    'location'          => $request->location,
                    'duration'          => $request->duration,
                    'participants'      => $request->participants,
                    'participant_count' => $request->participant_count ?: null,
                    'skills_values'     => $request->skills_values,
                    'photo_url'         => $photoUrl,
                    'outcome'           => $request->outcome,
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
        $current_school_session_id = $this->getSchoolCurrentSession();
        $user = auth()->user();

        $query = Event::where('session_id', $current_school_session_id)
            ->with('creator');

        if ($request->filled('activity_type')) {
            $query->where('activity_type', 'like', '%' . $request->activity_type . '%');
        }
        if ($request->filled('date_from')) {
            $query->whereDate('start', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('start', '<=', $request->date_to);
        }
        if ($user->role !== 'admin') {
            $query->where('created_by', $user->id);
        } elseif ($request->filled('created_by')) {
            $query->where('created_by', $request->created_by);
        }

        $events = $query->orderBy('start', 'desc')->paginate(20)->withQueryString();
        $teachers = $user->role === 'admin'
            ? User::where('role', 'teacher')->orderBy('first_name')->get()
            : collect();

        return view('events.report', compact('events', 'teachers'));
    }

    private function handlePhotoUpload(Request $request): ?string
    {
        if (!$request->hasFile('photo')) return null;

        if (env('CLOUDINARY_URL') || config('cloudinary.cloud_url')) {
            try {
                return cloudinary()->upload($request->file('photo')->getRealPath())->getSecurePath();
            } catch (\Exception $e) {
                \Log::channel('stderr')->error('Cloudinary upload failed: ' . $e->getMessage());
            }
        }

        $path = $request->file('photo')->store('events', 'public');
        return Storage::url($path);
    }
}
