@extends('layouts.app')
@section('content')
<div class="container-fluid">
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

                    {{-- Term / class switcher --}}
                    <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
                        <div>
                            <a href="{{ route('preprimary.entry', ['class_id' => $class_id, 'section_id' => $section_id, 'term' => 1]) }}"
                                class="btn btn-sm {{ $term == 1 ? 'btn-primary' : 'btn-outline-secondary' }}">Term 1</a>
                            <a href="{{ route('preprimary.entry', ['class_id' => $class_id, 'section_id' => $section_id, 'term' => 2]) }}"
                                class="btn btn-sm {{ $term == 2 ? 'btn-primary' : 'btn-outline-secondary' }}">Term 2</a>
                        </div>
                        <a href="{{ route('preprimary.narratives', ['class_id' => $class_id, 'section_id' => $section_id, 'term' => $term]) }}"
                            class="btn btn-sm btn-outline-info">
                            <i class="bi bi-chat-left-text me-1"></i> Narrative Remarks
                        </a>
                        <a href="{{ route('preprimary.printClass', ['class_id' => $class_id, 'section_id' => $section_id]) }}"
                            class="btn btn-sm btn-outline-success" target="_blank">
                            <i class="bi bi-printer me-1"></i> Print All Cards
                        </a>
                    </div>

                    <div class="alert alert-light border small py-2 mb-3" style="max-width:700px;">
                        <strong>Grade Key:</strong>
                        <span class="ms-2">E = Excellent</span>
                        <span class="ms-2">S = Satisfactory</span>
                        <span class="ms-2">I = Improvement needed</span>
                        <span class="ms-2">D = Still developing</span>
                    </div>

                    @if($promotions->count() === 0)
                    <div class="alert alert-warning" style="max-width:560px;">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        No students are enrolled in <strong>{{ $schoolClass->class_name }}</strong>
                        &mdash; <strong>{{ $section->section_name }}</strong> for the current session.
                        Please check admissions / promotions or select a different class.
                        <div class="mt-2">
                            <a href="{{ route('preprimary.entry') }}" class="btn btn-sm btn-outline-secondary">
                                &larr; Pick a different class
                            </a>
                        </div>
                    </div>
                    @else
                    <form method="POST" action="{{ route('preprimary.store') }}">
                        @csrf
                        <input type="hidden" name="class_id" value="{{ $class_id }}">
                        <input type="hidden" name="section_id" value="{{ $section_id }}">
                        <input type="hidden" name="term" value="{{ $term }}">

                        <div style="overflow-x:auto;">
                        <table class="table table-bordered table-sm" style="font-size:0.78rem; min-width:600px;">
                            <thead class="table-dark">
                                <tr>
                                    <th style="min-width:220px;">Skill</th>
                                    @foreach($promotions as $p)
                                    <th class="text-center" style="min-width:72px;">
                                        {{ $p->student->first_name ?? '' }}<br>
                                        <small class="fw-normal text-light opacity-75">
                                            @if($p->roll_number) #{{ $p->roll_number }} @endif
                                        </small>
                                    </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($skills as $category => $skillList)
                                <tr class="table-secondary">
                                    <td colspan="{{ $promotions->count() + 1 }}" class="fw-semibold small py-1 ps-2">
                                        {{ $category }}
                                    </td>
                                </tr>
                                @foreach($skillList as $code => $label)
                                <tr>
                                    <td class="align-middle">{{ $label }}</td>
                                    @foreach($promotions as $p)
                                    @php
                                        $key = $p->student_id . '.' . $code;
                                        $val = $existingGrades[$key] ?? '';
                                    @endphp
                                    <td class="text-center p-1 align-middle">
                                        <select name="grades[{{ $p->student_id }}][{{ $code }}]"
                                            class="form-select form-select-sm p-0 text-center"
                                            style="min-width:54px;font-size:0.78rem;">
                                            <option value="" {{ $val === '' ? 'selected' : '' }}>—</option>
                                            <option value="E" {{ $val === 'E' ? 'selected' : '' }}>E</option>
                                            <option value="S" {{ $val === 'S' ? 'selected' : '' }}>S</option>
                                            <option value="I" {{ $val === 'I' ? 'selected' : '' }}>I</option>
                                            <option value="D" {{ $val === 'D' ? 'selected' : '' }}>D</option>
                                        </select>
                                    </td>
                                    @endforeach
                                </tr>
                                @endforeach
                                @endforeach
                            </tbody>
                        </table>
                        </div>

                        <div class="mt-3 mb-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Save Grades
                            </button>
                        </div>
                    </form>

                    {{-- Student print links --}}
                    <div class="card mb-4" style="max-width:500px;">
                        <div class="card-header py-2 small fw-semibold">Print Individual Report Cards</div>
                        <div class="card-body py-2">
                            <ul class="list-unstyled mb-0">
                                @foreach($promotions as $p)
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
                    @endif {{-- end @else (has students) --}}

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
