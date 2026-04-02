@extends('layouts.app')
@section('title', 'RTE Students Report')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <div class="d-flex align-items-center mb-3">
                        <a href="{{ route('home') }}" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <h4 class="mb-0">RTE Students Report</h4>
                    </div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item active">RTE Students</li>
                        </ol>
                    </nav>

                    <div class="container-fluid px-0">
                        <form method="GET" action="{{ route('reports.rte') }}" class="mb-3">
                            <div class="row g-2 align-items-center">
                                <div class="col-auto">
                                    <label class="form-label mb-0">Academic Year:</label>
                                </div>
                                <div class="col-auto">
                                    <select name="session_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                        @foreach($sessions as $s)
                                            <option value="{{ $s->id }}" {{ $s->id == $selectedSessionId ? 'selected' : '' }}>{{ $s->session_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @if($selectedSession)
                                <div class="col-auto">
                                    <span class="text-muted small">Showing: <strong>{{ $selectedSession->session_name }}</strong></span>
                                </div>
                                @endif
                            </div>
                        </form>

                        <div class="card mb-3">
                            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-star me-2 text-warning"></i>RTE Students Report</h5>
                                <a href="?pdf=1&session_id={{ $selectedSessionId }}" class="btn btn-sm btn-light"><i class="fas fa-download me-1"></i> Download PDF</a>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="card text-white bg-info">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Total RTE Students</h6>
                                        <h3>{{ $students->count() }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                @if($students->isEmpty())
                                    <p class="text-center text-muted py-4">No RTE students found for the current session.</p>
                                @else
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>#</th>
                                                <th>Student Name</th>
                                                <th>Class / Div</th>
                                                <th>Admission No</th>
                                                <th>RTE Doc No</th>
                                                <th>Date of Birth</th>
                                                <th>Father's Name</th>
                                                <th class="text-center">Profile</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($students as $i => $student)
                                            <tr>
                                                <td>{{ $i + 1 }}</td>
                                                <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                                                <td>{{ $student->class_name }} {{ $student->section_name }}</td>
                                                <td>{{ $student->dga_admission_no ?? $student->general_id ?? '—' }}</td>
                                                <td>{{ $student->admission ? $student->admission->rte_doc_no : '—' }}</td>
                                                <td>{{ $student->birthday ? \Carbon\Carbon::parse($student->birthday)->format('d M Y') : '—' }}</td>
                                                <td>{{ $student->admission ? $student->admission->father_name : '—' }}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('admissions.show', $student->admission_id) }}" class="btn btn-sm btn-outline-primary">View</a>
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
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
