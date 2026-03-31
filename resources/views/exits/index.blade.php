@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-box-arrow-right"></i> Exited Students
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item active">Exited Students</li>
                        </ol>
                    </nav>

                    @include('session-messages')

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span>{{ count($exits) }} record(s) found</span>
                        <a href="{{ route('exits.create') }}" class="btn btn-danger btn-sm">
                            <i class="bi bi-box-arrow-right"></i> Record Student Exit
                        </a>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <table class="table table-hover table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Student Name</th>
                                        <th>Class</th>
                                        <th>Section</th>
                                        <th>Exit Date</th>
                                        <th>Reason</th>
                                        <th>Rating</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($exits as $exit)
                                        @php $admission = $exit->admission; @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                {{ $admission ? $admission->student_name : '—' }}
                                                @if($admission && $admission->dga_admission_no)
                                                    <br><small class="text-muted">{{ $admission->dga_admission_no }}</small>
                                                @elseif($admission && $admission->general_id)
                                                    <br><small class="text-muted">{{ $admission->general_id }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $admission && $admission->schoolClass ? $admission->schoolClass->class_name : '—' }}</td>
                                            <td>{{ $admission && $admission->section ? $admission->section->section_name : '—' }}</td>
                                            <td>{{ $exit->exit_date ? $exit->exit_date->format('d/m/Y') : '—' }}</td>
                                            <td>
                                                @if($exit->reason_for_leaving)
                                                    {{ \Illuminate\Support\Str::limit($exit->reason_for_leaving, 40) }}
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($exit->rating)
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <i class="bi bi-star{{ $i <= $exit->rating ? '-fill text-warning' : '' }}"></i>
                                                    @endfor
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('exits.show', $exit->id) }}" class="btn btn-outline-secondary btn-sm">View</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">No exited students yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
