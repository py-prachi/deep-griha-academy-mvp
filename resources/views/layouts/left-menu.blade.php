<div class="col-xs-1 col-sm-1 col-md-1 col-lg-2 col-xl-2 col-xxl-2 border-rt-e6 px-0">
    <div class="d-flex flex-column align-items-center align-items-sm-start">
        <ul class="nav flex-column pt-2 w-100">

            {{-- Dashboard --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->is('home')? 'active' : '' }}"
                   href="{{ url('home') }}">
                    <i class="ms-auto bi bi-grid"></i>
                    <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">
                        Dashboard
                    </span>
                </a>
            </li>

            {{-- Classes --}}
            @can('view classes')
            <li class="nav-item">
                @php
                    if (session()->has('browse_session_id')){
                        $classCount = \App\Models\SchoolClass::where(
                            'session_id',
                            session('browse_session_id')
                        )->count();
                    } else {
                        $latest_session = \App\Models\SchoolSession::latest()->first();

                        if($latest_session) {
                            $classCount = \App\Models\SchoolClass::where(
                                'session_id',
                                $latest_session->id
                            )->count();
                        } else {
                            $classCount = 0;
                        }
                    }
                @endphp

                <a class="nav-link d-flex {{ request()->is('classes')? 'active' : '' }}"
                   href="{{ url('classes') }}">
                    <i class="bi bi-diagram-3"></i>
                    <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">
                        Classes
                    </span>
                    <span class="ms-auto d-inline d-sm-none d-md-none d-xl-inline">
                        {{ $classCount }}
                    </span>
                </a>
            </li>
            @endcan


            {{-- Students / Teachers (Non Student) --}}
            @if(Auth::user()->role != "student")

            {{-- Students --}}
            <li class="nav-item">
                <a href="#student-submenu"
                   data-bs-toggle="collapse"
                   class="d-flex nav-link {{ request()->is('students*')? 'active' : '' }}">
                    <i class="bi bi-person-lines-fill"></i>
                    <span class="ms-2">Students</span>
                    <i class="ms-auto bi bi-chevron-down"></i>
                </a>

                <ul class="nav collapse {{ request()->is('students*')? 'show' : '' }}"
                    id="student-submenu">

                    <li class="nav-item">
                        <a class="nav-link"
                           href="{{ route('student.list.show') }}">
                            View Students
                        </a>
                    </li>

                    @if (!session()->has('browse_session_id') && Auth::user()->role == "admin")
                    <li class="nav-item">
                        <a class="nav-link"
                           href="{{ route('student.create.show') }}">
                            Add Student
                        </a>
                    </li>
                    @endif
                </ul>
            </li>


            {{-- Teachers --}}
            <li class="nav-item">
                <a href="#teacher-submenu"
                   data-bs-toggle="collapse"
                   class="d-flex nav-link {{ request()->is('teachers*')? 'active' : '' }}">
                    <i class="bi bi-person-lines-fill"></i>
                    <span class="ms-2">Teachers</span>
                    <i class="ms-auto bi bi-chevron-down"></i>
                </a>

                <ul class="nav collapse {{ request()->is('teachers*')? 'show' : '' }}"
                    id="teacher-submenu">

                    <li class="nav-item">
                        <a class="nav-link"
                           href="{{ route('teacher.list.show') }}">
                            View Teachers
                        </a>
                    </li>

                    @if (!session()->has('browse_session_id') && Auth::user()->role == "admin")
                    <li class="nav-item">
                        <a class="nav-link"
                           href="{{ route('teacher.create.show') }}">
                            Add Teacher
                        </a>
                    </li>
                    @endif
                </ul>
            </li>

            @endif


            {{-- Teacher Courses --}}
            @if(Auth::user()->role == "teacher")
            <li class="nav-item">
                <a class="nav-link {{ request()->is('courses/teacher*')? 'active' : '' }}"
                   href="{{ route('course.teacher.list.show', ['teacher_id'=>Auth::id()]) }}">
                    <i class="bi bi-journal-medical"></i>
                    My Courses
                </a>
            </li>
            @endif


            {{-- Student Section --}}
            @if(Auth::user()->role == "student")

            {{-- Attendance --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('student.attendance.show')? 'active' : '' }}"
                   href="{{ route('student.attendance.show', ['id'=>Auth::id()]) }}">
                    <i class="bi bi-calendar2-week"></i>
                    Attendance
                </a>
            </li>

            {{-- Courses --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('course.student.list.show')? 'active' : '' }}"
                   href="{{ route('course.student.list.show', ['student_id'=>Auth::id()]) }}">
                    <i class="bi bi-journal-medical"></i>
                    Courses
                </a>
            </li>


            {{-- Routine (SAFE) --}}
            @php
                if (session()->has('browse_session_id')) {
                    $class_info = \App\Models\Promotion::where(
                        'session_id',
                        session('browse_session_id')
                    )->where('student_id', Auth::id())->first();
                } else {
                    $latest_session = \App\Models\SchoolSession::latest()->first();

                    if ($latest_session) {
                        $class_info = \App\Models\Promotion::where(
                            'session_id',
                            $latest_session->id
                        )->where('student_id', Auth::id())->first();
                    } else {
                        $class_info = null;
                    }
                }
            @endphp

            <li class="nav-item border-bottom">
                @if($class_info)
                    <a class="nav-link"
                       href="{{ route('section.routine.show', [
                           'class_id'=>$class_info->class_id,
                           'section_id'=>$class_info->section_id
                       ]) }}">
                        <i class="bi bi-calendar4-range"></i>
                        Routine
                    </a>
                @else
                    <span class="nav-link text-muted">
                        <i class="bi bi-calendar4-range"></i>
                        Routine
                    </span>
                @endif
            </li>

            @endif


            {{-- Admin --}}
            @if(Auth::user()->role == "admin")

            <li class="nav-item">
                <a class="nav-link {{ request()->is('notice*')? 'active' : '' }}"
                   href="{{ route('notice.create') }}">
                    Notice
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link {{ request()->is('academics*')? 'active' : '' }}"
                   href="{{ url('academics/settings') }}">
                    Academic
                </a>
            </li>

            @endif


            {{-- Disabled --}}
            <li class="nav-item">
                <a class="nav-link disabled" href="#">
                    Payment
                </a>
            </li>

        </ul>
    </div>
</div>
