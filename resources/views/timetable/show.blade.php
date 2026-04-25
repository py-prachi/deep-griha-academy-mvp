@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">

                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar4-week me-1"></i> Timetable
                            @if($classLabel) &mdash; {{ $classLabel }}{{ $sectionLabel ? ' ' . $sectionLabel : '' }} @endif
                        </h5>
                        <div class="d-flex gap-2">
                            <a href="{{ route('timetable.edit', ['class_id' => request('class_id'), 'section_id' => request('section_id')]) }}"
                                class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil me-1"></i> Edit
                            </a>
                            <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-printer me-1"></i> Print
                            </button>
                        </div>
                    </div>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb small mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('timetable.edit', ['class_id' => request('class_id'), 'section_id' => request('section_id')]) }}">Timetable</a></li>
                            <li class="breadcrumb-item active">Full Week View</li>
                        </ol>
                    </nav>

                    @include('session-messages')

                    @php
                        $dayShort = [1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday'];
                        $today = \Carbon\Carbon::today()->isoWeekday();
                    @endphp

                    {{-- Full week grid --}}
                    <div class="bg-white border shadow-sm p-3 mb-3" style="overflow-x:auto;">
                        <table class="table table-bordered table-sm align-middle mb-0" style="min-width:750px;">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:140px;">Period</th>
                                    @foreach($dayShort as $dayNum => $dayName)
                                        <th class="text-center" style="{{ $dayNum == $today ? 'background-color:rgba(13,110,253,0.10);' : '' }}">
                                            {{ $dayName }}
                                            @if($dayNum == $today)
                                                <span class="badge bg-primary ms-1" style="font-size:0.6rem;">Today</span>
                                            @endif
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($allPeriods as $period)
                                <tr>
                                    <td class="small text-nowrap table-light">
                                        <span class="fw-semibold">{{ $period->label }}</span>
                                        <span class="text-muted d-block" style="font-size:0.72rem;">{{ $period->start_time }}–{{ $period->end_time }}</span>
                                    </td>
                                    @foreach($dayShort as $dayNum => $dayName)
                                    @php
                                        // Use this day's own period object (custom or default) to check is_break
                                        $dayPeriod    = isset($periodObjMap[$dayNum][$period->sort_order]) ? $periodObjMap[$dayNum][$period->sort_order] : null;
                                        $isDayCustom  = in_array($dayNum, $customDays);
                                        // If this day is custom but has no period at this sort_order, cell is empty
                                        $dayIsAbsent  = $isDayCustom && !$dayPeriod;
                                        $dayIsBreak   = !$dayIsAbsent && $dayPeriod ? (bool) $dayPeriod->is_break : (!$dayIsAbsent && (bool) $period->is_break);
                                        $dayLabel     = $dayPeriod ? $dayPeriod->label : $period->label;
                                        $dayPeriodId  = isset($periodIdMap[$dayNum][$period->sort_order]) ? $periodIdMap[$dayNum][$period->sort_order] : null;
                                        $routine      = $dayPeriodId && isset($grid[$dayNum][$dayPeriodId]) ? $grid[$dayNum][$dayPeriodId] : null;
                                        $subjectName  = $routine ? optional(optional($routine->course)->subject)->name : null;
                                        $todayStyle   = $dayNum == $today ? 'background-color:rgba(13,110,253,0.08);' : '';
                                    @endphp
                                    <td class="text-center small {{ $dayIsBreak ? 'table-light' : '' }}" style="{{ $todayStyle }}">
                                        @if($dayIsAbsent)
                                            {{-- Custom day with no period at this slot --}}
                                            <span class="text-muted" style="font-size:0.8rem;">–</span>
                                        @elseif($dayIsBreak)
                                            <span class="text-muted fst-italic" style="font-size:0.8rem;">{{ $dayLabel }}</span>
                                        @elseif($subjectName)
                                            @if($dayPeriod && $dayPeriod->label !== $period->label)
                                                <span class="text-muted d-block" style="font-size:0.7rem;">{{ $dayPeriod->label }}</span>
                                            @endif
                                            <span class="fw-semibold">{{ $subjectName }}</span>
                                        @else
                                            @if($dayPeriod && $dayPeriod->label !== $period->label)
                                                <span class="text-muted fst-italic" style="font-size:0.8rem;">{{ $dayPeriod->label }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        @endif
                                    </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
