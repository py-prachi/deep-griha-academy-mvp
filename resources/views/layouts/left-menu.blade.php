<div class="col-xs-1 col-sm-1 col-md-1 col-lg-2 col-xl-2 col-xxl-2 px-0 dga-sidebar">
    <div class="d-flex flex-column align-items-center align-items-sm-start">
                <ul class="nav flex-column pt-2 w-100">

                    {{-- ── DASHBOARD (all roles) ── --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('home')? 'active' : '' }}" href="{{url('home')}}"><i class="ms-auto bi bi-grid"></i> <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">{{ __('Dashboard') }}</span></a>
                    </li>

                    @php
                        if (session()->has('browse_session_id')) {
                            $classCount = \App\Models\SchoolClass::where('session_id', session('browse_session_id'))->count();
                        } else {
                            $latest_session = \App\Models\SchoolSession::latest()->first();
                            $classCount = $latest_session ? \App\Models\SchoolClass::where('session_id', $latest_session->id)->count() : 0;
                        }
                    @endphp

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

                    {{-- Learning Standard --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('learning-standard*') ? 'active' : '' }}" href="{{ route('lesson-plans.index') }}">
                            <i class="bi bi-journal-text"></i>
                            <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Learning Standard</span>
                        </a>
                    </li>

                    {{-- Lesson Planning (Class 1-8) --}}
                    @php
                        $menuSessionId2 = session('browse_session_id') ?: optional(\App\Models\SchoolSession::orderBy('id','desc')->first())->id;
                        // Match by LOWER(class_name) LIKE, not exact strings — actual class
                        // names are "Nursery", "Lower KG", "Upper KG", not "LKG"/"UKG".
                        $isPrePrimaryCT = auth()->user()->role === 'teacher' &&
                            \App\Models\ClassTeacher::where('teacher_id', auth()->id())
                                ->where('session_id', $menuSessionId2)
                                ->whereHas('schoolClass', function($q){
                                    $q->whereRaw('LOWER(class_name) LIKE ?', ['%nursery%'])
                                      ->orWhereRaw('LOWER(class_name) LIKE ?', ['%kg%']);
                                })
                                ->exists();
                        $showLessonPlanning = auth()->user()->role === 'admin' || $isPrePrimaryCT || (
                            auth()->user()->role === 'teacher' &&
                            \App\Models\SubjectTeacher::where('teacher_id', auth()->id())
                                ->where('session_id', $menuSessionId2)
                                ->where(function($q) {
                                    // Any non-pre-primary class assignment, OR any
                                    // pre-primary assignment where this teacher isn't
                                    // that class's own CT — a dedicated specialist
                                    // subject teacher (PE, Agriculture, or any future
                                    // subject) rather than the CT covering it via
                                    // Daily Plans.
                                    $q->whereHas('schoolClass', function($sub){
                                        $sub->whereRaw('LOWER(class_name) NOT LIKE ?', ['%nursery%'])
                                            ->whereRaw('LOWER(class_name) NOT LIKE ?', ['%kg%']);
                                    })->orWhereNotExists(function($sub){
                                        $sub->from('class_teachers')
                                            ->whereColumn('class_teachers.teacher_id', 'subject_teachers.teacher_id')
                                            ->whereColumn('class_teachers.class_id', 'subject_teachers.class_id')
                                            ->whereColumn('class_teachers.section_id', 'subject_teachers.section_id')
                                            ->whereColumn('class_teachers.session_id', 'subject_teachers.session_id');
                                    });
                                })
                                ->exists()
                        );
                        // A pre-primary CT sees Lesson Planning too now (read-only, for
                        // her specialist subject-teachers' entries), so she's not gated
                        // behind $showPreschoolPlan the same way as before.
                        $showPreschoolPlan = auth()->user()->role === 'admin' || $isPrePrimaryCT;
                    @endphp

                    @if($showLessonPlanning)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('lesson-planning*') ? 'active' : '' }}" href="{{ route('lesson-planning.index') }}">
                            <i class="bi bi-journal-text"></i>
                            <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Lesson &amp; Module Planning</span>
                        </a>
                    </li>
                    @endif

                    @if($showPreschoolPlan)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('preschool-plans*') ? 'active' : '' }}" href="{{ route('preschool-plans.index') }}">
                            <i class="bi bi-stars"></i>
                            <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Pre-School Plan</span>
                        </a>
                    </li>
                    @endif

                    {{-- Diagnostic Test Results --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('diagnostics*') ? 'active' : '' }}" href="{{ route('diagnostics.index') }}">
                            <i class="bi bi-clipboard-data"></i>
                            <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Diagnostic Results</span>
                        </a>
                    </li>

                    {{-- Timetable --}}
                    <li class="nav-item">
                        <a type="button" href="#teacher-timetable-submenu" data-bs-toggle="collapse"
                            class="d-flex nav-link {{ request()->is('timetable*') ? 'active' : '' }}">
                            <i class="bi bi-calendar4-week"></i>
                            <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Timetable</span>
                            <i class="ms-auto d-inline d-sm-none d-md-none d-xl-inline bi bi-chevron-down"></i>
                        </a>
                        <ul class="nav collapse {{ request()->is('timetable*') ? 'show' : 'hide' }} bg-white" id="teacher-timetable-submenu">
                            <li class="nav-item w-100">
                                <a class="nav-link {{ request()->routeIs('timetable.teacher') ? 'active' : '' }}" href="{{ route('timetable.teacher') }}">
                                    <i class="bi bi-person-lines-fill me-2"></i> My Timetable
                                </a>
                            </li>
                            <li class="nav-item w-100">
                                <a class="nav-link {{ request()->routeIs('timetable.edit') || request()->routeIs('timetable.show') ? 'active' : '' }}" href="{{ route('timetable.edit') }}">
                                    <i class="bi bi-pencil-square me-2"></i> Enter Timetable
                                </a>
                            </li>
                        </ul>
                    </li>

                    {{-- Marks — Pre-Primary and Class 1-8 sections are independent, since a
                         teacher can be CT/subject-teacher of both at once (e.g. CT of UKG
                         who also teaches a Class 1 subject) and needs both. --}}
                    @php
                        $menuSession = session('browse_session_id') ?: optional(\App\Models\SchoolSession::orderBy('id','desc')->first())->id;
                        // A teacher may be CT for more than one class (e.g. one teacher
                        // covering both Nursery and Lower KG) — don't assume just one.
                        $menuCTs = \App\Models\ClassTeacher::with('schoolClass')
                            ->where('teacher_id', Auth::user()->id)
                            ->where('session_id', $menuSession)
                            ->get();
                        $menuPreprimaryCTs = $menuCTs->filter(fn($a) =>
                            \App\Http\Controllers\PrePrimaryController::getPrePrimaryType(optional($a->schoolClass)->class_name ?? '')
                        );
                        $menuIsPP        = $menuPreprimaryCTs->isNotEmpty();
                        $menuIsMultiPPCt = $menuPreprimaryCTs->count() > 1;
                        $menuFirstPPCt   = $menuPreprimaryCTs->first();
                        $menuIsClass18CT = $menuCTs->count() > $menuPreprimaryCTs->count();
                        // Reuses the same "has a real Class 1-8 subject assignment" check as
                        // the Lesson Planning link, so a pre-primary-only subject assignment
                        // (e.g. English for LKG) doesn't wrongly unlock Class 1-8 Marks.
                        $menuIsAnyClass18SubjectTeacher = \App\Models\SubjectTeacher::where('teacher_id', Auth::user()->id)
                            ->where('session_id', $menuSession)
                            ->whereHas('schoolClass', function($q){
                                $q->whereRaw('LOWER(class_name) NOT LIKE ?', ['%nursery%'])
                                  ->whereRaw('LOWER(class_name) NOT LIKE ?', ['%kg%']);
                            })
                            ->exists();
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
                            {{-- Pre-primary CT: skill entry + remarks. If CT for more than one
                                 pre-school class, link to the bare route so the class picker shows. --}}
                            <li class="nav-item w-100">
                                <a class="nav-link {{ request()->is('preprimary/entry*') ? 'active' : '' }}"
                                    href="{{ $menuIsMultiPPCt ? route('preprimary.entry') : route('preprimary.entry', ['class_id' => $menuFirstPPCt->class_id, 'section_id' => $menuFirstPPCt->section_id]) }}">
                                    <i class="bi bi-check2-square me-2"></i> Skill Entry
                                </a>
                            </li>
                            <li class="nav-item w-100">
                                <a class="nav-link {{ request()->is('preprimary/narratives*') ? 'active' : '' }}"
                                    href="{{ $menuIsMultiPPCt ? route('preprimary.narratives') : route('preprimary.narratives', ['class_id' => $menuFirstPPCt->class_id, 'section_id' => $menuFirstPPCt->section_id]) }}">
                                    <i class="bi bi-chat-left-text me-2"></i> Remarks
                                </a>
                            </li>
                            @endif
                            {{-- Class 1-8: Enter Marks only for subject teachers; CT-only gets View only --}}
                            @if($menuIsAnyClass18SubjectTeacher)
                            <li class="nav-item w-100">
                                <a class="nav-link {{ request()->is('marks2') && !request()->is('marks2/review*') ? 'active' : '' }}" href="{{ route('marks.index') }}">
                                    <i class="bi bi-pencil me-2"></i> Enter Marks
                                </a>
                            </li>
                            @endif
                            @if($menuIsClass18CT || $menuIsAnyClass18SubjectTeacher)
                            <li class="nav-item w-100">
                                <a class="nav-link {{ request()->is('marks2/review*') ? 'active' : '' }}" href="{{ route('marks.review') }}">
                                    <i class="bi bi-grid-3x3-gap me-2"></i> Marks Review
                                </a>
                            </li>
                            @endif
                        </ul>
                    </li>


                    {{-- ── TEACHER: Events ── --}}
                    <li class="nav-item">
                        <a type="button" href="#teacher-events-submenu" data-bs-toggle="collapse"
                            class="d-flex nav-link {{ request()->is('calendar-event*') || request()->is('events/report*') ? 'active' : '' }}">
                            <i class="bi bi-calendar-event"></i>
                            <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Events</span>
                            <i class="ms-auto d-inline d-sm-none d-md-none d-xl-inline bi bi-chevron-down"></i>
                        </a>
                        <ul class="nav collapse {{ request()->is('calendar-event*') || request()->is('events/report*') ? 'show' : 'hide' }} bg-white" id="teacher-events-submenu">
                            <li class="nav-item w-100"><a class="nav-link {{ request()->is('calendar-event*') ? 'active' : '' }}" href="{{ route('events.show') }}"><i class="bi bi-calendar-plus me-2"></i> Log Activity</a></li>
                            <li class="nav-item w-100"><a class="nav-link {{ request()->is('events/report*') ? 'active' : '' }}" href="{{ route('events.report') }}"><i class="bi bi-table me-2"></i> My Activities</a></li>
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
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('timetable.student') ? 'active' : '' }}" href="{{ route('timetable.student') }}">
                            <i class="bi bi-calendar4-week"></i>
                            <span class="ms-1 d-inline d-sm-none d-md-none d-xl-inline">Timetable</span>
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

                    @php
                        $latestSessionForMenu = \App\Models\SchoolSession::orderBy('id','desc')->first();
                        $feeStructureCount = $latestSessionForMenu
                            ? \App\Models\FeeStructure::where('session_id', $latestSessionForMenu->id)->count()
                            : 0;
                        $feeStructureMissing = ($feeStructureCount === 0);
                        $prevSession = \App\Models\SchoolSession::orderBy('id','desc')->skip(1)->first();
                        $promotionPending = false;
                        if ($prevSession && $latestSessionForMenu && $prevSession->id !== $latestSessionForMenu->id) {
                            $prevCount = \App\Models\Promotion::where('session_id', $prevSession->id)->count();
                            $newCount  = \App\Models\Promotion::where('session_id', $latestSessionForMenu->id)->count();
                            $graduatedCount = \App\Models\User::whereIn('student_status', ['graduated', 'exited'])->count();
                            $promotionPending = ($prevCount > 0 && $newCount < ($prevCount - $graduatedCount));
                        }
                    @endphp

                    {{-- ── SECTION: PEOPLE ── --}}
                    <li class="nav-item px-2 pt-2 pb-1 d-none d-xl-block">
                        <span style="font-size:0.68rem;font-weight:700;letter-spacing:0.08em;color:#adb5bd;text-transform:uppercase;">People</span>
                    </li>
                    <li class="nav-item d-xl-none"><hr class="my-1 mx-2"></li>

                    {{-- Teachers --}}
                    <li class="nav-item">
                        <a type="button" href="#admin-teacher-submenu" data-bs-toggle="collapse"
                            class="d-flex nav-link {{ request()->is('teachers*') || request()->is('academics/teacher*') ? 'active' : '' }}">
                            <i class="bi bi-person-video2"></i>
                            <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Teachers</span>
                            <i class="ms-auto d-inline d-sm-none d-md-none d-xl-inline bi bi-chevron-down"></i>
                        </a>
                        <ul class="nav collapse {{ request()->is('teachers*') || request()->is('academics/teacher*') ? 'show' : 'hide' }} bg-white" id="admin-teacher-submenu">
                            <li class="nav-item w-100"><a class="nav-link {{ request()->routeIs('teacher.list.show') ? 'active' : '' }}" href="{{ route('teacher.list.show') }}"><i class="bi bi-list-ul me-2"></i> All Teachers</a></li>
                            <li class="nav-item w-100"><a class="nav-link {{ request()->routeIs('teacher.create.show') ? 'active' : '' }}" href="{{ route('teacher.create.show') }}"><i class="bi bi-person-plus me-2"></i> Add Teacher</a></li>
                            <li class="nav-item w-100"><a class="nav-link {{ request()->is('academics/teacher*') ? 'active' : '' }}" href="{{ route('academics.teacher-assignments') }}"><i class="bi bi-person-badge me-2"></i> Class Assignments</a></li>
                        </ul>
                    </li>

                    {{-- Admissions --}}
                    <li class="nav-item">
                        <a type="button" href="#admission-submenu" data-bs-toggle="collapse" class="d-flex nav-link {{ request()->is('admissions*') || request()->is('import*') ? 'active' : '' }}">
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

                    {{-- Students --}}
                    @if(auth()->user()->role === 'admin')
                    <li class="nav-item">
                        <a type="button" href="#students-submenu" data-bs-toggle="collapse"
                            class="d-flex nav-link {{ request()->is('students*') || request()->routeIs('counselling.*') ? 'active' : '' }}">
                            <i class="bi bi-people"></i>
                            <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Students</span>
                            <i class="ms-auto d-inline d-sm-none d-md-none d-xl-inline bi bi-chevron-down"></i>
                        </a>
                        <ul class="nav collapse {{ request()->is('students*') || request()->routeIs('counselling.*') ? 'show' : 'hide' }} bg-white" id="students-submenu">
                            <li class="nav-item w-100"><a class="nav-link {{ request()->is('students*') ? 'active' : '' }}" href="{{ route('student.list.show') }}"><i class="bi bi-person-lines-fill me-2"></i> All Students</a></li>
                            <li class="nav-item w-100"><a class="nav-link {{ request()->routeIs('counselling.index') ? 'active' : '' }}" href="{{ route('counselling.index') }}"><i class="bi bi-journal-medical me-2"></i> Counselling</a></li>
                            <li class="nav-item w-100"><a class="nav-link {{ request()->routeIs('counselling.report') ? 'active' : '' }}" href="{{ route('counselling.report') }}"><i class="bi bi-bar-chart-line me-2"></i> Counselling Report</a></li>
                        </ul>
                    </li>
                    @else
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('students*') ? 'active' : '' }}" href="{{ route('student.list.show') }}">
                            <i class="bi bi-people"></i>
                            <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Students</span>
                        </a>
                    </li>
                    @endif

                    {{-- Exit Formalities --}}
                    <li class="nav-item">
                        <a type="button" href="#exit-submenu" data-bs-toggle="collapse" class="d-flex nav-link {{ request()->is('lc*') || request()->routeIs('exits.*') ? 'active' : '' }}">
                            <i class="bi bi-box-arrow-right"></i>
                            <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Exit Formalities</span>
                            <i class="ms-auto d-inline d-sm-none d-md-none d-xl-inline bi bi-chevron-down"></i>
                        </a>
                        <ul class="nav collapse {{ request()->is('lc*') || request()->routeIs('exits.*') ? 'show' : 'hide' }} bg-white" id="exit-submenu">
                            <li class="nav-item w-100"><a class="nav-link" href="{{ route('lc.index') }}"><i class="bi bi-file-earmark-minus me-2"></i> Leaving Certificates</a></li>
                            <li class="nav-item w-100"><a class="nav-link {{ request()->routeIs('exits.*') ? 'active' : '' }}" href="{{ route('exits.index') }}"><i class="bi bi-door-open me-2"></i> Student Exits</a></li>
                        </ul>
                    </li>

                    {{-- ── SECTION: FINANCE ── --}}
                    <li class="nav-item px-2 pt-3 pb-1 d-none d-xl-block">
                        <span style="font-size:0.68rem;font-weight:700;letter-spacing:0.08em;color:#adb5bd;text-transform:uppercase;">Finance</span>
                    </li>
                    <li class="nav-item d-xl-none"><hr class="my-1 mx-2"></li>

                    {{-- Fees --}}
                    <li class="nav-item">
                        <a type="button" href="#fees-submenu" data-bs-toggle="collapse" class="d-flex nav-link {{ request()->is('fees*') || request()->is('fee-structures*') ? 'active' : '' }}">
                            <i class="bi bi-cash-stack"></i>
                            <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Fees</span>
                            @if($feeStructureMissing)
                                <span class="badge bg-warning text-dark ms-1 d-inline d-sm-none d-md-none d-xl-inline" title="Fee structure not set up">!</span>
                            @endif
                            <i class="ms-auto d-inline d-sm-none d-md-none d-xl-inline bi bi-chevron-down"></i>
                        </a>
                        <ul class="nav collapse {{ request()->is('fees*') || request()->is('fee-structures*') ? 'show' : 'hide' }} bg-white" id="fees-submenu">
                            <li class="nav-item w-100"><a class="nav-link {{ request()->routeIs('fees.collect') ? 'active' : '' }}" href="{{ route('fees.collect') }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Search a student and record their fee payment"><i class="bi bi-cash-coin me-2"></i> Collect Fee</a></li>
                            @if(Auth::user()->role == 'admin')
                            <li class="nav-item w-100">
                                <a class="nav-link {{ $feeStructureMissing ? 'text-warning fw-bold' : '' }} {{ request()->routeIs('fee-structures.index') ? 'active' : '' }}" href="{{ route('fee-structures.index') }}" data-bs-toggle="tooltip" data-bs-placement="right" title="View and manage the fee structure for each class and category">
                                    <i class="bi bi-table me-2"></i> Fee Structures
                                    @if($feeStructureMissing)<span class="badge bg-warning text-dark ms-1">!</span>@endif
                                </a>
                            </li>
                            <li class="nav-item w-100">
                                <a class="nav-link {{ request()->routeIs('fee-structures.import*') ? 'active' : '' }}" href="{{ route('fee-structures.import') }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Upload fee structure via Excel for the new academic year">
                                    <i class="bi bi-upload me-2"></i> Import Fee Structure
                                </a>
                            </li>
                            <li class="nav-item w-100">
                                <a class="nav-link {{ request()->routeIs('fee-structures.overview') ? 'active' : '' }}" href="{{ route('fee-structures.overview') }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Print-ready fee summary showing all classes, categories and amounts">
                                    <i class="bi bi-printer me-2"></i> Fee Overview / Print
                                </a>
                            </li>
                            <li class="nav-item w-100">
                                <a class="nav-link {{ request()->routeIs('fees.categoryReceipts') ? 'active' : '' }}" href="{{ route('fees.categoryReceipts') }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Record lump sum reimbursements received from Govt (RTE) and COC">
                                    <i class="bi bi-bank me-2"></i> Category Receipts
                                </a>
                            </li>
                            @endif
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
                            <li class="nav-item w-100"><a class="nav-link" href="{{ route('reports.admissions') }}" data-bs-toggle="tooltip" data-bs-placement="right" title="All admission inquiries with status, class, and confirmation details"><i class="bi bi-person-check me-2"></i> Admissions</a></li>
                            <li class="nav-item w-100"><a class="nav-link" href="{{ route('reports.students') }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Pick the student fields you need and download as PDF"><i class="bi bi-person-lines-fill me-2"></i> Student Info</a></li>
                            <li class="nav-item w-100"><a class="nav-link" href="{{ route('reports.classStrength') }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Headcount per class and section for the selected session"><i class="bi bi-people me-2"></i> Class Strength</a></li>
                            <li class="nav-item w-100"><a class="nav-link" href="{{ route('reports.rte') }}" data-bs-toggle="tooltip" data-bs-placement="right" title="RTE, COC, and discount students — fee tracking and application details"><i class="bi bi-star me-2"></i> Special Categories</a></li>
                            <li class="nav-item w-100"><a class="nav-link" href="{{ route('reports.fees.dateRange') }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Fee collected within a date range, filterable by class or category"><i class="bi bi-calendar-range me-2"></i> Collection Report</a></li>
                            <li class="nav-item w-100"><a class="nav-link" href="{{ route('reports.fees.defaulters') }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Students with outstanding fee balance for the current session"><i class="bi bi-exclamation-triangle me-2"></i> Defaulters</a></li>
                            <li class="nav-item w-100"><a class="nav-link" href="{{ route('reports.fees.rollovers') }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Unpaid fees carried over from a previous academic year"><i class="bi bi-arrow-return-right me-2"></i> Carried Forward</a></li>
                            <li class="nav-item w-100"><a class="nav-link" href="{{ route('reports.fees.categorySummary') }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Fee collection totals broken down by student category (General, RTE, COC)"><i class="bi bi-pie-chart me-2"></i> Category Summary</a></li>
                            <li class="nav-item w-100"><a class="nav-link" href="{{ route('reports.miscSales') }}" data-bs-toggle="tooltip" data-bs-placement="right" title="Revenue from uniform, stationery, and other non-fee items sold"><i class="bi bi-bag me-2"></i> Misc Sales</a></li>
                        </ul>
                    </li>

                    {{-- ── SECTION: ACADEMIC ── --}}
                    <li class="nav-item px-2 pt-3 pb-1 d-none d-xl-block">
                        <span style="font-size:0.68rem;font-weight:700;letter-spacing:0.08em;color:#adb5bd;text-transform:uppercase;">Academic</span>
                    </li>
                    <li class="nav-item d-xl-none"><hr class="my-1 mx-2"></li>

                    {{-- Classes --}}
                    @can('view classes')
                    <li class="nav-item">
                        <a class="nav-link d-flex {{ request()->is('classes') ? 'active' : '' }}" href="{{ url('classes') }}">
                            <i class="bi bi-diagram-3"></i>
                            <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Classes</span>
                            <span class="ms-auto d-inline d-sm-none d-md-none d-xl-inline">{{ $classCount }}</span>
                        </a>
                    </li>
                    @endcan

                    {{-- Learning Standard (admin: view only) --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('learning-standard*') ? 'active' : '' }}" href="{{ route('lesson-plans.index') }}">
                            <i class="bi bi-journal-text"></i>
                            <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Learning Standard</span>
                        </a>
                    </li>

                    {{-- Lesson & Module Planning (admin: view all) --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('lesson-planning*') ? 'active' : '' }}" href="{{ route('lesson-planning.index') }}">
                            <i class="bi bi-journal-text"></i>
                            <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Lesson &amp; Module Planning</span>
                        </a>
                    </li>

                    {{-- Pre-School Plans (admin: view all) --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('preschool-plans*') ? 'active' : '' }}" href="{{ route('preschool-plans.index') }}">
                            <i class="bi bi-stars"></i>
                            <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Pre-School Plans</span>
                        </a>
                    </li>

                    {{-- Diagnostic Test Results --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('diagnostics*') ? 'active' : '' }}" href="{{ route('diagnostics.index') }}">
                            <i class="bi bi-clipboard-data"></i>
                            <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Diagnostic Results</span>
                        </a>
                    </li>

                    {{-- Marks & Assessment --}}
                    <li class="nav-item">
                        <a type="button" href="#academic-submenu" data-bs-toggle="collapse"
                            class="d-flex nav-link {{ request()->is('marks2*') || request()->is('preprimary*') || request()->is('subjects*') ? 'active' : '' }}">
                            <i class="bi bi-pencil-square"></i>
                            <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Marks & Assessment</span>
                            <i class="ms-auto d-inline d-sm-none d-md-none d-xl-inline bi bi-chevron-down"></i>
                        </a>
                        <ul class="nav collapse {{ request()->is('marks2*') || request()->is('preprimary*') || request()->is('subjects*') ? 'show' : 'hide' }} bg-white" id="academic-submenu">
                            <li class="nav-item w-100"><a class="nav-link {{ request()->is('subjects*') ? 'active' : '' }}" href="{{ route('subjects.index') }}"><i class="bi bi-book me-2"></i> Subjects</a></li>
                            <li class="nav-item w-100"><a class="nav-link {{ request()->is('marks2/review*') ? 'active' : '' }}" href="{{ route('marks.review') }}"><i class="bi bi-grid-3x3-gap me-2"></i> Marks Review</a></li>
                            <li class="nav-item w-100"><a class="nav-link {{ request()->is('preprimary*') ? 'active' : '' }}" href="{{ route('preprimary.entry') }}"><i class="bi bi-check2-square me-2"></i> Pre-Primary Entry</a></li>
                        </ul>
                    </li>

                    {{-- ── SECTION: COMMUNICATION ── --}}
                    <li class="nav-item px-2 pt-3 pb-1 d-none d-xl-block">
                        <span style="font-size:0.68rem;font-weight:700;letter-spacing:0.08em;color:#adb5bd;text-transform:uppercase;">Communication</span>
                    </li>
                    <li class="nav-item d-xl-none"><hr class="my-1 mx-2"></li>

                    {{-- Communication submenu --}}
                    <li class="nav-item">
                        <a type="button" href="#comms-submenu" data-bs-toggle="collapse"
                            class="d-flex nav-link {{ request()->is('notice*') || request()->is('calendar-event*') || request()->is('events/report*') || request()->is('syllabus*') || request()->is('timetable*') ? 'active' : '' }}">
                            <i class="bi bi-megaphone"></i>
                            <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Communication</span>
                            <i class="ms-auto d-inline d-sm-none d-md-none d-xl-inline bi bi-chevron-down"></i>
                        </a>
                        <ul class="nav collapse {{ request()->is('notice*') || request()->is('calendar-event*') || request()->is('events/report*') || request()->is('syllabus*') || request()->is('timetable*') ? 'show' : 'hide' }} bg-white" id="comms-submenu">
                            <li class="nav-item w-100"><a class="nav-link {{ request()->is('notice*') ? 'active' : '' }}" href="{{ route('notice.create') }}"><i class="bi bi-megaphone me-2"></i> Notice</a></li>
                            <li class="nav-item w-100"><a class="nav-link {{ request()->is('calendar-event*') ? 'active' : '' }}" href="{{ route('events.show') }}"><i class="bi bi-calendar-event me-2"></i> Events</a></li>
                            <li class="nav-item w-100"><a class="nav-link {{ request()->is('events/report*') ? 'active' : '' }}" href="{{ route('events.report') }}"><i class="bi bi-table me-2"></i> Event Report</a></li>
                            <li class="nav-item w-100"><a class="nav-link {{ request()->is('syllabus*') ? 'active' : '' }}" href="{{ route('class.syllabus.create') }}"><i class="bi bi-journal-text me-2"></i> Syllabus</a></li>
                            <li class="nav-item w-100"><a class="nav-link {{ request()->is('timetable*') ? 'active' : '' }}" href="{{ route('timetable.edit') }}"><i class="bi bi-calendar4-week me-2"></i> Timetable</a></li>
                        </ul>
                    </li>

                    {{-- ── SECTION: ADMINISTRATION ── --}}
                    <li class="nav-item px-2 pt-3 pb-1 d-none d-xl-block">
                        <span style="font-size:0.68rem;font-weight:700;letter-spacing:0.08em;color:#adb5bd;text-transform:uppercase;">Administration</span>
                    </li>
                    <li class="nav-item d-xl-none"><hr class="my-1 mx-2"></li>

                    {{-- Settings (year-end & one-time) --}}
                    <li class="nav-item">
                        <a type="button" href="#settings-submenu" data-bs-toggle="collapse"
                            class="d-flex nav-link {{ request()->is('academics/settings*') || request()->is('promotions*') || request()->is('holidays*') || request()->is('special-school-days*') ? 'active' : '' }}">
                            <i class="bi bi-gear"></i>
                            <span class="ms-2 d-inline d-sm-none d-md-none d-xl-inline">Settings</span>
                            @if($promotionPending)
                                <span class="badge bg-warning text-dark ms-1 d-inline d-sm-none d-md-none d-xl-inline">!</span>
                            @endif
                            <i class="ms-auto d-inline d-sm-none d-md-none d-xl-inline bi bi-chevron-down"></i>
                        </a>
                        <ul class="nav collapse {{ request()->is('academics/settings*') || request()->is('promotions*') || request()->is('holidays*') || request()->is('special-school-days*') ? 'show' : 'hide' }} bg-white" id="settings-submenu">
                            @if(session()->has('browse_session_id'))
                            <li class="nav-item w-100">
                                <a class="nav-link text-primary fw-semibold" href="{{ url('academics/settings?clear_browse=1') }}">
                                    <i class="bi bi-arrow-left-circle me-2"></i> Return to Current Session
                                </a>
                            </li>
                            @endif
                            <li class="nav-item w-100">
                                <a class="nav-link {{ request()->is('academics/settings*') ? 'active' : '' }}" href="{{ url('academics/settings') }}">
                                    <i class="bi bi-calendar-plus me-2"></i> Academic Setup
                                </a>
                            </li>
                            <li class="nav-item w-100">
                                <a class="nav-link {{ request()->is('promotions*') ? 'active' : '' }}" href="{{ url('promotions/index') }}">
                                    <i class="bi bi-sort-numeric-up-alt me-2"></i> Promotions
                                    @if($promotionPending)<span class="badge bg-warning text-dark ms-1">!</span>@endif
                                </a>
                            </li>
                            <li class="nav-item w-100">
                                <a class="nav-link {{ request()->is('holidays*') ? 'active' : '' }}" href="{{ route('holidays.index') }}">
                                    <i class="bi bi-calendar-x me-2"></i> Holidays
                                </a>
                            </li>
                            <li class="nav-item w-100">
                                <a class="nav-link {{ request()->is('special-school-days*') ? 'active' : '' }}" href="{{ route('special-school-days.index') }}">
                                    <i class="bi bi-calendar-plus me-2"></i> Special School Days
                                </a>
                            </li>
                        </ul>
                    </li>

                    @endif {{-- end admin only --}}

                </ul>
            </div>
        </div>
