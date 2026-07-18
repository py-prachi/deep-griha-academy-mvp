@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4 pe-4">

                    <div class="d-flex align-items-center mb-2">
                        <a href="{{ $is_ct ? route('attendance.create.show', ['class_id' => $class_id, 'section_id' => $section_id]) : route('attendance.index') }}"
                           class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <h5 class="mb-0">
                            <i class="bi bi-calendar-range me-1"></i> Class Attendance History
                            <span class="text-muted fw-normal ms-2">
                                {{ $school_class->class_name ?? '' }}
                                @if($school_section) — {{ $school_section->section_name }} @endif
                            </span>
                        </h5>
                    </div>

                    @include('session-messages')

                    {{-- Range picker --}}
                    <div class="card mb-3 border-0 shadow-sm">
                        <div class="card-body py-3">
                            <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                                @php
                                    $baseParams = ['class_id' => $class_id, 'section_id' => $section_id];
                                @endphp
                                <a href="{{ route('attendance.history', $baseParams + ['range' => '7days']) }}"
                                   class="btn btn-sm {{ $range === '7days' ? 'btn-primary' : 'btn-outline-secondary' }}">Last 7 Days</a>
                                <a href="{{ route('attendance.history', $baseParams + ['range' => 'week']) }}"
                                   class="btn btn-sm {{ $range === 'week' ? 'btn-primary' : 'btn-outline-secondary' }}">This Week</a>
                                <a href="{{ route('attendance.history', $baseParams + ['range' => 'month']) }}"
                                   class="btn btn-sm {{ $range === 'month' ? 'btn-primary' : 'btn-outline-secondary' }}">This Month</a>
                            </div>
                            <form method="GET" action="{{ route('attendance.history') }}" class="d-flex flex-wrap gap-2 align-items-end">
                                <input type="hidden" name="class_id" value="{{ $class_id }}">
                                <input type="hidden" name="section_id" value="{{ $section_id }}">
                                <div>
                                    <label class="form-label small mb-1">From</label>
                                    <input type="date" name="from" class="form-control form-control-sm" value="{{ $from->toDateString() }}">
                                </div>
                                <div>
                                    <label class="form-label small mb-1">To</label>
                                    <input type="date" name="to" class="form-control form-control-sm" value="{{ $to->toDateString() }}">
                                </div>
                                <button type="submit" class="btn btn-sm btn-outline-primary {{ $range === 'custom' ? 'active' : '' }}">
                                    <i class="bi bi-search me-1"></i>Custom Range
                                </button>
                            </form>
                        </div>
                    </div>

                    @if($student_list->isEmpty())
                        <div class="alert alert-light text-muted">No students found for this class and section.</div>
                    @else
                    <div class="table-responsive bg-white border shadow-sm">
                        <table class="table table-sm table-bordered mb-0 align-middle" style="font-size:0.8rem;">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:45px;" class="text-center">Roll</th>
                                    <th style="min-width:160px;">Student</th>
                                    @foreach($dates as $d)
                                    <th class="text-center text-nowrap {{ $d->isWeekend() ? 'text-muted' : '' }}" style="width:70px;">
                                        {{ $d->format('d M') }}
                                        <span class="d-block" style="font-size:0.65rem;">{{ $d->format('D') }}</span>
                                    </th>
                                    @endforeach
                                    <th class="text-center text-nowrap" style="width:80px;">Present</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($student_list as $promotion)
                                @php
                                    $studentRow  = $grid[$promotion->student_id] ?? [];
                                    $presentDays = collect($studentRow)->filter(fn($s) => $s === 'on')->count();
                                    $markedDays  = count($studentRow);
                                @endphp
                                <tr>
                                    <td class="text-center text-muted">{{ $promotion->roll_number ?? '—' }}</td>
                                    <td>{{ optional($promotion->student)->first_name }} {{ optional($promotion->student)->last_name }}</td>
                                    @foreach($dates as $d)
                                    @php $status = $studentRow[$d->format('Y-m-d')] ?? null; @endphp
                                    <td class="text-center">
                                        @if($status === 'on')
                                            <span class="badge bg-success">P</span>
                                        @elseif($status === 'off')
                                            <span class="badge bg-danger">A</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    @endforeach
                                    <td class="text-center small text-muted">{{ $presentDays }} / {{ $markedDays }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
