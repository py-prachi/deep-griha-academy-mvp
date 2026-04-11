@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h5 class="mb-1">
                        <i class="bi bi-check2-square me-1"></i>
                        Pre-Primary Skill Assessment
                    </h5>
                    <p class="text-muted small mb-2">
                        {{ $schoolClass->class_name ?? '' }} &mdash; {{ $section->section_name ?? '' }}
                        &nbsp;|&nbsp; Term {{ $term }}
                    </p>

                    @include('session-messages')

                    {{-- Controls bar --}}
                    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                        <div>
                            <a href="{{ route('preprimary.entry', ['class_id' => $class_id, 'section_id' => $section_id, 'term' => 1]) }}"
                                class="btn btn-sm {{ $term == 1 ? 'btn-primary' : 'btn-outline-secondary' }}">Term 1</a>
                            <a href="{{ route('preprimary.entry', ['class_id' => $class_id, 'section_id' => $section_id, 'term' => 2]) }}"
                                class="btn btn-sm {{ $term == 2 ? 'btn-primary' : 'btn-outline-secondary' }}">Term 2</a>
                        </div>
                        <a href="{{ route('preprimary.narratives', ['class_id' => $class_id, 'section_id' => $section_id, 'term' => $term]) }}"
                            class="btn btn-sm btn-outline-info">
                            <i class="bi bi-chat-left-text me-1"></i> Remarks
                        </a>
                        <a href="{{ route('preprimary.printClass', ['class_id' => $class_id, 'section_id' => $section_id]) }}"
                            class="btn btn-sm btn-outline-success" target="_blank">
                            <i class="bi bi-printer me-1"></i> Print All
                        </a>
                    </div>

                    {{-- Grade key --}}
                    <div class="alert alert-light border small py-2 mb-3" style="max-width:600px;">
                        <strong>Grade:</strong>
                        <span class="ms-2 fw-semibold text-success">E</span> = Excellent &nbsp;
                        <span class="ms-2 fw-semibold text-primary">S</span> = Satisfactory &nbsp;
                        <span class="ms-2 fw-semibold text-warning">I</span> = Improvement needed &nbsp;
                        <span class="ms-2 fw-semibold text-danger">D</span> = Still developing
                    </div>

                    @if($allPromotions->count() === 0)
                    <div class="alert alert-warning" style="max-width:560px;">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        No students enrolled in <strong>{{ $schoolClass->class_name }}</strong>
                        &mdash; <strong>{{ $section->section_name }}</strong> for this session.
                        <div class="mt-2">
                            <a href="{{ route('preprimary.entry') }}" class="btn btn-sm btn-outline-secondary">&larr; Pick a different class</a>
                        </div>
                    </div>
                    @else

                    {{-- Pagination: which students are shown --}}
                    @php
                        $baseParams = ['class_id' => $class_id, 'section_id' => $section_id, 'term' => $term];
                    @endphp
                    @if($totalPages > 1)
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="small text-muted">
                            Students {{ ($page - 1) * $perPage + 1 }}–{{ min($page * $perPage, $allPromotions->count()) }}
                            of {{ $allPromotions->count() }}
                        </span>
                        @if($page > 1)
                        <a href="{{ route('preprimary.entry', array_merge($baseParams, ['page' => $page - 1])) }}"
                            class="btn btn-sm btn-outline-secondary">&laquo; Prev</a>
                        @endif
                        @for($p = 1; $p <= $totalPages; $p++)
                        <a href="{{ route('preprimary.entry', array_merge($baseParams, ['page' => $p])) }}"
                            class="btn btn-sm {{ $p == $page ? 'btn-primary' : 'btn-outline-secondary' }}">{{ $p }}</a>
                        @endfor
                        @if($page < $totalPages)
                        <a href="{{ route('preprimary.entry', array_merge($baseParams, ['page' => $page + 1])) }}"
                            class="btn btn-sm btn-outline-secondary">Next &raquo;</a>
                        @endif
                    </div>
                    @endif

                    <form method="POST" action="{{ route('preprimary.store') }}">
                        @csrf
                        <input type="hidden" name="class_id" value="{{ $class_id }}">
                        <input type="hidden" name="section_id" value="{{ $section_id }}">
                        <input type="hidden" name="term" value="{{ $term }}">
                        <input type="hidden" name="page" value="{{ $page }}">

                        <div style="overflow-x:auto;">
                        <table class="table table-bordered table-sm align-middle" style="font-size:0.88rem;">
                            <thead class="table-dark">
                                <tr>
                                    <th style="min-width:260px; position:sticky; left:0; z-index:2; background:#212529;">Skill / Competency</th>
                                    @foreach($promotions as $p)
                                    <th class="text-center" style="min-width:80px;">
                                        <div style="font-size:0.82rem;">{{ $p->student->first_name ?? '' }}</div>
                                        @if($p->roll_number)
                                        <div class="fw-normal opacity-75" style="font-size:0.72rem;">#{{ $p->roll_number }}</div>
                                        @endif
                                    </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($skills as $category => $skillList)
                                <tr class="table-secondary">
                                    <td colspan="{{ $promotions->count() + 1 }}"
                                        style="font-size:0.82rem; font-weight:600; background:#e9ecef; position:sticky; left:0;">
                                        {{ $category }}
                                    </td>
                                </tr>
                                @foreach($skillList as $code => $label)
                                <tr>
                                    <td style="position:sticky; left:0; background:#fff; z-index:1;">{{ $label }}</td>
                                    @foreach($promotions as $p)
                                    @php
                                        $key = $p->student_id . '.' . $code;
                                        $val = $existingGrades[$key] ?? '';
                                    @endphp
                                    <td class="text-center p-1">
                                        <select name="grades[{{ $p->student_id }}][{{ $code }}]"
                                            class="form-select form-select-sm text-center px-1"
                                            style="font-size:0.88rem; font-weight:600;">
                                            <option value="" {{ $val === '' ? 'selected' : '' }}>—</option>
                                            <option value="E" {{ $val === 'E' ? 'selected' : '' }}
                                                style="color:#198754;">E</option>
                                            <option value="S" {{ $val === 'S' ? 'selected' : '' }}
                                                style="color:#0d6efd;">S</option>
                                            <option value="I" {{ $val === 'I' ? 'selected' : '' }}
                                                style="color:#ffc107;">I</option>
                                            <option value="D" {{ $val === 'D' ? 'selected' : '' }}
                                                style="color:#dc3545;">D</option>
                                        </select>
                                    </td>
                                    @endforeach
                                </tr>
                                @endforeach
                                @endforeach
                            </tbody>
                        </table>
                        </div>

                        <div class="d-flex align-items-center gap-3 mt-3 mb-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Save Grades
                            </button>
                            @if($totalPages > 1)
                            <span class="text-muted small">Saves this page only. Navigate pages to enter all students.</span>
                            @endif
                        </div>
                    </form>

                    {{-- Print links --}}
                    <div class="card mb-4" style="max-width:480px;">
                        <div class="card-header py-2 small fw-semibold">Print Individual Report Cards</div>
                        <div class="card-body py-2">
                            <ul class="list-unstyled mb-0">
                                @foreach($allPromotions as $p)
                                <li class="mb-1">
                                    <a href="{{ route('preprimary.printReportCard', $p->student_id) }}" target="_blank"
                                        class="text-decoration-none small">
                                        <i class="bi bi-file-earmark-text me-1"></i>
                                        {{ $p->student->first_name ?? '' }} {{ $p->student->last_name ?? '' }}
                                        @if($p->roll_number) <span class="text-muted">(#{{ $p->roll_number }})</span> @endif
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    @endif {{-- end has students --}}

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
