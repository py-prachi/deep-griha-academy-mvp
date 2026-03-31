@extends('layouts.app')
@section('title', 'Import Students')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">

                    <h4 class="mb-1"><i class="bi bi-file-earmark-arrow-up"></i> Bulk Import Students</h4>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admissions.index') }}">Admissions</a></li>
                            <li class="breadcrumb-item active">Import Students</li>
                        </ol>
                    </nav>

                    @include('session-messages')

                    {{-- Import result banner --}}
                    @if(session('import_result'))
                        @php $result = session('import_result'); @endphp
                        <div class="alert alert-{{ $result['imported'] > 0 ? 'success' : 'warning' }} mb-3">
                            <strong><i class="bi bi-check2-circle me-1"></i> Import Complete</strong><br>
                            {{ $result['imported'] }} student(s) imported successfully.
                            @if($result['skipped'] > 0)
                                {{ $result['skipped'] }} row(s) failed.
                            @endif
                            @if(!empty($result['rowErrors']))
                                <ul class="mt-2 mb-0">
                                    @foreach($result['rowErrors'] as $err)
                                        <li>{{ $err }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger mb-3">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Step 1: Upload + Download template --}}
                    @if(!isset($parsed))
                    <div class="row g-3">
                        <div class="col-md-7">
                            <div class="card shadow-sm">
                                <div class="card-header bg-dark text-white fw-semibold">
                                    <i class="bi bi-upload me-1"></i> Step 1 — Upload Filled Template
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('import.students.preview') }}" enctype="multipart/form-data">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label">Select Excel file (.xlsx)</label>
                                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                                            <div class="form-text">Max 5 MB. Must use the official template.</div>
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-eye me-1"></i> Validate &amp; Preview
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="card shadow-sm border-primary">
                                <div class="card-header bg-primary text-white fw-semibold">
                                    <i class="bi bi-download me-1"></i> Step 0 — Download Template
                                </div>
                                <div class="card-body">
                                    <p class="small text-muted mb-3">Download the official template, fill it in, then upload above.</p>
                                    <a href="{{ route('import.students.template') }}" class="btn btn-outline-primary w-100">
                                        <i class="bi bi-file-earmark-excel me-1"></i> Download StudentImportTemplate.xlsx
                                    </a>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header bg-light fw-semibold">How it works</div>
                                <div class="card-body small text-muted">
                                    <ol class="ps-3 mb-0">
                                        <li>Download the template</li>
                                        <li>Fill in student data (one row per student)</li>
                                        <li>Upload — system validates all rows</li>
                                        <li>Review the preview table</li>
                                        <li>Click <strong>Confirm Import</strong></li>
                                    </ol>
                                    <hr class="my-2">
                                    <ul class="ps-3 mb-0">
                                        <li>Invalid rows are skipped — valid ones still import</li>
                                        <li>Duplicate warnings shown but still imported</li>
                                        <li>All imported students get status: <strong>Confirmed</strong></li>
                                        <li>Default password: <code>dga@student2026</code></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Step 2: Preview table --}}
                    @isset($parsed)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Step 2 — Review &amp; Confirm</h5>
                        <a href="{{ route('import.students') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Upload Different File
                        </a>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-auto">
                            <span class="badge bg-success fs-6 p-2">
                                <i class="bi bi-check-circle me-1"></i> {{ $validCount }} Valid
                            </span>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-warning text-dark fs-6 p-2">
                                <i class="bi bi-exclamation-triangle me-1"></i> {{ $warningCount }} Warning
                            </span>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-danger fs-6 p-2">
                                <i class="bi bi-x-circle me-1"></i> {{ $errorCount }} Error (will be skipped)
                            </span>
                        </div>
                    </div>

                    @if($validCount + $warningCount > 0)
                        <form method="POST" action="{{ route('import.students.commit') }}">
                            @csrf
                            <button type="submit" class="btn btn-success mb-3"
                                onclick="return confirm('Import {{ $validCount + $warningCount }} student(s) as confirmed admissions? This cannot be undone.')">
                                <i class="bi bi-check2-all me-1"></i>
                                Confirm Import ({{ $validCount + $warningCount }} rows)
                            </button>
                        </form>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" style="font-size:13px;">
                            <thead class="table-dark">
                                <tr>
                                    <th>Row</th>
                                    <th>Status</th>
                                    <th>Name</th>
                                    <th>DOB</th>
                                    <th>Gender</th>
                                    <th>Class</th>
                                    <th>Section</th>
                                    <th>Category</th>
                                    <th>Father</th>
                                    <th>Issues</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($parsed as $row)
                                @php
                                    $d = $row['data'];
                                    if ($row['status'] === 'error')   $rowClass = 'table-danger';
                                    elseif ($row['status'] === 'warning') $rowClass = 'table-warning';
                                    else $rowClass = 'table-success';
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    <td>{{ $row['line'] }}</td>
                                    <td>
                                        @if($row['status'] === 'error')
                                            <span class="badge bg-danger">Error</span>
                                        @elseif($row['status'] === 'warning')
                                            <span class="badge bg-warning text-dark">Warning</span>
                                        @else
                                            <span class="badge bg-success">Valid</span>
                                        @endif
                                    </td>
                                    <td>{{ $d['student_name'] }}</td>
                                    <td>{{ $d['date_of_birth'] }}</td>
                                    <td>{{ $d['gender'] }}</td>
                                    <td>{{ $d['class_name'] }}</td>
                                    <td>{{ $d['section_name'] }}</td>
                                    <td>{{ $d['fee_category'] }}</td>
                                    <td>{{ $d['father_name'] }}</td>
                                    <td>
                                        @if(!empty($row['errors']))
                                            <ul class="mb-0 ps-3 text-danger" style="font-size:12px;">
                                                @foreach($row['errors'] as $e)<li>{{ $e }}</li>@endforeach
                                            </ul>
                                        @endif
                                        @if(!empty($row['warnings']))
                                            <ul class="mb-0 ps-3 text-warning" style="font-size:12px;">
                                                @foreach($row['warnings'] as $w)<li>{{ $w }}</li>@endforeach
                                            </ul>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($validCount + $warningCount > 0)
                        <form method="POST" action="{{ route('import.students.commit') }}">
                            @csrf
                            <button type="submit" class="btn btn-success"
                                onclick="return confirm('Import {{ $validCount + $warningCount }} student(s) as confirmed admissions? This cannot be undone.')">
                                <i class="bi bi-check2-all me-1"></i>
                                Confirm Import ({{ $validCount + $warningCount }} rows)
                            </button>
                            <a href="{{ route('import.students') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
                        </form>
                    @else
                        <div class="alert alert-danger mt-2">All rows have errors — fix the file and re-upload.</div>
                        <a href="{{ route('import.students') }}" class="btn btn-outline-secondary">Try Again</a>
                    @endif
                    @endisset

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
