@extends('layouts.app')
@section('title', 'RTE Students Report')
@section('content')
<div class="container-fluid">
    <div class="card mb-3">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-star me-2 text-warning"></i>RTE Students Report</h5>
            <a href="?pdf=1" class="btn btn-sm btn-light"><i class="fas fa-download me-1"></i> Download PDF</a>
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
                        <tr><th>#</th><th>Student Name</th><th>Class / Div</th><th>Admission No</th><th>RTE Doc No</th><th>Date of Birth</th><th>Father's Name</th><th class="text-center">Profile</th></tr>
                    </thead>
                    <tbody>
                        @foreach($students as $i => $student)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                            <td>{{ $student->class_name }} {{ $student->section_name }}</td>
                            <td>{{ $student->dga_admission_no ?? $student->general_id ?? '—' }}</td>
                            <td>{{ $student->admission->rte_doc_no ?? '—' }}</td>
                            <td>{{ $student->birthday ? \Carbon\Carbon::parse($student->birthday)->format('d M Y') : '—' }}</td>
                            <td>{{ $student->admission->father_name ?? '—' }}</td>
                            <td class="text-center"><a href="{{ route('students.profile', $student->id) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
