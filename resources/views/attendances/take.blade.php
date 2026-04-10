@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <div class="d-flex align-items-center mb-2">
                        <a href="{{ route('attendance.index') }}" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <h5 class="mb-0">
                            <i class="bi bi-calendar2-check me-1"></i> Take Attendance
                            <span class="text-muted fw-normal ms-2">
                                {{ $school_class->class_name ?? '' }}
                                @if($school_section) — {{ $school_section->section_name }} @endif
                            </span>
                        </h5>
                    </div>

                    <p class="text-muted small mb-3">{{ now()->format('d M Y') }}</p>

                    @include('session-messages')

                    @if($attendance_count >= 1 && auth()->user()->role !== 'admin')
                        <div class="alert alert-info">
                            <i class="bi bi-lock me-1"></i>
                            Attendance for today has already been submitted.
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-8 col-lg-6 bg-white border p-3 shadow-sm">
                            <form action="{{ route('attendances.store') }}" method="POST">
                                @csrf

                                @if(auth()->user()->role === 'admin')
                                <div class="mb-3">
                                    <label class="form-label small">Attendance Date</label>
                                    <input type="date" name="attendance_date" class="form-control form-control-sm"
                                        value="{{ now()->toDateString() }}" style="max-width:180px;">
                                </div>
                                @endif

                                <input type="hidden" name="session_id"  value="{{ $current_school_session_id }}">
                                <input type="hidden" name="class_id"    value="{{ request()->query('class_id') }}">
                                <input type="hidden" name="section_id"  value="{{ request()->query('section_id', 0) }}">
                                <input type="hidden" name="course_id"   value="0">

                                <table class="table table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width:50px;">Roll</th>
                                            <th>Student</th>
                                            <th class="text-center">Present</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($student_list->sortBy('roll_number') as $student)
                                        <input type="hidden" name="student_ids[]" value="{{ $student->student_id }}">
                                        <tr>
                                            <td class="text-muted">{{ $student->roll_number ?? '—' }}</td>
                                            <td>{{ $student->student->first_name }} {{ $student->student->last_name }}</td>
                                            <td class="text-center">
                                                <input type="hidden" name="status[{{ $student->student_id }}]" value="off">
                                                <input class="form-check-input" type="checkbox"
                                                    name="status[{{ $student->student_id }}]"
                                                    value="on" checked>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                @if(count($student_list) > 0 && ($attendance_count < 1 || auth()->user()->role === 'admin'))
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check2 me-1"></i> Submit Attendance
                                </button>
                                @endif
                            </form>
                        </div>
                    </div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
