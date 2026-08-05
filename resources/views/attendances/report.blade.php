@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4 pe-4">

                    <div class="d-flex align-items-center mb-2">
                        <a href="{{ route('student.attendance.show', $student->id) }}" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <h5 class="mb-0">
                            <i class="bi bi-pie-chart me-1"></i> Attendance Report
                            <span class="text-muted fw-normal ms-2">
                                {{ $student->first_name }} {{ $student->last_name }}
                                @if($school_class)
                                    &nbsp;·&nbsp; {{ $school_class->class_name }}
                                    @if($school_section) — {{ $school_section->section_name }} @endif
                                @endif
                            </span>
                        </h5>
                    </div>
                    <p class="text-muted small mb-3">
                        Based on actual school working days (Mon–Fri, excluding recorded holidays)
                        from {{ $from->format('d M Y') }} to {{ $to->format('d M Y') }}.
                    </p>

                    @include('session-messages')

                    <div class="row g-3">
                        {{-- Overall donut --}}
                        <div class="col-md-4">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-light fw-semibold">Overall — Till Now</div>
                                <div class="card-body d-flex flex-column align-items-center">
                                    @if($overallPercentage === null)
                                        <p class="text-muted small text-center mb-0">
                                            No working days have elapsed yet for this class this session.
                                        </p>
                                    @else
                                    @php
                                        $absentDays = $totalWorkingDays - $totalPresent;
                                        $absentPct  = 100 - $overallPercentage;
                                    @endphp
                                    <div class="attendance-donut" role="img"
                                         aria-label="{{ $overallPercentage }}% present, {{ $absentPct }}% absent"
                                         style="background: conic-gradient(#198754 0% {{ $overallPercentage }}%, #dc3545 {{ $overallPercentage }}% 100%);">
                                        <div class="attendance-donut-hole">
                                            <div class="attendance-donut-pct">{{ $overallPercentage }}%</div>
                                            <div class="text-muted" style="font-size:0.7rem;">Present</div>
                                        </div>
                                    </div>
                                    <div class="w-100 mt-3" style="font-size:0.85rem;">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <span><i class="bi bi-check-circle-fill" style="color:#198754;"></i> Present</span>
                                            <span>{{ $totalPresent }} / {{ $totalWorkingDays }} days ({{ $overallPercentage }}%)</span>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span><i class="bi bi-x-circle-fill" style="color:#dc3545;"></i> Absent</span>
                                            <span>{{ $absentDays }} / {{ $totalWorkingDays }} days ({{ $absentPct }}%)</span>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Month-wise table --}}
                        <div class="col-md-8">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-light fw-semibold">Month-wise Breakdown</div>
                                <div class="card-body p-0">
                                    @if(empty($months))
                                        <p class="text-muted small p-3 mb-0">No working days have elapsed yet for this class this session.</p>
                                    @else
                                    <div class="table-responsive">
                                        <table class="table table-sm mb-0 align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Month</th>
                                                    <th class="text-center">Working Days</th>
                                                    <th class="text-center">Present</th>
                                                    <th class="text-center">Absent</th>
                                                    <th class="text-center">%</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($months as $m)
                                                @php
                                                    $pct = $m['percentage'];
                                                    $badgeClass = $pct === null ? 'bg-secondary'
                                                        : ($pct < 75 ? 'bg-danger' : ($pct < 90 ? 'bg-warning text-dark' : 'bg-success'));
                                                @endphp
                                                <tr>
                                                    <td>{{ $m['label'] }}</td>
                                                    <td class="text-center">{{ $m['total'] }}</td>
                                                    <td class="text-center">{{ $m['present'] }}</td>
                                                    <td class="text-center">{{ $m['total'] - $m['present'] }}</td>
                                                    <td class="text-center">
                                                        <span class="badge {{ $badgeClass }}">{{ $pct !== null ? $pct . '%' : '—' }}</span>
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
                    </div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>

<style>
    .attendance-donut {
        width: 160px;
        height: 160px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .attendance-donut-hole {
        width: 68%;
        height: 68%;
        background: #fff;
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .attendance-donut-pct {
        font-size: 1.4rem;
        font-weight: 700;
        color: #212529;
    }
</style>
@endsection
