@extends('layouts.app')
@section('title', 'Academic Settings')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h4 class="mb-1"><i class="bi bi-tools me-1"></i> Academic Settings</h4>
                    <p class="text-muted small mb-4">Current session: <strong>{{ $school_sessions->sortByDesc('id')->first()->session_name ?? '—' }}</strong></p>

                    @include('session-messages')

                    {{-- ── SECTION 1: YEAR-END WORKFLOW ── --}}
                    <h6 class="text-uppercase text-muted small fw-bold mb-2 border-bottom pb-1">
                        <i class="bi bi-calendar-check me-1"></i> Year-End Workflow
                        <span class="text-secondary fw-normal ms-1">(do these steps in order at the start of each academic year)</span>
                    </h6>
                    <div class="row g-3 mb-4">

                        {{-- Step 1: Create Session --}}
                        @php
                            $existingNames = $school_sessions->pluck('session_name')->map(fn($n) => trim($n))->toArray();
                            $latestName = $school_sessions->sortByDesc('id')->first() ? trim($school_sessions->sortByDesc('id')->first()->session_name) : '2025-2026';
                            $parts = explode('-', $latestName);
                            $endYear = isset($parts[1]) ? (int)trim($parts[1]) : (int)trim($parts[0]) + 1;
                            $upcomingSessions = [];
                            for ($y = $endYear; $y <= $endYear + 2; $y++) {
                                $name = $y . '-' . ($y + 1);
                                if (!in_array($name, $existingNames)) $upcomingSessions[] = $name;
                            }
                        @endphp
                        <div class="col-md-4">
                            <div class="card h-100 border-primary">
                                <div class="card-header bg-primary text-white py-2">
                                    <strong><span class="badge bg-white text-primary me-1">1</span> Create New Session</strong>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small">One session per academic year. Creates a new year (e.g. 2026-2027).</p>
                                    @if ($latest_school_session_id == $current_school_session_id)
                                        @if(count($upcomingSessions) > 0)
                                        <form action="{{ route('school.session.store') }}" method="POST">
                                            @csrf
                                            <select class="form-select form-select-sm mb-2" name="session_name" required>
                                                <option value="" disabled selected>Select year</option>
                                                @foreach($upcomingSessions as $sName)
                                                    <option value="{{ $sName }}">{{ $sName }}</option>
                                                @endforeach
                                            </select>
                                            <button class="btn btn-sm btn-primary w-100"><i class="bi bi-plus me-1"></i> Create Session</button>
                                        </form>
                                        @else
                                            <p class="text-success small mb-0"><i class="bi bi-check-circle me-1"></i> All upcoming sessions already created.</p>
                                        @endif
                                    @else
                                        <p class="text-warning small mb-0"><i class="bi bi-exclamation-triangle me-1"></i> Switch to latest session to create a new one.</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Step 2: Clone Classes --}}
                        <div class="col-md-4">
                            <div class="card h-100 border-primary">
                                <div class="card-header bg-primary text-white py-2">
                                    <strong><span class="badge bg-white text-primary me-1">2</span> Clone Classes & Sections</strong>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small">Copy all classes and sections from last year into the new session.</p>
                                    <form action="{{ route('school.session.clone-classes') }}" method="POST">
                                        @csrf
                                        <div class="mb-2">
                                            <label class="form-label form-label-sm mb-1">Copy FROM:</label>
                                            <select class="form-select form-select-sm" name="source_session_id" required>
                                                @foreach($school_sessions->sortByDesc('id') as $s)
                                                    <option value="{{ $s->id }}">{{ $s->session_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label form-label-sm mb-1">Copy INTO (must be empty):</label>
                                            <select class="form-select form-select-sm" name="target_session_id" required>
                                                @foreach($school_sessions->sortByDesc('id') as $s)
                                                    <option value="{{ $s->id }}">{{ $s->session_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <button class="btn btn-sm btn-primary w-100"><i class="bi bi-copy me-1"></i> Clone</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Step 3: Promote --}}
                        <div class="col-md-4">
                            <div class="card h-100 border-primary">
                                <div class="card-header bg-primary text-white py-2">
                                    <strong><span class="badge bg-white text-primary me-1">3</span> Promote Students</strong>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small">Move all students from the previous year into their new class in the current session.</p>
                                    <a href="{{ url('promotions/index') }}" class="btn btn-sm btn-primary w-100">
                                        <i class="bi bi-sort-numeric-up-alt me-1"></i> Go to Promotions
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── SECTION 2: ONGOING SETUP ── --}}
                    <h6 class="text-uppercase text-muted small fw-bold mb-2 border-bottom pb-1">
                        <i class="bi bi-gear me-1"></i> Academic Setup
                        <span class="text-secondary fw-normal ms-1">(set up once per year)</span>
                    </h6>
                    <div class="row g-3 mb-4">

                        {{-- Subjects --}}
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6><i class="bi bi-book me-1 text-primary"></i> Subjects</h6>
                                    <p class="text-muted small">Add, edit, or deactivate subjects. Assign which subjects are taught in each class.</p>
                                    <a href="{{ route('subjects.index') }}" class="btn btn-sm btn-outline-primary w-100">Manage Subjects</a>
                                </div>
                            </div>
                        </div>

                        {{-- Teacher Assignments --}}
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6><i class="bi bi-person-badge me-1 text-primary"></i> Teacher Assignments</h6>
                                    <p class="text-muted small">Assign class teachers (for attendance) and subject teachers (for marks & assignments).</p>
                                    <a href="{{ route('academics.teacher-assignments') }}" class="btn btn-sm btn-outline-primary w-100">Assign Teachers</a>
                                </div>
                            </div>
                        </div>

                        {{-- Attendance Type --}}
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6><i class="bi bi-calendar2-check me-1 text-primary"></i> Attendance Type</h6>
                                    <p class="text-muted small">Section-wise is standard for primary schools. Do not change mid-year.</p>
                                    <form action="{{ route('school.attendance.type.update') }}" method="POST">
                                        @csrf
                                        <div class="form-check mb-1">
                                            <input class="form-check-input" type="radio" name="attendance_type" id="att_section" value="section"
                                                {{ optional($academic_setting)->attendance_type === 'section' ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="att_section">By Section <span class="badge bg-success ms-1">Recommended</span></label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="attendance_type" id="att_course" value="course"
                                                {{ optional($academic_setting)->attendance_type === 'course' ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="att_course">By Subject</label>
                                        </div>
                                        <button class="btn btn-sm btn-outline-primary w-100"><i class="bi bi-check2 me-1"></i> Save</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Add Section (mid-year) --}}
                        @if ($latest_school_session_id == $current_school_session_id)
                        <div class="col-md-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6><i class="bi bi-diagram-2 me-1 text-primary"></i> Add Section</h6>
                                    <p class="text-muted small">Add a new section to an existing class (e.g. open Section B mid-year).</p>
                                    <form action="{{ route('school.section.create') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="session_id" value="{{ $current_school_session_id }}">
                                        <div class="row g-1 mb-2">
                                            <div class="col-6">
                                                <input class="form-control form-control-sm" name="section_name" type="text" placeholder="Section (e.g. B)" required>
                                            </div>
                                            <div class="col-6">
                                                <input class="form-control form-control-sm" name="room_no" type="text" placeholder="Room No." required>
                                            </div>
                                        </div>
                                        <select class="form-select form-select-sm mb-2" name="class_id" required>
                                            <option value="" disabled selected>Assign to class</option>
                                            @isset($school_classes)
                                                @foreach($school_classes as $sc)
                                                    <option value="{{ $sc->id }}">{{ $sc->class_name }}</option>
                                                @endforeach
                                            @endisset
                                        </select>
                                        <button class="btn btn-sm btn-outline-primary w-100"><i class="bi bi-plus me-1"></i> Add Section</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif

                    </div>

                    {{-- ── SECTION 3: BROWSE / HISTORY ── --}}
                    <h6 class="text-uppercase text-muted small fw-bold mb-2 border-bottom pb-1">
                        <i class="bi bi-clock-history me-1"></i> Browse Historical Data
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h6><i class="bi bi-calendar3 me-1 text-secondary"></i> Browse by Session</h6>
                                    <p class="text-muted small">View data from a previous academic year. Resets when you select the latest session.</p>
                                    <form action="{{ route('school.session.browse') }}" method="POST">
                                        @csrf
                                        <select class="form-select form-select-sm mb-2" name="session_id" required>
                                            @foreach($school_sessions->sortByDesc('id') as $s)
                                                <option value="{{ $s->id }}" {{ $s->id == $current_school_session_id ? 'selected' : '' }}>
                                                    {{ $s->session_name }}{{ $s->id == $latest_school_session_id ? ' (latest)' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-sm btn-outline-secondary w-100"><i class="bi bi-eye me-1"></i> Browse</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
