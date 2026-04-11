@extends('layouts.app')

@push('styles')
<style>.border-dashed { border: 2px dashed #dee2e6 !important; }</style>
@endpush

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-3">
                <div class="col ps-4">

                @if(!empty($isStudent))
                {{-- ── STUDENT DASHBOARD ── --}}
                <h5 class="mb-1">
                    Hello, {{ auth()->user()->first_name }}!
                    @if($promotion)
                        <span class="text-muted fw-normal fs-6 ms-2">
                            {{ $promotion->section->schoolClass->class_name ?? '' }}
                            @if($promotion->section) — {{ $promotion->section->section_name }} @endif
                            @if($promotion->roll_number) &nbsp;| Roll No. {{ $promotion->roll_number }} @endif
                        </span>
                    @endif
                </h5>
                <hr class="mt-2 mb-3">

                {{-- Row 1: Attendance + Timetable + Syllabus --}}
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-header py-2 small fw-semibold">
                                <i class="bi bi-calendar2-check me-1"></i> Attendance
                            </div>
                            <div class="card-body text-center py-4">
                                <div class="display-6 fw-bold text-success">{{ $present }}</div>
                                <div class="text-muted small">days present out of {{ $total_days }} recorded</div>
                                <a href="{{ route('student.attendance.show', ['id' => auth()->user()->id]) }}"
                                    class="btn btn-sm btn-outline-secondary mt-3">View full attendance</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 border-dashed">
                            <div class="card-header py-2 small fw-semibold">
                                <i class="bi bi-table me-1"></i> Timetable
                            </div>
                            <div class="card-body text-center py-5 text-muted">
                                <i class="bi bi-clock" style="font-size:2rem;"></i>
                                <div class="small mt-2">Timetable will be available here once entered by the school.</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100 border-dashed">
                            <div class="card-header py-2 small fw-semibold">
                                <i class="bi bi-file-earmark-text me-1"></i> Syllabus
                            </div>
                            <div class="card-body text-center py-5 text-muted">
                                <i class="bi bi-journal-bookmark" style="font-size:2rem;"></i>
                                <div class="small mt-2">Syllabus documents will appear here once uploaded by the school.</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Row 2: Events + Notices --}}
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header py-2 small fw-semibold">
                                <i class="bi bi-calendar-event me-1"></i> Events
                            </div>
                            <div class="card-body text-dark">
                                @include('components.events.event-calendar', ['editable' => 'false', 'selectable' => 'false'])
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header py-2 small fw-semibold d-flex justify-content-between">
                                <span><i class="bi bi-megaphone me-1"></i> Notices</span>
                                {{ $notices->links() }}
                            </div>
                            <div class="card-body p-0">
                                @if(count($notices) < 1)
                                    <p class="text-muted small p-3 mb-0">No notices.</p>
                                @else
                                <div class="accordion accordion-flush" id="noticeAccordion">
                                    @foreach ($notices as $notice)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed py-2 small" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#notice-{{ $notice->id }}">
                                                {{ \Carbon\Carbon::parse($notice->created_at)->format('d M Y') }}
                                            </button>
                                        </h2>
                                        <div id="notice-{{ $notice->id }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}">
                                            <div class="accordion-body small">{!! \Purify::clean($notice->notice) !!}</div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @elseif(!empty($isTeacher))
                {{-- ── TEACHER DASHBOARD ── --}}
                <h5 class="mb-0">Good {{ \Carbon\Carbon::now()->format('G') < 12 ? 'morning' : (\Carbon\Carbon::now()->format('G') < 17 ? 'afternoon' : 'evening') }}, {{ auth()->user()->first_name }}!</h5>
                @if($ct)
                <p class="text-muted small mb-3">
                    Class Teacher &mdash;
                    <strong>{{ $ct->schoolClass->class_name ?? '' }}</strong>
                    @if($ct->section) &mdash; {{ $ct->section->section_name }} @endif
                    &nbsp;|&nbsp; {{ \Carbon\Carbon::today()->format('l, d M Y') }}
                </p>
                @else
                <p class="text-muted small mb-3">You are not assigned as Class Teacher this session.</p>
                @endif

                @if($ct)
                {{-- Row 1: Quick action cards --}}
                <div class="row g-3 mb-4">

                    {{-- Attendance card --}}
                    @php
                        $presentToday  = $attendanceToday->where('status', 'on')->count();
                        $absentToday   = $attendanceToday->where('status', 'off')->count();
                        $lateToday     = $attendanceToday->where('status', 'late')->count();
                        $markedToday   = $attendanceToday->count();
                        $totalStudents = $students->count();
                        $attRoute = $ppType
                            ? route('attendance.create.show', ['class_id' => $ct->class_id, 'section_id' => $ct->section_id])
                            : route('attendance.create.show', ['class_id' => $ct->class_id, 'section_id' => $ct->section_id]);
                    @endphp
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-header py-2 small fw-semibold d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-calendar2-check me-1"></i> Attendance — Today</span>
                                @if($markedToday === 0)
                                    <span class="badge bg-danger">Not marked</span>
                                @else
                                    <span class="badge bg-success">Marked</span>
                                @endif
                            </div>
                            <div class="card-body py-3">
                                @if($markedToday > 0)
                                <div class="d-flex justify-content-around text-center mb-3">
                                    <div><div class="fs-4 fw-bold text-success">{{ $presentToday }}</div><div class="small text-muted">Present</div></div>
                                    <div><div class="fs-4 fw-bold text-danger">{{ $absentToday }}</div><div class="small text-muted">Absent</div></div>
                                    @if($lateToday > 0)
                                    <div><div class="fs-4 fw-bold text-warning">{{ $lateToday }}</div><div class="small text-muted">Late</div></div>
                                    @endif
                                </div>
                                @else
                                <p class="text-muted small mb-3">Attendance not taken yet for today.</p>
                                @endif
                                <a href="{{ route('attendance.create.show', ['class_id' => $ct->class_id, 'section_id' => $ct->section_id]) }}"
                                    class="btn btn-sm btn-outline-primary w-100">
                                    <i class="bi bi-pencil me-1"></i>
                                    {{ $markedToday > 0 ? 'Edit Attendance' : 'Take Attendance' }}
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Marks / Assessment card --}}
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-header py-2 small fw-semibold">
                                <i class="bi bi-pencil-square me-1"></i>
                                {{ $ppType ? 'Skill Assessment' : 'Marks Entry' }}
                            </div>
                            <div class="card-body py-3">
                                @if($ppType)
                                <p class="small text-muted mb-3">Pre-primary skill grading for Term 1 &amp; Term 2.</p>
                                <div class="d-flex flex-column gap-2">
                                    <a href="{{ route('preprimary.entry', ['class_id' => $ct->class_id, 'section_id' => $ct->section_id, 'term' => 1]) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-check2-square me-1"></i> Term 1 Skills
                                    </a>
                                    <a href="{{ route('preprimary.entry', ['class_id' => $ct->class_id, 'section_id' => $ct->section_id, 'term' => 2]) }}"
                                        class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-check2-square me-1"></i> Term 2 Skills
                                    </a>
                                    <a href="{{ route('preprimary.narratives', ['class_id' => $ct->class_id, 'section_id' => $ct->section_id, 'term' => 1]) }}"
                                        class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-chat-left-text me-1"></i> Remarks
                                    </a>
                                </div>
                                @else
                                @foreach([1, 2] as $term)
                                @php $ms = $marksStatus[$term] ?? null; @endphp
                                @if($ms)
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span>Term {{ $term }}</span>
                                        <span class="text-muted">{{ $ms['entered'] }}/{{ $ms['total'] }} students</span>
                                    </div>
                                    <div class="progress" style="height:6px;">
                                        <div class="progress-bar bg-success" style="width:{{ $ms['total'] > 0 ? round($ms['entered']/$ms['total']*100) : 0 }}%"></div>
                                    </div>
                                </div>
                                @endif
                                @endforeach
                                <a href="{{ route('marks.index') }}" class="btn btn-sm btn-outline-primary w-100 mt-2">
                                    <i class="bi bi-pencil me-1"></i> Enter Marks
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Quick links card --}}
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-header py-2 small fw-semibold">
                                <i class="bi bi-grid me-1"></i> Quick Links
                            </div>
                            <div class="card-body py-3">
                                <div class="d-flex flex-column gap-2">
                                    @if($ppType)
                                    <a href="{{ route('preprimary.printClass', ['class_id' => $ct->class_id, 'section_id' => $ct->section_id]) }}"
                                        class="btn btn-sm btn-outline-success" target="_blank">
                                        <i class="bi bi-printer me-1"></i> Print All Report Cards
                                    </a>
                                    @else
                                    <a href="{{ route('marks.review', ['class_id' => $ct->class_id, 'section_id' => $ct->section_id]) }}"
                                        class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-grid-3x3-gap me-1"></i> Marks Review
                                    </a>
                                    <a href="{{ route('marks.printClass', ['class_id' => $ct->class_id, 'section_id' => $ct->section_id]) }}"
                                        class="btn btn-sm btn-outline-success" target="_blank">
                                        <i class="bi bi-printer me-1"></i> Print Report Cards
                                    </a>
                                    @endif
                                    <a href="{{ route('teacher.profile.show', auth()->user()->id) }}"
                                        class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-person-circle me-1"></i> My Profile
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Row 2: Student list + Notices --}}
                <div class="row g-3 mb-4">
                    {{-- Student list --}}
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header py-2 small fw-semibold d-flex justify-content-between">
                                <span><i class="bi bi-people me-1"></i> My Class — {{ $totalStudents }} students</span>
                            </div>
                            <div class="card-body p-0">
                                @if($students->isEmpty())
                                <p class="text-muted small p-3 mb-0">No students enrolled in your class this session.</p>
                                @else
                                <div style="max-height:320px;overflow-y:auto;">
                                <table class="table table-sm table-hover mb-0" style="font-size:0.82rem;">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th class="ps-3">#</th>
                                            <th>Name</th>
                                            <th class="text-center">Today</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($students as $p)
                                        @php
                                            $att = $attendanceToday->get($p->student_id);
                                            $attStatus = $att ? $att->status : null;
                                        @endphp
                                        <tr>
                                            <td class="ps-3 text-muted">{{ $p->roll_number ?? '—' }}</td>
                                            <td>{{ $p->student->first_name ?? '' }} {{ $p->student->last_name ?? '' }}</td>
                                            <td class="text-center">
                                                @if($attStatus === 'on')
                                                    <span class="badge bg-success" style="font-size:0.65rem;">P</span>
                                                @elseif($attStatus === 'off')
                                                    <span class="badge bg-danger" style="font-size:0.65rem;">A</span>
                                                @elseif($attStatus === 'late')
                                                    <span class="badge bg-warning text-dark" style="font-size:0.65rem;">L</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Notices --}}
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header py-2 small fw-semibold">
                                <i class="bi bi-megaphone me-1"></i> Notices
                            </div>
                            <div class="card-body p-0">
                                @if($notices->isEmpty())
                                <p class="text-muted small p-3 mb-0">No notices.</p>
                                @else
                                <div class="accordion accordion-flush" id="noticeAccordion">
                                    @foreach($notices->take(5) as $notice)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed py-2 small" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#notice-{{ $notice->id }}">
                                                {{ \Carbon\Carbon::parse($notice->created_at)->format('d M Y') }}
                                            </button>
                                        </h2>
                                        <div id="notice-{{ $notice->id }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}">
                                            <div class="accordion-body small">{!! \Purify::clean($notice->notice) !!}</div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Row 3: Events --}}
                <div class="card mb-4">
                    <div class="card-header py-2 small fw-semibold">
                        <i class="bi bi-calendar-event me-1"></i> Events
                    </div>
                    <div class="card-body text-dark">
                        @include('components.events.event-calendar', ['editable' => 'false', 'selectable' => 'false'])
                    </div>
                </div>

                @else
                {{-- No CT assignment --}}
                <div class="alert alert-warning mt-3" style="max-width:560px;">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    You are not assigned as a Class Teacher for this session.
                    Your subject assignments (if any) are accessible from the Marks menu.
                </div>
                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header py-2 small fw-semibold"><i class="bi bi-megaphone me-1"></i> Notices</div>
                            <div class="card-body p-0">
                                @if($notices->isEmpty())
                                <p class="text-muted small p-3 mb-0">No notices.</p>
                                @else
                                <div class="accordion accordion-flush" id="noticeAccordion2">
                                    @foreach($notices->take(5) as $notice)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed py-2 small" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#notice2-{{ $notice->id }}">
                                                {{ \Carbon\Carbon::parse($notice->created_at)->format('d M Y') }}
                                            </button>
                                        </h2>
                                        <div id="notice2-{{ $notice->id }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}">
                                            <div class="accordion-body small">{!! \Purify::clean($notice->notice) !!}</div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header py-2 small fw-semibold"><i class="bi bi-calendar-event me-1"></i> Events</div>
                            <div class="card-body text-dark">
                                @include('components.events.event-calendar', ['editable' => 'false', 'selectable' => 'false'])
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @else
                {{-- ── ADMIN DASHBOARD ── --}}
                <h5 class="mb-1">Dashboard</h5>
                <p class="text-muted small mb-3">{{ \Carbon\Carbon::today()->format('l, d M Y') }}</p>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card text-center py-3">
                            <div class="fs-2 fw-bold">{{ $studentCount }}</div>
                            <div class="small text-muted">Students enrolled</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center py-3">
                            <div class="fs-2 fw-bold">{{ $teacherCount }}</div>
                            <div class="small text-muted">Teachers</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-center py-3">
                            <div class="fs-2 fw-bold">{{ $classCount }}</div>
                            <div class="small text-muted">Classes this session</div>
                        </div>
                    </div>
                </div>

                @if($studentCount > 0)
                @php
                    $maleStudentPercentage   = $studentCount > 0 ? round($maleStudentsBySession / $studentCount * 100) : 0;
                    $femaleStudentPercentage = 100 - $maleStudentPercentage;
                @endphp
                <div class="d-flex align-items-center gap-3 mb-4">
                    <span class="small text-muted text-nowrap">Gender split:</span>
                    <div class="progress flex-grow-1" style="height:10px;">
                        <div class="progress-bar bg-primary" style="width:{{ $maleStudentPercentage }}%"
                            title="Male {{ $maleStudentPercentage }}%"></div>
                        <div class="progress-bar" style="width:{{ $femaleStudentPercentage }}%;background:#49a4fe;"
                            title="Female {{ $femaleStudentPercentage }}%"></div>
                    </div>
                    <span class="small text-muted">{{ $maleStudentPercentage }}% M / {{ $femaleStudentPercentage }}% F</span>
                </div>
                @endif

                <div class="row g-3">
                    <div class="col-lg-6">
                        <div class="card mb-3">
                            <div class="card-header py-2 small fw-semibold"><i class="bi bi-calendar-event me-1"></i> Events</div>
                            <div class="card-body text-dark">
                                @include('components.events.event-calendar', ['editable' => 'false', 'selectable' => 'false'])
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card mb-3">
                            <div class="card-header py-2 small fw-semibold d-flex justify-content-between">
                                <span><i class="bi bi-megaphone me-1"></i> Notices</span>
                                {{ $notices->links() }}
                            </div>
                            <div class="card-body p-0 text-dark">
                                <div class="accordion accordion-flush" id="noticeAccordion">
                                    @foreach($notices as $notice)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed py-2 small" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#flush-collapse{{$notice->id}}">
                                                {{ \Carbon\Carbon::parse($notice->created_at)->format('d M Y') }}
                                            </button>
                                        </h2>
                                        <div id="flush-collapse{{$notice->id}}" class="accordion-collapse collapse {{$loop->first ? 'show' : ''}}">
                                            <div class="accordion-body small overflow-auto">{!!Purify::clean($notice->notice)!!}</div>
                                        </div>
                                    </div>
                                    @endforeach
                                    @if($notices->isEmpty())
                                        <div class="p-3 text-muted small">No notices.</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @endif

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
