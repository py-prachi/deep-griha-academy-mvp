@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">

                    <h5 class="mb-1"><i class="bi bi-diagram-3 me-1"></i> Classes</h5>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb small mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                            <li class="breadcrumb-item active">Classes</li>
                        </ol>
                    </nav>

                    @include('session-messages')

                    @isset($school_classes)
                        @foreach ($school_classes as $school_class)
                        @php
                            $classSections = isset($school_sections)
                                ? $school_sections->where('class_id', $school_class->id)
                                : collect();
                            $syllabusCount = isset($school_class->syllabi) ? $school_class->syllabi->count() : 0;
                        @endphp

                        <div class="card mb-3 shadow-sm">
                            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                                <span class="fw-semibold">
                                    <i class="bi bi-mortarboard me-1 text-muted"></i>
                                    {{ $school_class->class_name }}
                                </span>
                                <div class="d-flex align-items-center gap-2">
                                    @can('edit classes')
                                    <a href="{{ route('class.edit', ['id' => $school_class->id]) }}"
                                        class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:0.8rem;">
                                        <i class="bi bi-pencil me-1"></i> Edit Class
                                    </a>
                                    @endcan
                                </div>
                            </div>

                            <div class="card-body p-0">
                                @if($classSections->isEmpty())
                                <div class="text-muted small p-3">No sections set up for this class.</div>
                                @else
                                <table class="table table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">Section</th>
                                            <th>Room</th>
                                            <th class="text-center">Students</th>
                                            <th class="text-center">Timetable</th>
                                            <th class="text-center">Syllabus</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($classSections as $section)
                                        @php
                                            $studentCount = $studentCounts[$school_class->id][$section->id] ?? 0;
                                        @endphp
                                        <tr>
                                            <td class="ps-3 fw-semibold">{{ $section->section_name }}</td>
                                            <td class="text-muted small">{{ $section->room_no ?: '—' }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('student.list.show', ['class_id' => $school_class->id, 'section_id' => $section->id, 'section_name' => $section->section_name]) }}"
                                                    class="text-decoration-none">
                                                    {{ $studentCount }}
                                                    <i class="bi bi-people ms-1 text-muted" style="font-size:0.8rem;"></i>
                                                </a>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('timetable.show', ['class_id' => $school_class->id, 'section_id' => $section->id]) }}"
                                                    class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:0.78rem;">
                                                    <i class="bi bi-calendar4-week me-1"></i> View
                                                </a>
                                            </td>
                                            <td class="text-center text-muted small">
                                                {{ $syllabusCount > 0 ? $syllabusCount . ' file' . ($syllabusCount > 1 ? 's' : '') : '—' }}
                                            </td>
                                            <td class="text-end pe-3">
                                                @can('edit sections')
                                                <a href="{{ route('section.edit', ['id' => $section->id]) }}"
                                                    class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:0.78rem;">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                @endcan
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @endif
                            </div>
                        </div>

                        @endforeach
                    @endisset

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
