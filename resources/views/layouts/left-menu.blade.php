<div class="col-xs-1 col-sm-1 col-md-1 col-lg-2 col-xl-2 col-xxl-2 border-rt-e6 px-0">
    <div class="d-flex flex-column align-items-center align-items-sm-start ">
                <ul class="nav flex-column pt-2 w-100">

                    {{-- ── DASHBOARD (all roles) ── --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('home')? 'active' : '' }}" href="{{url('home')}}"><i class="ms-auto bi bi-grid"></i> <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">{{ __('Dashboard') }}</span></a>
                    </li>

                    {{-- ── CLASSES (admin + teacher via permission) ── --}}
                    @can('view classes')
                    <li class="nav-item">
                        @php
                            if (session()->has('browse_session_id')){
                                $classCount = \App\Models\SchoolClass::where('session_id', session('browse_session_id'))->count();
                            } else {
                                $latest_session = \App\Models\SchoolSession::latest()->first();
                                if($latest_session) {
                                    $classCount = \App\Models\SchoolClass::where('session_id', $latest_session->id)->count();
                                } else {
                                    $classCount = 0;
                                }
                            }
                        @endphp
                        <a class="nav-link d-flex {{ request()->is('classes')? 'active' : '' }}" href="{{url('classes')}}"><i class="bi bi-diagram-3"></i> <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Classes</span> <span class="ms-auto d-inline d-sm-none d-md-none d-xl-inline">{{ $classCount }}</span></a>
                    </li>
                    @endcan

                    {{-- ── TEACHER ONLY ── --}}
                    @if(Auth::user()->role == "teacher")

                    {{-- Students --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('students*') ? 'active' : '' }}" href="{{ route('student.list.show') }}">
                            <i class="bi bi-person-lines-fill"></i>
                            <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Students</span>
                        </a>
                    </li>

                    {{-- Attendance --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('attendances*') ? 'active' : '' }}" href="{{ url('attendances') }}">
                            <i class="bi bi-calendar2-check"></i>
                            <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Attendance</span>
                        </a>
                    </li>

                    {{-- Marks — shown based on CT class type --}}
                    @php
                        $menuSession = session('browse_session_id') ?: optional(\App\Models\SchoolSession::orderBy('id','desc')->first())->id;
                        $menuCT = \App\Models\ClassTeacher::with('schoolClass')
                            ->where('teacher_id', Auth::user()->id)
                            ->where('session_id', $menuSession)
                            ->first();
                        $menuIsPP = $menuCT
                            ? \App\Http\Controllers\PrePrimaryController::getPrePrimaryType(optional($menuCT->schoolClass)->class_name ?? '')
                            : null;
                        $menuIs18 = $menuCT && !$menuIsPP;
                        // Also check if subject teacher (non-CT) — show Class 1-8 marks
                        $menuIsSubjectTeacher = !$menuCT && \App\Models\SubjectTeacher::where('teacher_id', Auth::user()->id)->where('session_id', $menuSession)->exists();
                    @endphp
                    <li class="nav-item">
                        <a type="button" href="#teacher-marks-submenu" data-bs-toggle="collapse"
                            class="d-flex nav-link {{ request()->is('marks2*') || request()->is('preprimary*') ? 'active' : '' }}">
                            <i class="bi bi-pencil-square"></i>
                            <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Marks</span>
                            <i class="ms-auto d-inline d-sm-none d-md-none d-xl-inline bi bi-chevron-down"></i>
                        </a>
                        <ul class="nav collapse {{ request()->is('marks2*') || request()->is('preprimary*') ? 'show' : 'hide' }} bg-white" id="teacher-marks-submenu">
                            @if($menuIsPP)
                            {{-- Pre-primary CT: only pre-primary entry --}}
                            <li class="nav-item w-100">
                                <a class="nav-link {{ request()->is('preprimary/entry*') ? 'active' : '' }}"
                                    href="{{ route('preprimary.entry', ['class_id' => $menuCT->class_id, 'section_id' => $menuCT->section_id]) }}">
                                    <i class="bi bi-check2-square me-2"></i> Skill Entry
                                </a>
                            </li>
                            <li class="nav-item w-100">
                                <a class="nav-link {{ request()->is('preprimary/narratives*') ? 'active' : '' }}"
                                    href="{{ route('preprimary.narratives', ['class_id' => $menuCT->class_id, 'section_id' => $menuCT->section_id]) }}">
                                    <i class="bi bi-chat-left-text me-2"></i> Remarks
                                </a>
                            </li>
                            @else
                            {{-- Class 1-8 CT or subject teacher: marks entry only --}}
                            <li class="nav-item w-100">
                                <a class="nav-link {{ request()->is('marks2') ? 'active' : '' }}" href="{{ route('marks.index') }}">
                                    <i class="bi bi-pencil me-2"></i> Enter Marks
                                </a>
                            </li>
                            @if($menuIs18)
                            <li class="nav-item w-100">
                                <a class="nav-link {{ request()->is('marks2/review*') ? 'active' : '' }}" href="{{ route('marks.review') }}">
                                    <i class="bi bi-grid-3x3-gap me-2"></i> Marks Review
                                </a>
                            </li>
                            @endif
                            @endif
                        </ul>
                    </li>


                    @endif

                    {{-- ── STUDENT ONLY ── --}}
                    @if(Auth::user()->role == "student")
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('student.attendance.show') ? 'active' : '' }}"
                            href="{{ route('student.attendance.show', ['id' => Auth::user()->id]) }}">
                            <i class="bi bi-calendar2-week"></i>
                            <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Attendance</span>
                        </a>
                    </li>
                    <li class="nav-item border-bottom">
                        <a class="nav-link {{ request()->routeIs('marks.reportCard') ? 'active' : '' }}" href="{{ route('marks.reportCard') }}">
                            <i class="bi bi-pencil-square"></i>
                            <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">My Marks</span>
                        </a>
                    </li>
                    @endif

                    {{-- Exams / Grades section removed — replaced by new Marks system (/marks2) --}}

                    {{-- ── ADMIN ONLY ── --}}
                    @if (Auth::user()->role == "admin")

                    {{-- Teachers --}}
                    <li class="nav-item">
                        <a type="button" href="#admin-teacher-submenu" data-bs-toggle="collapse" class="d-flex nav-link {{ request()->is('teachers*') ? 'active' : '' }}">
                            <i class="bi bi-person-video2"></i>
                            <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Teachers</span>
                            <i class="ms-auto d-inline d-sm-none d-md-none d-xl-inline bi bi-chevron-down"></i>
                        </a>
                        <ul class="nav collapse {{ request()->is('teachers*') ? 'show' : 'hide' }} bg-white" id="admin-teacher-submenu">
                            <li class="nav-item w-100"><a class="nav-link" href="{{ route('teacher.list.show') }}"><i class="bi bi-list-ul me-2"></i> All Teachers</a></li>
                            <li class="nav-item w-100"><a class="nav-link" href="{{ route('teacher.create.show') }}"><i class="bi bi-person-plus me-2"></i> Add Teacher</a></li>
                        </ul>
                    </li>

                    {{-- Admissions --}}
                    <li class="nav-item">
                        <a type="button" href="#admission-submenu" data-bs-toggle="collapse" class="d-flex nav-link {{ request()->is('admissions*')? 'active' : '' }}">
                            <i class="bi bi-person-plus-fill"></i>
                            <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Admissions</span>
                            <i class="ms-auto d-inline d-sm-none d-md-none d-xl-inline bi bi-chevron-down"></i>
                        </a>
                        <ul class="nav collapse {{ request()->is('admissions*') || request()->is('import*') ? 'show' : 'hide' }} bg-white" id="admission-submenu">
                            <li class="nav-item w-100"><a class="nav-link" href="{{ route('admissions.index') }}"><i class="bi bi-list-ul me-2"></i> All Admissions</a></li>
                            <li class="nav-item w-100"><a class="nav-link" href="{{ route('admissions.create') }}"><i class="bi bi-person-plus me-2"></i> New Inquiry</a></li>
                            <li class="nav-item w-100"><a class="nav-link" href="{{ route('admissions.cancelled') }}"><i class="bi bi-archive me-2"></i> Cancelled</a></li>
                            <li class="nav-item w-100"><a class="nav-link {{ request()->is('import*') ? 'active' : '' }}" href="{{ route('import.students') }}"><i class="bi bi-file-earmark-arrow-up me-2"></i> Import Students</a></li>
                        </ul>
                    </li>

                    {{-- Exit Formalities --}}
                    <li class="nav-item">
                        <a type="button" href="#exit-submenu" data-bs-toggle="collapse" class="d-flex nav-link {{ request()->is('lc*')? 'active' : '' }}">
                            <i class="bi bi-box-arrow-right"></i>
                            <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Exit Formalities</span>
                            <i class="ms-auto d-inline d-sm-none d-md-none d-xl-inline bi bi-chevron-down"></i>
                        </a>
                        <ul class="nav collapse {{ request()->is('lc*')? 'show' : 'hide' }} bg-white" id="exit-submenu">
                            <li class="nav-item w-100"><a class="nav-link" href="{{ route('lc.index') }}"><i class="bi bi-file-earmark-minus me-2"></i> Leaving Certificates</a></li>
                            <li class="nav-item w-100"><a class="nav-link {{ request()->routeIs('exits.*') ? 'active' : '' }}" href="{{ route('exits.index') }}"><i class="bi bi-box-arrow-right me-2"></i> Student Exits</a></li>
                        </ul>
                    </li>

                    {{-- Fees --}}
                    @php
                        $latestSessionForMenu = \App\Models\SchoolSession::orderBy('id','desc')->first();
                        $feeStructureCount = $latestSessionForMenu
                            ? \App\Models\FeeStructure::where('session_id', $latestSessionForMenu->id)->count()
                            : 0;
                        $feeStructureMissing = ($feeStructureCount === 0);
                    @endphp
                    <li class="nav-item">
                        <a type="button" href="#fees-submenu" data-bs-toggle="collapse" class="d-flex nav-link {{ request()->is('fees*') || request()->is('fee-structures*') ? 'active' : '' }}">
                            <i class="bi bi-cash-stack"></i>
                            <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Fees</span>
                            @if($feeStructureMissing)
                                <span class="badge bg-warning text-dark ms-1 d-inline d-sm-none d-md-none d-xl-inline" title="Fee structure not set up for {{ $latestSessionForMenu->session_name }}">!</span>
                            @endif
                            <i class="ms-auto d-inline d-sm-none d-md-none d-xl-inline bi bi-chevron-down"></i>
                        </a>
                        <ul class="nav collapse {{ request()->is('fees*') || request()->is('fee-structures*') ? 'show' : 'hide' }} bg-white" id="fees-submenu">
                            <li class="nav-item w-100">
                                <a class="nav-link {{ request()->routeIs('fees.collect') ? 'active' : '' }}" href="{{ route('fees.collect') }}">
                                    <i class="bi bi-cash-coin me-2"></i> Collect Fee
                                </a>
                            </li>
                            <li class="nav-item w-100">
                                <a class="nav-link {{ $feeStructureMissing ? 'text-warning fw-bold' : '' }}" href="{{ route('fee-structures.index') }}">
                                    <i class="bi bi-table me-2"></i> Fee Structures
                                    @if($feeStructureMissing)
                                        <span class="badge bg-warning text-dark ms-1">Setup needed</span>
                                    @endif
                                </a>
                            </li>
                        </ul>
                    </li>

                    {{-- Reports --}}
                    <li class="nav-item">
                        <a type="button" href="#reports-submenu" data-bs-toggle="collapse" class="d-flex nav-link {{ request()->is('reports*') ? 'active' : '' }}">
                            <i class="bi bi-bar-chart-line"></i>
                            <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Reports</span>
                            <i class="ms-auto d-inline d-sm-none d-md-none d-xl-inline bi bi-chevron-down"></i>
                        </a>
                        <ul class="nav collapse {{ request()->is('reports*') ? 'show' : 'hide' }} bg-white" id="reports-submenu">
                            {{-- Daily Collection hidden — redundant with Collection Report, pending Angela confirmation --}}
                            <li class="nav-item w-100"><a class="nav-link" href="{{ route('reports.fees.dateRange') }}"><i class="bi bi-calendar-range me-2"></i> Collection Report</a></li>
                            <li class="nav-item w-100"><a class="nav-link" href="{{ route('reports.fees.defaulters') }}"><i class="bi bi-exclamation-triangle me-2"></i> Defaulters</a></li>
                            <li class="nav-item w-100"><a class="nav-link" href="{{ route('reports.fees.categorySummary') }}"><i class="bi bi-pie-chart me-2"></i> Category Summary</a></li>
                            <li class="nav-item w-100"><a class="nav-link" href="{{ route('reports.admissions') }}"><i class="bi bi-person-check me-2"></i> Admissions</a></li>
                            <li class="nav-item w-100"><a class="nav-link" href="{{ route('reports.classStrength') }}"><i class="bi bi-people me-2"></i> Class Strength</a></li>
                            <li class="nav-item w-100"><a class="nav-link" href="{{ route('reports.rte') }}"><i class="bi bi-star me-2"></i> RTE Students</a></li>
                            <li class="nav-item w-100"><a class="nav-link" href="{{ route('reports.miscSales') }}"><i class="bi bi-bag me-2"></i> Misc Sales</a></li>
                        </ul>
                    </li>

                    {{-- Academic Settings --}}
                    <li class="nav-item">
                        <a type="button" href="#academic-submenu" data-bs-toggle="collapse" class="d-flex nav-link {{ request()->is('academics*') ? 'active' : '' }}">
                            <i class="bi bi-tools"></i>
                            <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Academic</span>
                            <i class="ms-auto d-inline d-sm-none d-md-none d-xl-inline bi bi-chevron-down"></i>
                        </a>
                        <ul class="nav collapse {{ request()->is('academics*') ? 'show' : 'hide' }} bg-white" id="academic-submenu">
                            <li class="nav-item w-100"><a class="nav-link" href="{{ url('academics/settings') }}"><i class="bi bi-gear me-2"></i> Settings</a></li>
                            <li class="nav-item w-100"><a class="nav-link" href="{{ route('subjects.index') }}"><i class="bi bi-book me-2"></i> Subjects</a></li>
                            <li class="nav-item w-100"><a class="nav-link" href="{{ route('academics.teacher-assignments') }}"><i class="bi bi-person-badge me-2"></i> Teacher Assignments</a></li>
                            <li class="nav-item w-100"><a class="nav-link {{ request()->is('marks2') ? 'active' : '' }}" href="{{ route('marks.index') }}"><i class="bi bi-pencil-square me-2"></i> Enter Marks (Cl. 1–8)</a></li>
                            <li class="nav-item w-100"><a class="nav-link {{ request()->is('marks2/review*') ? 'active' : '' }}" href="{{ route('marks.review') }}"><i class="bi bi-grid-3x3-gap me-2"></i> Marks Review</a></li>
                            <li class="nav-item w-100"><a class="nav-link {{ request()->is('preprimary*') ? 'active' : '' }}" href="{{ route('preprimary.entry') }}"><i class="bi bi-check2-square me-2"></i> Pre-Primary Entry</a></li>
                        </ul>
                    </li>

                    {{-- Promotion --}}
                    @if (!session()->has('browse_session_id'))
                    @php
                        // Check if any students from previous session are not yet promoted to latest session
                        $prevSession = \App\Models\SchoolSession::orderBy('id','desc')->skip(1)->first();
                        $promotionPending = false;
                        if ($prevSession && $latestSessionForMenu && $prevSession->id !== $latestSessionForMenu->id) {
                            $prevCount = \App\Models\Promotion::where('session_id', $prevSession->id)->count();
                            $newCount  = \App\Models\Promotion::where('session_id', $latestSessionForMenu->id)->count();
                            // Count graduated students — they are intentionally not promoted
                            $graduatedCount = \App\Models\User::where('student_status', 'graduated')->count();
                            $promotionPending = ($prevCount > 0 && $newCount < ($prevCount - $graduatedCount));
                        }
                    @endphp
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('promotions*')? 'active' : '' }}" href="{{url('promotions/index')}}">
                            <i class="bi bi-sort-numeric-up-alt"></i>
                            <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Promotion</span>
                            @if($promotionPending)
                                <span class="badge bg-warning text-dark ms-1 d-inline d-sm-none d-md-none d-xl-inline" title="Some students not yet promoted">!</span>
                            @endif
                        </a>
                    </li>
                    @endif

                    {{-- Academic utilities --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('syllabus*')? 'active' : '' }}" href="{{route('class.syllabus.create')}}"><i class="bi bi-journal-text"></i> <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Syllabus</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('routine*')? 'active' : '' }}" href="{{route('section.routine.create')}}"><i class="bi bi-calendar4-range"></i> <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Routine</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('notice*')? 'active' : '' }}" href="{{route('notice.create')}}"><i class="bi bi-megaphone"></i> <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Notice</span></a>
                    </li>
                    <li class="nav-item border-bottom">
                        <a class="nav-link {{ request()->is('calendar-event*')? 'active' : '' }}" href="{{route('events.show')}}"><i class="bi bi-calendar-event"></i> <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Event</span></a>
                    </li>

                    {{-- Placeholders --}}
                    <li class="nav-item">
                        <a class="nav-link disabled" href="#" aria-disabled="true"><i class="bi bi-person-lines-fill"></i> <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Staff</span></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link disabled" href="#" aria-disabled="true"><i class="bi bi-journals"></i> <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Library</span></a>
                    </li>

                    @endif {{-- end admin only --}}

                </ul>
            </div>
        </div>
