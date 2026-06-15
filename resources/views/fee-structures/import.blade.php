@extends('layouts.app')
@section('title', 'Import Fee Structures')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">

                    <div class="d-flex align-items-center mb-3">
                        <a href="{{ route('fee-structures.index') }}" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <h4 class="mb-0">Import Fee Structures</h4>
                    </div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('fee-structures.index') }}">Fee Structures</a></li>
                            <li class="breadcrumb-item active">Import</li>
                        </ol>
                    </nav>

                    {{-- Alerts --}}
                    @if($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif
                    @if(session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif

                    {{-- Upload card (hidden after preview) --}}
                    @if(!isset($parsed))
                    <div class="card mb-4">
                        <div class="card-header bg-dark text-white fw-bold">
                            <i class="bi bi-upload me-2"></i> Upload Fee Structure Sheet
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">
                                Upload the <strong>FeeStructureTemplate.xlsx</strong> with your fee amounts.
                                Existing rows for the same class + category will be <strong>updated</strong>; new rows will be <strong>added</strong>.
                            </p>
                            <a href="{{ route('fee-structures.import.template') }}" class="btn btn-outline-secondary btn-sm mb-4">
                                <i class="bi bi-download me-1"></i> Download Template
                            </a>

                            <form action="{{ route('fee-structures.import.preview') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Select .xlsx file</label>
                                    <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-eye me-1"></i> Preview
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Instructions --}}
                    <div class="card border-0 bg-light">
                        <div class="card-body small text-muted">
                            <strong>Columns in the template:</strong>
                            <code>class_name</code> &nbsp;|&nbsp;
                            <code>fee_category</code> &nbsp;|&nbsp;
                            <code>tuition_fee (boys)</code> &nbsp;|&nbsp;
                            <code>transport_fee</code> &nbsp;|&nbsp;
                            <code>other_fee</code>
                            <br class="mt-1">
                            Total is auto-calculated. Academic year is taken from the current session.
                            Fee categories: <code>general</code>, <code>rte</code>, <code>coc</code>.
                            Girls tuition fee is auto-calculated at 75% + ₹50 of boys rate for general category.
                        </div>
                    </div>
                    @endif

                    {{-- Preview table --}}
                    @if(isset($parsed))
                    @php
                        $newCount    = collect($parsed)->where('status','valid')->where('data.is_update', false)->count();
                        $hasChanges  = collect($parsed)->where('status','valid')->where('data.is_update', true)->filter(fn($r) => !empty($r['data']['diff']))->count();
                        $noChanges   = collect($parsed)->where('status','valid')->where('data.is_update', true)->filter(fn($r) =>  empty($r['data']['diff']))->count();
                    @endphp

                    @if($hasChanges > 0)
                    <div class="alert alert-warning d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <span><strong>{{ $hasChanges }} row(s) will update existing fee structures.</strong>
                        Changed fields are shown as <span class="text-danger text-decoration-line-through">old</span> → <span class="text-success fw-bold">new</span>.
                        Review carefully before confirming.</span>
                    </div>
                    @endif

                    <div class="card mb-3">
                        <div class="card-header bg-dark text-white d-flex align-items-center flex-wrap gap-2">
                            <span class="fw-bold"><i class="bi bi-eye me-2"></i>Preview</span>
                            @if($newCount)   <span class="badge bg-success">{{ $newCount }} new</span> @endif
                            @if($hasChanges) <span class="badge bg-warning text-dark">{{ $hasChanges }} with changes</span> @endif
                            @if($noChanges)  <span class="badge bg-secondary">{{ $noChanges }} unchanged</span> @endif
                            @if($errorCount) <span class="badge bg-danger">{{ $errorCount }} error(s)</span> @endif
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Row</th>
                                            <th>Class</th>
                                            <th>Category</th>
                                            <th class="text-end">Tuition (Boys)</th>
                                            <th class="text-end">Transport</th>
                                            <th class="text-end">Other</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($parsed as $row)
                                        @php
                                            $d         = $row['data'];
                                            $isError   = $row['status'] === 'error';
                                            $diff      = $d['diff'] ?? [];
                                            $hasChange = $d['is_update'] && !empty($diff);
                                            $unchanged = $d['is_update'] && empty($diff);
                                            $trClass   = $isError ? 'table-danger' : ($hasChange ? 'table-warning' : ($unchanged ? 'table-light' : ''));
                                        @endphp
                                        <tr class="{{ $trClass }}">
                                            <td class="text-muted small">{{ $row['line'] }}</td>
                                            <td class="fw-semibold">{{ $d['class_name'] }}</td>
                                            <td>
                                                @php $catColour = ['general'=>'primary','rte'=>'success','coc'=>'info'][$d['fee_category']] ?? 'secondary'; @endphp
                                                <span class="badge bg-{{ $catColour }}">{{ $d['fee_category'] }}</span>
                                            </td>

                                            @foreach(['tuition_fee','transport_fee','other_fee'] as $field)
                                            @php
                                                $newVal  = $d[$field] !== '' ? (float)$d[$field] : null;
                                                $changed = isset($diff[$field]);
                                                $oldVal  = $changed ? $diff[$field]['old'] : null;
                                            @endphp
                                            <td class="text-end" @if($changed) style="background:#fff3cd" @endif>
                                                @if($changed)
                                                    <span class="text-danger text-decoration-line-through small d-block lh-1">₹{{ number_format((float)$oldVal,0) }}</span>
                                                    <span class="text-success fw-bold">₹{{ number_format((float)$newVal,0) }}</span>
                                                @elseif($isError)
                                                    <span class="text-muted small">{{ $newVal !== null ? '₹'.number_format($newVal,0) : '—' }}</span>
                                                @else
                                                    <span class="{{ $unchanged ? 'text-muted' : '' }}">{{ $newVal !== null ? '₹'.number_format($newVal,0) : '—' }}</span>
                                                @endif
                                            </td>
                                            @endforeach

                                            <td>
                                                @if($isError)
                                                    <span class="text-danger small"><i class="bi bi-x-circle me-1"></i>{{ implode('; ', $row['errors']) }}</span>
                                                @elseif($hasChange)
                                                    <span class="text-warning small"><i class="bi bi-pencil-fill me-1"></i>{{ count($diff) }} field(s) changed</span>
                                                @elseif($unchanged)
                                                    <span class="text-secondary small"><i class="bi bi-dash me-1"></i>No change</span>
                                                @else
                                                    <span class="text-success small"><i class="bi bi-check-circle me-1"></i>New</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    @if($validCount > 0)
                    <form action="{{ route('fee-structures.import.commit') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-lg me-1"></i> Confirm Import ({{ $validCount }} rows)
                        </button>
                    </form>
                    @endif
                    <a href="{{ route('fee-structures.import') }}" class="btn btn-outline-secondary ms-2">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Upload Different File
                    </a>
                    @endif

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
