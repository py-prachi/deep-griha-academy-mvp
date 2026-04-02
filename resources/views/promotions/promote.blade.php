@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <div class="d-flex align-items-center mb-3">
                        <a href="{{ route('promotions.index', ['class_id' => $schoolClass->id]) }}" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <h4 class="mb-0">
                            <i class="bi bi-person-lines-fill me-1"></i>
                            Promote — {{ $schoolClass->class_name }}, Section {{ $section->section_name }}
                        </h4>
                    </div>

                    @include('session-messages')

                    @php $isClass8 = ($schoolClass->class_name === 'Class 8'); @endphp

                    @if($isClass8)
                    <div class="alert alert-warning mb-3">
                        <i class="bi bi-mortarboard-fill me-1"></i>
                        <strong>Class 8 — Final Year.</strong>
                        Use <strong>Graduate All</strong> if all students have passed out, or tick individually.
                        Leave unchecked for students repeating — they need a class/section assigned.
                    </div>
                    @endif

                    <div class="mb-4 mt-2">
                        <form action="{{route('promotions.store')}}" method="POST">
                            @csrf

                            {{-- Apply to All bar --}}
                            <div class="card mb-3 border-primary">
                                <div class="card-body py-2">
                                    <div class="row align-items-center g-2">
                                        <div class="col-auto">
                                            <strong class="text-primary">
                                                <i class="bi bi-lightning-fill me-1"></i>Apply to All:
                                            </strong>
                                        </div>
                                        <div class="col-md-3">
                                            <select class="form-select form-select-sm" id="bulkClass">
                                                <option value="" disabled selected>Select target class</option>
                                                @foreach ($school_classes as $school_class)
                                                    <option value="{{ $school_class->id }}">{{ $school_class->class_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <select class="form-select form-select-sm" id="bulkSection" disabled>
                                                <option value="" disabled selected>Select target section</option>
                                            </select>
                                        </div>
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-sm btn-primary" onclick="applyToAll()">
                                                <i class="bi bi-check2-all me-1"></i>Apply to All
                                            </button>
                                        </div>
                                        @if($isClass8)
                                        <div class="col-auto ms-3">
                                            <button type="button" class="btn btn-sm btn-warning" onclick="graduateAll()">
                                                <i class="bi bi-mortarboard-fill me-1"></i>Graduate All
                                            </button>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>#ID Card No.</th>
                                            <th>Name</th>
                                            <th>From Class</th>
                                            <th>From Section</th>
                                            @if($isClass8)
                                            <th class="text-center">Graduate?</th>
                                            @endif
                                            <th>To Class</th>
                                            <th>To Section</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @isset($students)
                                            @foreach ($students as $index => $student)
                                            <tr id="row-{{ $index }}">
                                                <td>
                                                    <input type="text" class="form-control form-control-sm" name="id_card_number[{{$student->student->id}}]" value="{{$student->id_card_number}}">
                                                </td>
                                                <td>{{ $student->student->first_name }} {{ $student->student->last_name }}</td>
                                                <td>{{ $schoolClass->class_name }}</td>
                                                <td>{{ $section->section_name }}</td>
                                                @if($isClass8)
                                                <td class="text-center">
                                                    <input type="checkbox"
                                                        class="form-check-input graduate-check"
                                                        name="graduate[{{ $student->student->id }}]"
                                                        value="1"
                                                        data-index="{{ $index }}"
                                                        onchange="toggleGraduate(this)">
                                                </td>
                                                @endif
                                                <td id="class-cell-{{ $index }}">
                                                    <select onchange="getSections(this, {{ $index }});"
                                                        class="form-select form-select-sm row-class-select"
                                                        id="inputAssignToClass{{ $index }}"
                                                        name="class_id[{{ $index }}]"
                                                        data-index="{{ $index }}">
                                                        <option value="" disabled selected>Select class</option>
                                                        @foreach ($school_classes as $school_class)
                                                            <option value="{{ $school_class->id }}">{{ $school_class->class_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td id="section-cell-{{ $index }}">
                                                    <select class="form-select form-select-sm row-section-select"
                                                        id="inputAssignToSection{{ $index }}"
                                                        name="section_id[{{ $index }}]"
                                                        data-index="{{ $index }}">
                                                        <option value="" disabled selected>Select section</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            @endforeach
                                        @endisset
                                    </tbody>
                                </table>
                            </div>

                            <button type="submit" class="btn btn-success mb-3">
                                <i class="bi bi-sort-numeric-up-alt me-1"></i> Confirm Promotion
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>

<script>
    var sectionsUrl = "{{ route('get.sections.courses.by.classId') }}";

    // Fetch sections for a class_id, then call callback(sections)
    function fetchSections(classId, callback) {
        fetch(sectionsUrl + '?class_id=' + classId)
            .then(function(resp) { return resp.json(); })
            .then(function(data) { callback(data.sections || []); })
            .catch(function(err) { console.log(err); });
    }

    // Populate a single section select from a sections array
    function fillSectionSelect(select, sections, selectedId) {
        select.options.length = 0;
        select.add(new Option('Select section', '', true, false));
        sections.forEach(function(s) {
            var opt = new Option(s.section_name, s.id);
            if (selectedId && s.id == selectedId) opt.selected = true;
            select.add(opt);
        });
    }

    // Called when a per-row class select changes
    function getSections(obj, index) {
        var classId = obj.options[obj.selectedIndex].value;
        var sectionSelect = document.getElementById('inputAssignToSection' + index);
        fetchSections(classId, function(sections) {
            fillSectionSelect(sectionSelect, sections, null);
        });
    }

    // Apply bulk class+section to all non-graduated rows
    function applyToAll() {
        var bulkClass = document.getElementById('bulkClass');
        var bulkSection = document.getElementById('bulkSection');
        var classId = bulkClass.value;
        var sectionId = bulkSection.value;
        var sectionName = bulkSection.options[bulkSection.selectedIndex] ? bulkSection.options[bulkSection.selectedIndex].text : '';

        if (!classId || !sectionId || sectionId === '') {
            alert('Please select both a target class and section before applying.');
            return;
        }

        var rowClassSelects = document.querySelectorAll('.row-class-select');
        rowClassSelects.forEach(function(sel) {
            if (sel.disabled) return; // skip graduated rows
            var index = sel.getAttribute('data-index');
            // Set class value
            sel.value = classId;
            // Set section
            var sectionSelect = document.getElementById('inputAssignToSection' + index);
            sectionSelect.options.length = 0;
            sectionSelect.add(new Option(sectionName, sectionId, true, true));
        });
    }

    // Load bulk section dropdown when bulk class changes
    document.getElementById('bulkClass').addEventListener('change', function() {
        var bulkSection = document.getElementById('bulkSection');
        bulkSection.disabled = true;
        bulkSection.options.length = 0;
        bulkSection.add(new Option('Loading...', '', true, false));
        fetchSections(this.value, function(sections) {
            bulkSection.disabled = false;
            fillSectionSelect(bulkSection, sections, null);
        });
    });

    // Toggle graduate checkbox — disables class/section row
    function toggleGraduate(checkbox) {
        var index = checkbox.getAttribute('data-index');
        var classCell = document.getElementById('class-cell-' + index);
        var sectionCell = document.getElementById('section-cell-' + index);
        var classSelect = document.getElementById('inputAssignToClass' + index);
        var sectionSelect = document.getElementById('inputAssignToSection' + index);
        var row = document.getElementById('row-' + index);
        if (checkbox.checked) {
            classCell.style.opacity = '0.3';
            sectionCell.style.opacity = '0.3';
            classSelect.disabled = true;
            sectionSelect.disabled = true;
            row.classList.add('table-success');
        } else {
            classCell.style.opacity = '1';
            sectionCell.style.opacity = '1';
            classSelect.disabled = false;
            sectionSelect.disabled = false;
            row.classList.remove('table-success');
        }
    }

    // Graduate all — check every graduate checkbox
    function graduateAll() {
        document.querySelectorAll('.graduate-check').forEach(function(cb) {
            if (!cb.checked) {
                cb.checked = true;
                toggleGraduate(cb);
            }
        });
    }
</script>
@endsection
