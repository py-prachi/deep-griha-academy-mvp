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
                        <div class="card h-100">
                            <div class="card-header py-2 small fw-semibold d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-calendar4-week me-1"></i> Today's Timetable</span>
                                <a href="{{ route('timetable.student') }}" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:0.75rem;">Full Week</a>
                            </div>
                            <div class="card-body p-0">
                                @if($todayWeekday > 6)
                                <div class="text-center text-muted small p-4">No school on Sunday.</div>
                                @elseif($todayPeriods->isEmpty() || $todayRoutines->isEmpty())
                                <div class="text-center text-muted small p-4">
                                    <i class="bi bi-clock" style="font-size:1.5rem;"></i>
                                    <div class="mt-1">Timetable not set up yet.</div>
                                </div>
                                @else
                                <table class="table table-sm mb-0" style="font-size:0.82rem;">
                                    @foreach($todayPeriods as $period)
                                        @if($period->is_break)
                                        <tr class="table-light">
                                            <td class="text-muted py-1 ps-3" colspan="2">
                                                <em>{{ $period->label }}</em>
                                                <span class="text-muted" style="font-size:0.75rem;"> {{ $period->start_time }}–{{ $period->end_time }}</span>
                                            </td>
                                        </tr>
                                        @else
                                        @php $slot = isset($todayRoutines[$period->id]) ? $todayRoutines[$period->id] : null; @endphp
                                        <tr>
                                            <td class="text-muted ps-3 py-1" style="width:85px;">{{ $period->start_time }}</td>
                                            <td class="py-1 fw-semibold">
                                                {{ $slot ? optional(optional($slot->course)->subject)->name : '—' }}
                                            </td>
                                        </tr>
                                        @endif
                                    @endforeach
                                </table>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-header py-2 small fw-semibold">
                                <i class="bi bi-file-earmark-text me-1"></i> Syllabus &amp; Assignments
                            </div>
                            <div class="card-body text-center py-4 text-muted">
                                <i class="bi bi-journal-bookmark" style="font-size:2rem;"></i>
                                <div class="small mt-2">Syllabus and assignments will be available here soon.</div>
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
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Row 2: Notices --}}
                <div class="row g-3 mb-4">
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
                <div class="row dashboard">
                    <div class="col">
                        <div class="card rounded-pill">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto"><div class="fw-bold"><i class="bi bi-person-lines-fill me-3"></i> Total Students</div></div>
                                    <span class="badge bg-dark rounded-pill">{{$studentCount}}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card rounded-pill">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto"><div class="fw-bold"><i class="bi bi-person-lines-fill me-3"></i> Total Teachers</div></div>
                                    <span class="badge bg-dark rounded-pill">{{$teacherCount}}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card rounded-pill">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto"><div class="fw-bold"><i class="bi bi-diagram-3 me-3"></i> Total Classes</div></div>
                                    <span class="badge bg-dark rounded-pill">{{ $classCount }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($studentCount > 0)
                <div class="mt-3 d-flex align-items-center">
                    <div class="col-3">
                        <span class="ps-2 me-2">Students %</span>
                        <span class="badge rounded-pill border" style="background-color: #0678c8;">Male</span>
                        <span class="badge rounded-pill border" style="background-color: #49a4fe;">Female</span>
                    </div>
                    @php
                    $maleStudentPercentage = round(($maleStudentsBySession/$studentCount), 2) * 100;
                    $maleStudentPercentageStyle = "style='background-color: #0678c8; width: $maleStudentPercentage%'";
                    $femaleStudentPercentage = round((($studentCount - $maleStudentsBySession)/$studentCount), 2) * 100;
                    $femaleStudentPercentageStyle = "style='background-color: #49a4fe; width: $femaleStudentPercentage%'";
                    @endphp
                    <div class="col-9 progress">
                        <div class="progress-bar progress-bar-striped" role="progressbar" {!!$maleStudentPercentageStyle!!} aria-valuenow="{{$maleStudentPercentage}}" aria-valuemin="0" aria-valuemax="100">{{$maleStudentPercentage}}%</div>
                        <div class="progress-bar progress-bar-striped" role="progressbar" {!!$femaleStudentPercentageStyle!!} aria-valuenow="{{$femaleStudentPercentage}}" aria-valuemin="0" aria-valuemax="100">{{$femaleStudentPercentage}}%</div>
                    </div>
                </div>
                @endif

                <div class="row align-items-md-stretch mt-4">
                    <div class="col">
                        <div class="p-3 text-white bg-dark rounded-3">
                            <h3>Welcome to Deep Griha Academy!</h3>
                            <p><i class="bi bi-emoji-heart-eyes"></i> Thanks for your love and support.</p>
                        </div>
                    </div>
                    <div class="col">
                        <div class="p-3 bg-white border rounded-3" style="height: 100%;">
                            <h3>School Management System</h3>
                            <p class="text-end">for <i class="bi bi-lightning"></i> <a href="https://deepgriha.org/programmes/cbse-english-academy-pune/" target="_blank" style="text-decoration: none;">Deep Griha Academy</a> <i class="bi bi-lightning"></i>.</p>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-lg-6">
                        <div class="card mb-3">
                            <div class="card-header bg-transparent"><i class="bi bi-calendar-event me-2"></i> Events</div>
                            <div class="card-body text-dark">
                                @include('components.events.event-calendar', ['editable' => 'false', 'selectable' => 'false'])
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card mb-3">
                            <div class="card-header bg-transparent d-flex justify-content-between">
                                <span><i class="bi bi-megaphone me-2"></i> Notices</span> {{ $notices->links() }}
                            </div>
                            <div class="card-body p-0 text-dark">
                                <div class="accordion accordion-flush" id="noticeAccordion">
                                    @foreach ($notices as $notice)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="flush-heading{{$notice->id}}">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapse{{$notice->id}}" aria-expanded="{{$loop->first ? 'true' : 'false'}}" aria-controls="flush-collapse{{$notice->id}}">
                                                Published at: {{$notice->created_at}}
                                            </button>
                                        </h2>
                                        <div id="flush-collapse{{$notice->id}}" class="accordion-collapse collapse {{$loop->first ? 'show' : ''}}" aria-labelledby="flush-heading{{$notice->id}}" data-bs-parent="#noticeAccordion">
                                            <div class="accordion-body overflow-auto">{!!Purify::clean($notice->notice)!!}</div>
                                        </div>
                                    </div>
                                    @endforeach
                                    @if(count($notices) < 1)
                                        <div class="p-3">No notices</div>
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
