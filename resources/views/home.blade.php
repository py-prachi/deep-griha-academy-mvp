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

                @else
                {{-- ── ADMIN / TEACHER DASHBOARD ── --}}
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
