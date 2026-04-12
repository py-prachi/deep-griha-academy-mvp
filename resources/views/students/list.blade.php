@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h5 class="mb-1"><i class="bi bi-people me-1"></i> Students</h5>
                    <p class="text-muted small mb-3">Select a class and section to view the student list.</p>

                    @include('session-messages')

                    <form class="row g-2 mb-3 align-items-end" action="{{route('student.list.show')}}" method="GET">
                        <div class="col-auto">
                            <label class="form-label form-label-sm mb-1">Class</label>
                            <select onchange="getSections(this);" class="form-select form-select-sm" name="class_id" required>
                                <option value="" disabled {{ !request()->query('class_id') ? 'selected' : '' }}>Select class</option>
                                @isset($school_classes)
                                    @foreach ($school_classes as $school_class)
                                        <option value="{{ $school_class->id }}" {{ $school_class->id == request()->query('class_id') ? 'selected' : '' }}>
                                            {{ $school_class->class_name }}
                                        </option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>
                        <div class="col-auto">
                            <label class="form-label form-label-sm mb-1">Section</label>
                            <select class="form-select form-select-sm" id="section-select" name="section_id" required>
                                <option value="{{ request()->query('section_id') }}">{{ request()->query('section_name') ?: 'Select section' }}</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search me-1"></i> Load</button>
                        </div>
                    </form>

                    @if($studentList->isEmpty())
                        <div class="alert alert-light border mt-2" style="max-width:420px;">
                            <i class="bi bi-arrow-up-circle me-1 text-muted"></i>
                            Select a class and section above to view students.
                        </div>
                    @else
                    @php
                        $first = $studentList->first();
                        $classLabel   = $first->schoolClass->class_name ?? '';
                        $sectionLabel = $first->section->section_name ?? '';
                    @endphp
                    <p class="text-muted small mb-2">
                        Showing <strong>{{ $studentList->count() }}</strong> student(s) —
                        {{ $classLabel }}{{ $sectionLabel ? ' — ' . $sectionLabel : '' }}
                    </p>
                    <div class="bg-white border shadow-sm p-3">
                        <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width:50px;">Roll</th>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        @if(auth()->user()->role === 'admin')
                                        <th>Email</th>
                                        <th>ID Card</th>
                                        @endif
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($studentList->sortBy('roll_number') as $student)
                                    <tr>
                                        <td class="text-muted">{{ $student->roll_number ?? '—' }}</td>
                                        <td>{{ $student->student->first_name }} {{ $student->student->last_name }}</td>
                                        <td>{{ $student->student->phone }}</td>
                                        @if(auth()->user()->role === 'admin')
                                        <td class="text-muted small" style="font-size:0.82rem;">{{ $student->student->email }}</td>
                                        <td class="text-muted small">{{ $student->id_card_number }}</td>
                                        @endif
                                        <td>
                                            <a href="{{route('student.attendance.show', ['id' => $student->student->id])}}" class="btn btn-sm btn-outline-secondary py-0 px-2"><i class="bi bi-calendar2-week"></i> Attendance</a>
                                            <a href="{{url('students/view/profile/'.$student->student->id)}}" class="btn btn-sm btn-outline-secondary py-0 px-2"><i class="bi bi-person"></i> Profile</a>
                                            @if(auth()->user()->role === 'admin')
                                            <form method="POST" action="{{ route('admin.resetPassword', $student->student->id) }}" class="d-inline"
                                                onsubmit="return confirm('Reset password to default for {{ $student->student->first_name }}?')">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-warning py-0 px-2"><i class="bi bi-key"></i> Reset PW</button>
                                            </form>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@push('scripts')
<script>
    function getSections(obj, preselectedSectionId) {
        var class_id = obj.options[obj.selectedIndex].value;
        var url = "{{route('get.sections.courses.by.classId')}}?class_id=" + class_id;
        fetch(url)
        .then((resp) => resp.json())
        .then(function(data) {
            var sectionSelect = document.getElementById('section-select');
            sectionSelect.options.length = 0;
            data.sections.unshift({'id': 0, 'section_name': 'Please select a section'});
            data.sections.forEach(function(section, key) {
                sectionSelect[key] = new Option(section.section_name, section.id);
            });
            if (preselectedSectionId) {
                sectionSelect.value = preselectedSectionId;
            }
        })
        .catch(function(error) {
            console.log(error);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        var classSelect = document.querySelector('select[name="class_id"]');
        var preselectedSectionId = '{{ request()->query("section_id") }}';
        if (classSelect && classSelect.value) {
            getSections(classSelect, preselectedSectionId);
        }
    });
</script>
@endpush
@endsection
