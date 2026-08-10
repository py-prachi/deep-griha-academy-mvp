@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">

                    {{-- Print header (hidden on screen) --}}
                    <div class="print-only text-center mb-3">
                        <h5 class="fw-bold mb-0">Deep Griha Academy</h5>
                        <p class="mb-0">Timetable
                            @if($classLabel) &mdash; {{ $classLabel }}{{ $sectionLabel ? ' ' . $sectionLabel : '' }} @endif
                        </p>
                        <small class="text-muted">Printed on {{ date('d M Y') }}</small>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-1 no-print">
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
                    <nav aria-label="breadcrumb" class="mb-3 no-print">
                        <ol class="breadcrumb small mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('timetable.edit', ['class_id' => request('class_id'), 'section_id' => request('section_id')]) }}">Timetable</a></li>
                            <li class="breadcrumb-item active">Full Week View</li>
                        </ol>
                    </nav>

                    @include('session-messages')

                    @php
                        $dayShort = [1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday'];
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
            <div class="no-print">@include('layouts.footer')</div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .print-only { display: none; }

    @media print {
        /* Hide nav bar, sidebar, watermark, buttons, breadcrumb, footer */
        .navbar,
        .dga-sidebar,
        #watermark,
        .no-print { display: none !important; }

        .print-only { display: block !important; }

        /* Collapse the Bootstrap grid so content fills the page */
        .container { width: 100% !important; max-width: 100% !important; padding: 0 !important; margin: 0 !important; }
        .row { margin: 0 !important; }
        .col-xs-11, .col-sm-11, .col-md-11,
        .col-lg-10, .col-xl-10, .col-xxl-10 {
            width: 100% !important;
            max-width: 100% !important;
            flex: 0 0 100% !important;
            padding: 0 !important;
        }
        .col.ps-4 { padding-left: 0.5rem !important; }

        /* Table fills the page, no horizontal scroll */
        .bg-white.border.shadow-sm {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            overflow: visible !important;
        }
        table {
            font-size: 0.76rem !important;
            min-width: unset !important;
            width: 100% !important;
            table-layout: fixed !important;
        }
        table th, table td {
            white-space: normal !important;
            word-break: break-word !important;
            overflow-wrap: break-word !important;
            padding: 0.25rem 0.3rem !important;
        }
        /* Give period column a fixed narrow width; days share the rest equally */
        table th:first-child, table td:first-child { width: 13% !important; }

        /* Remove today highlight (inline style) and Today badge */
        table thead th, table tbody td { background-color: white !important; }
        table .table-light, table thead.table-light th {
            background-color: #e9ecef !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        thead .badge { display: none !important; }

        footer, .footer { display: none !important; }
        body { font-size: 11px; margin: 0; }
    }
</style>
@endpush
@endsection
