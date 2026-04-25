@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">

                    <h5 class="mb-1">
                        <i class="bi bi-calendar4-week me-1"></i>
                        @if(isset($viewingTeacher) && $viewingTeacher)
                            {{ $viewingTeacher->first_name }} {{ $viewingTeacher->last_name }} — Timetable
                        @else
                            My Timetable
                        @endif
                    </h5>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb small mb-0">
                            @if(isset($viewingTeacher) && $viewingTeacher)
                                <li class="breadcrumb-item"><a href="{{ route('teacher.list.show') }}">Teachers</a></li>
                                <li class="breadcrumb-item active">Timetable</li>
                            @else
                                <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
                                <li class="breadcrumb-item active">My Timetable</li>
                            @endif
                        </ol>
                    </nav>

                    @include('session-messages')

                    @if($routines->isEmpty())
                    <div class="alert alert-light border" style="max-width:480px;">
                        <i class="bi bi-info-circle me-1 text-muted"></i>
                        No timetable slots have been assigned to you yet. Contact the admin to set up the timetable.
                    </div>
                    @else

                    @php $activeDay = \Carbon\Carbon::today()->isoWeekday(); @endphp
                    @php $dayShort = [1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat']; @endphp

                    <ul class="nav nav-tabs mb-0" role="tablist">
                        @foreach($days as $dayNum => $dayName)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $dayNum == $activeDay ? 'active' : '' }}"
                                data-bs-toggle="tab" data-bs-target="#tday-{{ $dayNum }}"
                                type="button" role="tab">
                                {{ $dayShort[$dayNum] ?? $dayName }}
                                @if($dayNum == $activeDay)
                                    <span class="badge bg-success ms-1" style="font-size:0.6rem;">Today</span>
                                @endif
                            </button>
                        </li>
                        @endforeach
                    </ul>

                    <div class="tab-content border border-top-0 bg-white shadow-sm mb-3">
                        @foreach($days as $dayNum => $dayName)
                        @php $dayPeriods = $periodsByDay[$dayNum] ?? collect(); @endphp
                        <div class="tab-pane fade {{ $dayNum == $activeDay ? 'show active' : '' }}" id="tday-{{ $dayNum }}" role="tabpanel">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:160px;">Period</th>
                                        <th>Subject</th>
                                        <th>Class</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dayPeriods as $period)
                                    @php $routine = isset($grid[$dayNum][$period->id]) ? $grid[$dayNum][$period->id] : null; @endphp
                                    <tr class="{{ $period->is_break ? 'table-light' : '' }}">
                                        <td class="small text-nowrap">
                                            <span class="fw-semibold">{{ $period->label }}</span>
                                            <span class="text-muted d-block" style="font-size:0.72rem;">{{ $period->start_time }}–{{ $period->end_time }}</span>
                                        </td>
                                        @if($period->is_break)
                                            <td colspan="2" class="text-muted small fst-italic">{{ $period->label }}</td>
                                        @else
                                            <td class="fw-semibold small">
                                                {{ $routine ? optional(optional($routine->course)->subject)->name : '—' }}
                                            </td>
                                            <td class="text-muted small">
                                                @if($routine)
                                                    {{ optional($routine->schoolClass)->class_name }}
                                                    {{ optional($routine->section)->section_name }}
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endforeach
                    </div>

                    @endif

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
