@extends('layouts.app')

@section('content')
<script src="{{ asset('js/masonry.pkgd.min.js') }}"></script>
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-tools"></i> Academic Settings
                    </h1>

                    @include('session-messages')

                    <div class="mb-4">
                        <div class="row" data-masonry='{"percentPosition": true }'>
                            {{-- Clone Classes & Sections --}}
                            <div class="col-md-4 mb-4">
                                <div class="p-3 border bg-light shadow-sm">
                                    <h6><i class="bi bi-copy me-1"></i> Clone Classes &amp; Sections</h6>
                                    <p class="text-muted">
                                        <small>Copy all classes and sections from one session into another empty session. Use this after creating a new academic year session.</small>
                                    </p>
                                    @if(session('status'))
                                        <div class="alert alert-success alert-sm py-1 px-2 mb-2">
                                            <small>{{ session('status') }}</small>
                                        </div>
                                    @endif
                                    @if(session('error'))
                                        <div class="alert alert-danger alert-sm py-1 px-2 mb-2">
                                            <small>{{ session('error') }}</small>
                                        </div>
                                    @endif
                                    <form action="{{ route('school.session.clone-classes') }}" method="POST">
                                        @csrf
                                        <div class="mb-2">
                                            <label class="form-label form-label-sm mb-1">Copy FROM (source):</label>
                                            <select class="form-select form-select-sm" name="source_session_id" required>
                                                @isset($school_sessions)
                                                    @foreach($school_sessions as $s)
                                                        <option value="{{ $s->id }}">{{ $s->session_name }}</option>
                                                    @endforeach
                                                @endisset
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label form-label-sm mb-1">Copy INTO (target — must be empty):</label>
                                            <select class="form-select form-select-sm" name="target_session_id" required>
                                                @isset($school_sessions)
                                                    @foreach($school_sessions as $s)
                                                        <option value="{{ $s->id }}">{{ $s->session_name }}</option>
                                                    @endforeach
                                                @endisset
                                            </select>
                                        </div>
                                        <button class="btn btn-sm btn-outline-success" type="submit"><i class="bi bi-copy me-1"></i> Clone</button>
                                    </form>
                                </div>
                            </div>

                            @if ($latest_school_session_id == $current_school_session_id)
                            <div class="col-md-4 mb-4">
                                <div class="p-3 border bg-light shadow-sm">
                                    <h6>Create Session</h6>
                                    <p class="text-danger">
                                        <small><i class="bi bi-exclamation-diamond-fill me-2"></i> Create one Session per academic year. Last created session will be considered as the latest academic session.</small>
                                    </p>
                                    @php
                                        // Build list of upcoming session names not yet created
                                        $existingNames = $school_sessions->pluck('session_name')->map(function($n) { return trim($n); })->toArray();
                                        $latestName = $school_sessions->sortByDesc('id')->first() ? trim($school_sessions->sortByDesc('id')->first()->session_name) : '2025-2026';
                                        // Parse end year from latest session (e.g. "2025-2026" → 2026)
                                        $parts = explode('-', $latestName);
                                        $endYear = isset($parts[1]) ? (int) trim($parts[1]) : (int) trim($parts[0]) + 1;
                                        $upcomingSessions = [];
                                        for ($y = $endYear; $y <= $endYear + 2; $y++) {
                                            $name = $y . '-' . ($y + 1);
                                            if (!in_array($name, $existingNames)) {
                                                $upcomingSessions[] = $name;
                                            }
                                        }
                                    @endphp
                                    <form action="{{route('school.session.store')}}" method="POST">
                                        @csrf
                                        <div class="mb-3">
                                            @if(count($upcomingSessions) > 0)
                                                <select class="form-select form-select-sm" name="session_name" required>
                                                    <option value="" disabled selected>Select session year</option>
                                                    @foreach($upcomingSessions as $sName)
                                                        <option value="{{ $sName }}">{{ $sName }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <p class="text-muted small mb-0">No upcoming sessions to create.</p>
                                            @endif
                                        </div>
                                        @if(count($upcomingSessions) > 0)
                                            <button class="btn btn-sm btn-outline-primary" type="submit"><i class="bi bi-check2"></i> Create</button>
                                        @endif
                                    </form>
                                </div>
                            </div>
                            @endif
                            <div class="col-md-4 mb-4">
                                <div class="p-3 border bg-light shadow-sm">
                                    <h6>Browse by Session</h6>
                                    <p class="text-danger">
                                        <small><i class="bi bi-exclamation-diamond-fill me-2"></i> Only use this when you want to browse data from previous Sessions.</small>
                                    </p>
                                    <form action="{{route('school.session.browse')}}" method="POST">
                                        @csrf
                                    <div class="mb-3">
                                        <p class="mt-2">Select "Session" to browse by:</p>
                                        <select class="form-select form-select-sm" aria-label=".form-select-sm" name="session_id" required>
                                            @isset($school_sessions)
                                                @foreach ($school_sessions as $school_session)
                                                    <option value="{{$school_session->id}}">{{$school_session->session_name}}</option>
                                                @endforeach
                                            @endisset
                                        </select>
                                    </div>
                                    <button class="btn btn-sm btn-outline-primary" type="submit"><i class="bi bi-check2"></i> Set</button>
                                    </form>
                                </div>
                            </div>
                            @if ($latest_school_session_id == $current_school_session_id)
                            <div class="col-md-4 mb-4">
                                <div class="p-3 border bg-light shadow-sm">
                                    <h6>Create Semester for Current Session</h6>
                                    <form action="{{route('school.semester.create')}}" method="POST">
                                        @csrf
                                    <input type="hidden" name="session_id" value="{{$current_school_session_id}}">
                                    <div class="mt-2">
                                        <p>Semester name<sup><i class="bi bi-asterisk text-primary"></i></sup></p>
                                        <input type="text" class="form-control form-control-sm" placeholder="First Semester" aria-label="Semester name" name="semester_name" required>
                                    </div>
                                    <div class="mt-2">
                                        <label for="inputStarts" class="form-label">Starts<sup><i class="bi bi-asterisk text-primary"></i></sup></label>
                                        <input type="date" class="form-control form-control-sm" id="inputStarts" placeholder="Starts" name="start_date" required>
                                    </div>
                                    <div class="mt-2">
                                        <label for="inputEnds" class="form-label">Ends<sup><i class="bi bi-asterisk text-primary"></i></sup></label>
                                        <input type="date" class="form-control form-control-sm" id="inputEnds" placeholder="Ends" name="end_date" required>
                                    </div>
                                    <button type="submit" class="mt-3 btn btn-sm btn-outline-primary"><i class="bi bi-check2"></i> Create</button>
                                </form>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="p-3 border bg-light shadow-sm">
                                    <h6>Attendance Type</h6>
                                    <p class="text-danger">
                                        <small><i class="bi bi-exclamation-diamond-fill me-2"></i> Do not change the type in the middle of a Semester.</small>
                                    </p>
                                    <form action="{{route('school.attendance.type.update')}}" method="POST">
                                        @csrf
                                        <div class="form-check">
                                            <input
                                                class="form-check-input"
                                                type="radio"
                                                name="attendance_type"
                                                id="attendance_type_section"
                                                value="section"
                                                {{ optional($academic_setting)->attendance_type === 'section' ? 'checked' : '' }}
                                            >

                                            <label class="form-check-label" for="attendance_type_section">
                                                Attendance by Section
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input
                                                class="form-check-input"
                                                type="radio"
                                                name="attendance_type"
                                                id="attendance_type_course"
                                                value="course"
                                                {{ optional($academic_setting)->attendance_type === 'course' ? 'checked' : '' }}
                                            >
                                            <label class="form-check-label" for="attendance_type_course">
                                                Attendance by Course
                                            </label>
                                        </div>

                                        <button type="submit" class="mt-3 btn btn-sm btn-outline-primary"><i class="bi bi-check2"></i> Save</button>
                                    </form>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="p-3 border bg-light shadow-sm">
                                    <h6>Create Class</h6>
                                    <form action="{{route('school.class.create')}}" method="POST">
                                        @csrf
                                        <input type="hidden" name="session_id" value="{{$current_school_session_id}}">
                                        <div class="mb-3">
                                            <input type="text" class="form-control form-control-sm" name="class_name" placeholder="Class name" aria-label="Class name" required>
                                        </div>
                                        <button class="btn btn-sm btn-outline-primary" type="submit"><i class="bi bi-check2"></i> Create</button>
                                    </form>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="p-3 border bg-light shadow-sm">
                                <h6>Create Section</h6>
                                    <form action="{{route('school.section.create')}}" method="POST">
                                        @csrf
                                        <input type="hidden" name="session_id" value="{{$current_school_session_id}}">
                                        <div class="mb-3">
                                            <input class="form-control form-control-sm" name="section_name" type="text" placeholder="Section name" required>
                                        </div>
                                        <div class="mb-3">
                                            <input class="form-control form-control-sm" name="room_no" type="text" placeholder="Room No." required>
                                        </div>
                                        <div>
                                            <p>Assign section to class:</p>
                                            <select class="form-select form-select-sm" aria-label=".form-select-sm" name="class_id" required>
                                                @isset($school_classes)
                                                    @foreach ($school_classes as $school_class)
                                                    <option value="{{$school_class->id}}">{{$school_class->class_name}}</option>
                                                    @endforeach
                                                @endisset
                                            </select>
                                        </div>
                                        <button type="submit" class="mt-3 btn btn-sm btn-outline-primary"><i class="bi bi-check2"></i> Save</button>
                                    </form>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="p-3 border bg-light shadow-sm">
                                    <h6>Create Course</h6>
                                    <form action="{{route('school.course.create')}}" method="POST">
                                        @csrf
                                        <input type="hidden" name="session_id" value="{{$current_school_session_id}}">
                                        <div class="mb-1">
                                            <input type="text" class="form-control form-control-sm" name="course_name" placeholder="Course name" aria-label="Course name" required>
                                        </div>
                                        <div class="mb-3">
                                            <p class="mt-2">Course Type:<sup><i class="bi bi-asterisk text-primary"></i></sup></p>
                                            <select class="form-select form-select-sm" name="course_type" aria-label=".form-select-sm" required>
                                                <option value="Core">Core</option>
                                                <option value="General">General</option>
                                                <option value="Elective">Elective</option>
                                                <option value="Optional">Optional</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <p>Assign to semester:<sup><i class="bi bi-asterisk text-primary"></i></sup></p>
                                            <select class="form-select form-select-sm" aria-label=".form-select-sm" name="semester_id" required>
                                                @isset($semesters)
                                                    @foreach ($semesters as $semester)
                                                    <option value="{{$semester->id}}">{{$semester->semester_name}}</option>
                                                    @endforeach
                                                @endisset
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <p>Assign to class:<sup><i class="bi bi-asterisk text-primary"></i></sup></p>
                                            <select class="form-select form-select-sm" aria-label=".form-select-sm" name="class_id" required>
                                                @isset($school_classes)
                                                    @foreach ($school_classes as $school_class)
                                                    <option value="{{$school_class->id}}">{{$school_class->class_name}}</option>
                                                    @endforeach
                                                @endisset
                                            </select>
                                        </div>
                                        <button class="btn btn-sm btn-outline-primary" type="submit"><i class="bi bi-check2"></i> Create</button>
                                    </form>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="p-3 border bg-light shadow-sm">
                                    <h6>Assign Teacher</h6>
                                    <form action="{{route('school.teacher.assign')}}" method="POST">
                                        @csrf
                                        <input type="hidden" name="session_id" value="{{$current_school_session_id}}">
                                        <div class="mb-3">
                                            <p class="mt-2">Select Teacher:<sup><i class="bi bi-asterisk text-primary"></i></sup></p>
                                            <select class="form-select form-select-sm" aria-label=".form-select-sm" name="teacher_id" required>
                                                @isset($teachers)
                                                    @foreach ($teachers as $teacher)
                                                    <option value="{{$teacher->id}}">{{$teacher->first_name}} {{$teacher->last_name}}</option>
                                                    @endforeach
                                                @endisset
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <p>Assign to semester:<sup><i class="bi bi-asterisk text-primary"></i></sup></p>
                                            <select class="form-select form-select-sm" aria-label=".form-select-sm" name="semester_id" required>
                                                @isset($semesters)
                                                    @foreach ($semesters as $semester)
                                                    <option value="{{$semester->id}}">{{$semester->semester_name}}</option>
                                                    @endforeach
                                                @endisset
                                            </select>
                                        </div>
                                        <div>
                                            <p>Assign to class:<sup><i class="bi bi-asterisk text-primary"></i></sup></p>
                                            <select onchange="getSectionsAndCourses(this);" class="form-select form-select-sm" aria-label=".form-select-sm" name="class_id" required>
                                                @isset($school_classes)
                                                    <option selected disabled>Please select a class</option>
                                                    @foreach ($school_classes as $school_class)
                                                    <option value="{{$school_class->id}}">{{$school_class->class_name}}</option>
                                                    @endforeach
                                                @endisset
                                            </select>
                                        </div>
                                        <div>
                                            <p class="mt-2">Assign to section:<sup><i class="bi bi-asterisk text-primary"></i></sup></p>
                                            <select class="form-select form-select-sm" id="section-select" aria-label=".form-select-sm" name="section_id" required>
                                            </select>
                                        </div>
                                        <div>
                                            <p class="mt-2">Assign to course:<sup><i class="bi bi-asterisk text-primary"></i></sup></p>
                                            <select class="form-select form-select-sm" id="course-select" aria-label=".form-select-sm" name="course_id" required>
                                            </select>
                                        </div>
                                        <button type="submit" class="mt-3 btn btn-sm btn-outline-primary"><i class="bi bi-check2"></i> Save</button>
                                    </form>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="p-3 border bg-light shadow-sm">
                                    <h6>Allow Final Marks Submission</h6>
                                    <form action="{{route('school.final.marks.submission.status.update')}}" method="POST">
                                        @csrf
                                        <p class="text-danger">
                                            <small><i class="bi bi-exclamation-diamond-fill me-2"></i> Usually teachers are allowed to submit final marks just before the end of a "Semester".</small>
                                        </p>
                                        <p class="text-primary">
                                            <small><i class="bi bi-exclamation-diamond-fill me-2"></i> Disallow at the start of a "Semester".</small>
                                        </p>
                                        <div class="form-check form-switch">
                                            <input
                                                class="form-check-input"
                                                type="checkbox"
                                                name="marks_submission_status"
                                                id="marks_submission_status_check"
                                                {{ optional($academic_setting)->marks_submission_status === 'on' ? 'checked' : '' }}
                                            >

                                            <label class="form-check-label" for="marks_submission_status_check">
                                                {{ optional($academic_setting)->marks_submission_status === 'on' ? 'Allowed' : 'Disallowed' }}
                                            </label>

                                        </div>
                                        <button type="submit" class="mt-3 btn btn-sm btn-outline-primary"><i class="bi bi-check2"></i> Save</button>
                                    </form>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
<script>
    function getSectionsAndCourses(obj) {
        var class_id = obj.options[obj.selectedIndex].value;

        var url = "{{route('get.sections.courses.by.classId')}}?class_id=" + class_id 

        fetch(url)
        .then((resp) => resp.json())
        .then(function(data) {
            var sectionSelect = document.getElementById('section-select');
            sectionSelect.options.length = 0;
            data.sections.unshift({'id': 0,'section_name': 'Please select a section'})
            data.sections.forEach(function(section, key) {
                sectionSelect[key] = new Option(section.section_name, section.id);
            });

            var courseSelect = document.getElementById('course-select');
            courseSelect.options.length = 0;
            data.courses.unshift({'id': 0,'course_name': 'Please select a course'})
            data.courses.forEach(function(course, key) {
                courseSelect[key] = new Option(course.course_name, course.id);
            });
        })
        .catch(function(error) {
            console.log(error);
        });
    }
</script>
@endsection
