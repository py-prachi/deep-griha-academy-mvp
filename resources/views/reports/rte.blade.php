@extends('layouts.app')
@section('title', 'Special Category Students Report')
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
                        <h4 class="mb-0">Special Category Students</h4>
                    </div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item active">Special Category Students</li>
                        </ol>
                    </nav>

                    <div class="container-fluid px-0">
                        {{-- Session filter --}}
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

                        {{-- Summary cards --}}
                        <div class="row mb-4 g-3">
                            <div class="col-md-4">
                                <div class="card text-white bg-success">
                                    <div class="card-body text-center py-3">
                                        <h6 class="card-title mb-1">RTE Students</h6>
                                        <h3 class="mb-0">{{ $rteStudents->count() }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-white bg-warning text-dark">
                                    <div class="card-body text-center py-3">
                                        <h6 class="card-title mb-1">Discount Students</h6>
                                        <h3 class="mb-0">{{ $discountStudents->count() }}</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-white bg-info">
                                    <div class="card-body text-center py-3">
                                        <h6 class="card-title mb-1">COC Students</h6>
                                        <h3 class="mb-0">{{ $cocStudents->count() }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Tabs --}}
                        <ul class="nav nav-tabs mb-3" id="categoryTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="rte-tab" data-bs-toggle="tab" data-bs-target="#rte-pane" type="button" role="tab">
                                    <span class="badge bg-success me-1">{{ $rteStudents->count() }}</span> RTE
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="discount-tab" data-bs-toggle="tab" data-bs-target="#discount-pane" type="button" role="tab">
                                    <span class="badge bg-warning text-dark me-1">{{ $discountStudents->count() }}</span> Discount
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="coc-tab" data-bs-toggle="tab" data-bs-target="#coc-pane" type="button" role="tab">
                                    <span class="badge bg-info me-1">{{ $cocStudents->count() }}</span> COC
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="categoryTabsContent">

                            {{-- RTE Tab --}}
                            <div class="tab-pane fade show active" id="rte-pane" role="tabpanel">
                                <div class="d-flex justify-content-end mb-2">
                                    <a href="?pdf=1&session_id={{ $selectedSessionId }}" class="btn btn-sm btn-outline-dark">
                                        <i class="fas fa-download me-1"></i> Download RTE PDF
                                    </a>
                                </div>
                                @if($rteStudents->isEmpty())
                                    <p class="text-center text-muted py-4">No RTE students found for this session.</p>
                                @else
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-success">
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
                                            @foreach($rteStudents as $i => $student)
                                            <tr>
                                                <td>{{ $i + 1 }}</td>
                                                <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                                                <td>{{ $student->class_name }} {{ $student->section_name }}</td>
                                                <td>{{ $student->dga_admission_no ?? $student->general_id ?? '—' }}</td>
                                                <td>{{ $student->admission ? $student->admission->rte_doc_no : '—' }}</td>
                                                <td>{{ $student->birthday ? \Carbon\Carbon::parse($student->birthday)->format('d M Y') : '—' }}</td>
                                                <td>{{ $student->admission ? $student->admission->father_name : '—' }}</td>
                                                <td class="text-center">
                                                    @if($student->admission_id)
                                                    <a href="{{ route('admissions.show', $student->admission_id) }}" class="btn btn-sm btn-outline-primary">View</a>
                                                    @else
                                                    <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @endif
                            </div>

                            {{-- Discount Tab --}}
                            <div class="tab-pane fade" id="discount-pane" role="tabpanel">
                                @if($discountStudents->isEmpty())
                                    <p class="text-center text-muted py-4">No discount students found for this session.</p>
                                @else
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-warning">
                                            <tr>
                                                <th>#</th>
                                                <th>Student Name</th>
                                                <th>Class / Div</th>
                                                <th>Admission No</th>
                                                <th>Gender</th>
                                                <th class="text-center">Discount %</th>
                                                <th>Father's Name</th>
                                                <th class="text-center">Profile</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($discountStudents as $i => $student)
                                            <tr>
                                                <td>{{ $i + 1 }}</td>
                                                <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                                                <td>{{ $student->class_name }} {{ $student->section_name }}</td>
                                                <td>{{ $student->dga_admission_no ?? $student->general_id ?? '—' }}</td>
                                                <td>{{ $student->gender }}</td>
                                                <td class="text-center">
                                                    @if($student->admission && $student->admission->discount_percentage !== null)
                                                        <span class="badge bg-warning text-dark fs-6">{{ $student->admission->discount_percentage }}%</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>{{ $student->admission ? $student->admission->father_name : '—' }}</td>
                                                <td class="text-center">
                                                    @if($student->admission_id)
                                                    <a href="{{ route('admissions.show', $student->admission_id) }}" class="btn btn-sm btn-outline-primary">View</a>
                                                    @else
                                                    <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @endif
                            </div>

                            {{-- COC Tab --}}
                            <div class="tab-pane fade" id="coc-pane" role="tabpanel">
                                @if($cocStudents->isEmpty())
                                    <p class="text-center text-muted py-4">No COC students found for this session.</p>
                                @else
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-info">
                                            <tr>
                                                <th>#</th>
                                                <th>Student Name</th>
                                                <th>Class / Div</th>
                                                <th>Admission No</th>
                                                <th>Date of Birth</th>
                                                <th>Father's Name</th>
                                                <th class="text-center">Profile</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($cocStudents as $i => $student)
                                            <tr>
                                                <td>{{ $i + 1 }}</td>
                                                <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                                                <td>{{ $student->class_name }} {{ $student->section_name }}</td>
                                                <td>{{ $student->dga_admission_no ?? $student->general_id ?? '—' }}</td>
                                                <td>{{ $student->birthday ? \Carbon\Carbon::parse($student->birthday)->format('d M Y') : '—' }}</td>
                                                <td>{{ $student->admission ? $student->admission->father_name : '—' }}</td>
                                                <td class="text-center">
                                                    @if($student->admission_id)
                                                    <a href="{{ route('admissions.show', $student->admission_id) }}" class="btn btn-sm btn-outline-primary">View</a>
                                                    @else
                                                    <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @endif
                            </div>

                        </div>{{-- /tab-content --}}
                    </div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
