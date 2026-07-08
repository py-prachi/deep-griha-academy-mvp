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

                    @php
                        $activeDay   = \Carbon\Carbon::today()->isoWeekday();
                        $dayShort    = [1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat'];

                        // Build list of next-school-day slots for this teacher
                        $nextDaySlots = collect();
                        foreach (($periodsByDay[$nextSchoolDayWeekday] ?? collect()) as $pd) {
                            if ($pd->is_break) continue;
                            $rt = $grid[$nextSchoolDayWeekday][$pd->id] ?? null;
                            if (!$rt) continue;
                            $planKey = $rt->class_id . '_' . $rt->section_id . '_' . optional($rt->course)->subject_id;
                            // 'dated' = plan pinned to tomorrow; 'exists' = plan exists but no date set; 'missing' = no plan
                            $planStatus = isset($nextDayPlans[$planKey]) ? 'dated'
                                : (isset($anyPlansExist[$planKey]) ? 'exists' : 'missing');
                            $nextDaySlots->push((object)[
                                'period'      => $pd,
                                'routine'     => $rt,
                                'plan_key'    => $planKey,
                                'plan_status' => $planStatus,
                                'class_id'    => $rt->class_id,
                                'section_id'  => $rt->section_id,
                                'subject_id'  => optional($rt->course)->subject_id,
                            ]);
                        }
                        $plannedCount = $nextDaySlots->where('plan_status', 'dated')->count();
                        $totalCount   = $nextDaySlots->count();
                    @endphp

                    {{-- Next Teaching Day lesson plan status panel --}}
                    @if(!isset($viewingTeacher) || !$viewingTeacher)
                    @php
                        $missingCount = $nextDaySlots->where('plan_status', 'missing')->count();
                        $panelBorder  = $missingCount > 0 ? 'danger' : ($plannedCount < $totalCount ? 'secondary' : 'success');
                    @endphp
                    <div class="card mb-3 border-{{ $panelBorder }} shadow-sm">
                        <div class="card-header py-2 d-flex align-items-center justify-content-between bg-{{ $panelBorder }} bg-opacity-10">
                            <span class="fw-semibold small">
                                <i class="bi bi-calendar-check me-1"></i>
                                Next Teaching Day — {{ $nextSchoolDay->format('l, d M Y') }}
                            </span>
                            @if($totalCount > 0)
                            <span class="badge {{ $missingCount > 0 ? 'bg-danger' : ($plannedCount === $totalCount ? 'bg-success' : 'bg-secondary') }}">
                                {{ $plannedCount }} / {{ $totalCount }} dated
                            </span>
                            @endif
                        </div>
                        @if($nextDaySlots->isEmpty())
                        <div class="card-body py-2 text-muted small">No classes scheduled for this day.</div>
                        @else
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0 align-middle" style="font-size:0.83rem;">
                                <tbody>
                                    @foreach($nextDaySlots as $slot)
                                    <tr>
                                        <td class="ps-3 text-nowrap text-muted" style="width:110px;">
                                            {{ $slot->period->label }}
                                            <span class="d-block" style="font-size:0.7rem;">{{ $slot->period->start_time }}–{{ $slot->period->end_time }}</span>
                                        </td>
                                        <td class="fw-semibold">{{ optional(optional($slot->routine->course)->subject)->name }}</td>
                                        <td class="text-muted">
                                            {{ optional($slot->routine->schoolClass)->class_name }}
                                            {{ optional($slot->routine->section)->section_name }}
                                        </td>
                                        <td class="text-end pe-3" style="width:150px;">
                                            @if($slot->plan_status === 'dated')
                                                @php $linkedPlan = $nextDayPlans[$slot->plan_key] ?? null; @endphp
                                                <a href="{{ $linkedPlan ? route('lesson-planning.lessons.edit', $linkedPlan->id) : '#' }}"
                                                   class="badge bg-success text-decoration-none" title="View plan">
                                                    <i class="bi bi-check-lg me-1"></i>Plan set
                                                </a>
                                            @elseif($slot->plan_status === 'exists')
                                                <a href="{{ route('lesson-planning.lessons.link-slot', ['class_id' => $slot->class_id, 'section_id' => $slot->section_id, 'subject_id' => $slot->subject_id, 'scheduled_date' => $nextSchoolDay->toDateString()]) }}"
                                                   class="badge bg-secondary text-decoration-none" title="Plans exist — select one to link to this date">
                                                    <i class="bi bi-link-45deg me-1"></i>Select plan
                                                </a>
                                            @else
                                                <a href="{{ route('lesson-planning.lessons.create', ['class_id' => $slot->class_id, 'section_id' => $slot->section_id, 'subject_id' => $slot->subject_id, 'scheduled_date' => $nextSchoolDay->toDateString()]) }}"
                                                   class="badge bg-danger text-decoration-none">
                                                    <i class="bi bi-exclamation-triangle me-1"></i>Missing
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @endif
                    </div>
                    @endif

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
                                        @if($dayNum == $nextSchoolDayWeekday)
                                        <th class="text-center" style="width:110px;">Lesson Plan</th>
                                        @endif
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
                                            <td colspan="{{ $dayNum == $nextSchoolDayWeekday ? 3 : 2 }}" class="text-muted small fst-italic">{{ $period->label }}</td>
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
                                            @if($dayNum == $nextSchoolDayWeekday)
                                            <td class="text-center">
                                                @if(!$routine)
                                                    <span class="text-muted">—</span>
                                                @else
                                                @php
                                                    $pk  = $routine->class_id . '_' . $routine->section_id . '_' . optional($routine->course)->subject_id;
                                                    $cid = $routine->class_id;
                                                    $sid = $routine->section_id;
                                                    $subid = optional($routine->course)->subject_id;
                                                @endphp
                                                @if(isset($nextDayPlans[$pk]))
                                                    <a href="{{ route('lesson-planning.lessons.edit', $nextDayPlans[$pk]->id) }}"
                                                       class="badge bg-success text-decoration-none" title="View plan">
                                                        <i class="bi bi-check-lg"></i>
                                                    </a>
                                                @elseif(isset($anyPlansExist[$pk]))
                                                    <a href="{{ route('lesson-planning.lessons.link-slot', ['class_id' => $cid, 'section_id' => $sid, 'subject_id' => $subid, 'scheduled_date' => $nextSchoolDay->toDateString()]) }}"
                                                       class="badge bg-secondary text-decoration-none" title="Plans exist — select one to link">
                                                        <i class="bi bi-link-45deg"></i>
                                                    </a>
                                                @else
                                                    <a href="{{ route('lesson-planning.lessons.create', ['class_id' => $cid, 'section_id' => $sid, 'subject_id' => $subid, 'scheduled_date' => $nextSchoolDay->toDateString()]) }}"
                                                       class="badge bg-danger text-decoration-none" title="Add lesson plan for {{ $nextSchoolDay->format('d M') }}">
                                                        <i class="bi bi-plus-lg"></i> Add
                                                    </a>
                                                @endif
                                                @endif
                                            </td>
                                            @endif
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
