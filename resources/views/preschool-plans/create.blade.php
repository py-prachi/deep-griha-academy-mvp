@extends('layouts.app')
@section('title', 'Add Daily Plan')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4 pe-4">

                    <nav aria-label="breadcrumb" class="mb-2">
                        <ol class="breadcrumb small">
                            <li class="breadcrumb-item"><a href="{{ route('preschool-plans.index', ['class_id' => $ctAssignment->class_id, 'section_id' => $ctAssignment->section_id]) }}">Pre-School Plans</a></li>
                            <li class="breadcrumb-item active">Add Daily Plan</li>
                        </ol>
                    </nav>

                    <h4 class="mb-1"><i class="bi bi-stars me-1"></i> Add Daily Plan</h4>
                    <p class="text-muted small mb-3">
                        {{ $ctAssignment->schoolClass->class_name }} {{ $ctAssignment->section->section_name }}
                    </p>

                    @include('session-messages')

                    <div class="card shadow-sm" style="max-width:900px;">
                        <div class="card-body">
                            <form method="POST" action="{{ route('preschool-plans.store') }}" id="plan-form">
                                @csrf
                                <input type="hidden" name="class_id" value="{{ $ctAssignment->class_id }}">
                                <input type="hidden" name="section_id" value="{{ $ctAssignment->section_id }}">

                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Plan Date <span class="text-danger">*</span></label>
                                    <input type="date" name="plan_date" class="form-control @error('plan_date') is-invalid @enderror"
                                           value="{{ old('plan_date') }}" required style="max-width:200px;">
                                    @error('plan_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <h6 class="fw-semibold mb-2 border-bottom pb-1">Activity Slots</h6>

                                <div id="slots-container">
                                    @php $oldSlots = old('slots', [['activity_name'=>'','material'=>'','objective'=>'','actual_teach'=>'','assessment'=>''],['activity_name'=>'','material'=>'','objective'=>'','actual_teach'=>'','assessment'=>''],['activity_name'=>'','material'=>'','objective'=>'','actual_teach'=>'','assessment'=>'']]); @endphp

                                    @foreach($oldSlots as $i => $slot)
                                    <div class="slot-row card mb-3 border-secondary" data-index="{{ $i }}">
                                        <div class="card-header py-2 d-flex justify-content-between align-items-center bg-light">
                                            <span class="fw-semibold small">Activity {{ $i + 1 }}</span>
                                            <button type="button" class="btn btn-xs btn-outline-danger py-0 px-1 remove-slot" title="Remove">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>
                                        <div class="card-body pt-2 pb-2">
                                            <div class="mb-2">
                                                <label class="form-label small fw-semibold mb-1">Activity Name <span class="text-danger">*</span></label>
                                                <input type="text" name="slots[{{ $i }}][activity_name]"
                                                       class="form-control form-control-sm @error('slots.'.$i.'.activity_name') is-invalid @enderror"
                                                       value="{{ $slot['activity_name'] ?? '' }}" required>
                                                @error('slots.'.$i.'.activity_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold mb-1">Material</label>
                                                    <textarea name="slots[{{ $i }}][material]" rows="5" style="min-height:110px;" class="form-control form-control-sm">{{ $slot['material'] ?? '' }}</textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold mb-1">Objective</label>
                                                    <textarea name="slots[{{ $i }}][objective]" rows="5" style="min-height:110px;" class="form-control form-control-sm">{{ $slot['objective'] ?? '' }}</textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold mb-1">Actual Teach</label>
                                                    <textarea name="slots[{{ $i }}][actual_teach]" rows="5" style="min-height:110px;" class="form-control form-control-sm">{{ $slot['actual_teach'] ?? '' }}</textarea>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold mb-1">Assessment</label>
                                                    <textarea name="slots[{{ $i }}][assessment]" rows="5" style="min-height:110px;" class="form-control form-control-sm">{{ $slot['assessment'] ?? '' }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                <button type="button" id="add-slot-btn" class="btn btn-sm btn-outline-secondary mb-4">
                                    <i class="bi bi-plus-circle me-1"></i> Add Slot
                                </button>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-1"></i> Save Plan
                                    </button>
                                    <a href="{{ route('preschool-plans.index', ['class_id' => $ctAssignment->class_id, 'section_id' => $ctAssignment->section_id]) }}" class="btn btn-outline-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>

<script>
(function () {
    var container = document.getElementById('slots-container');
    var addBtn    = document.getElementById('add-slot-btn');
    var slotCount = container.querySelectorAll('.slot-row').length;

    function slotHtml(index) {
        return '<div class="slot-row card mb-3 border-secondary" data-index="' + index + '">' +
            '<div class="card-header py-2 d-flex justify-content-between align-items-center bg-light">' +
                '<span class="fw-semibold small">Activity ' + (index + 1) + '</span>' +
                '<button type="button" class="btn btn-xs btn-outline-danger py-0 px-1 remove-slot" title="Remove"><i class="bi bi-x-lg"></i></button>' +
            '</div>' +
            '<div class="card-body pt-2 pb-2">' +
                '<div class="mb-2"><label class="form-label small fw-semibold mb-1">Activity Name <span class="text-danger">*</span></label>' +
                '<input type="text" name="slots[' + index + '][activity_name]" class="form-control form-control-sm" required></div>' +
                '<div class="row g-2">' +
                    '<div class="col-md-6"><label class="form-label small fw-semibold mb-1">Material</label><textarea name="slots[' + index + '][material]" rows="5" style="min-height:110px;" class="form-control form-control-sm"></textarea></div>' +
                    '<div class="col-md-6"><label class="form-label small fw-semibold mb-1">Objective</label><textarea name="slots[' + index + '][objective]" rows="5" style="min-height:110px;" class="form-control form-control-sm"></textarea></div>' +
                    '<div class="col-md-6"><label class="form-label small fw-semibold mb-1">Actual Teach</label><textarea name="slots[' + index + '][actual_teach]" rows="5" style="min-height:110px;" class="form-control form-control-sm"></textarea></div>' +
                    '<div class="col-md-6"><label class="form-label small fw-semibold mb-1">Assessment</label><textarea name="slots[' + index + '][assessment]" rows="5" style="min-height:110px;" class="form-control form-control-sm"></textarea></div>' +
                '</div>' +
            '</div>' +
        '</div>';
    }

    addBtn.addEventListener('click', function () {
        var div = document.createElement('div');
        div.innerHTML = slotHtml(slotCount++);
        container.appendChild(div.firstChild);
        bindRemove(container.lastChild);
    });

    function bindRemove(row) {
        row.querySelector('.remove-slot').addEventListener('click', function () {
            row.remove();
            reindex();
        });
    }

    function reindex() {
        var rows = container.querySelectorAll('.slot-row');
        rows.forEach(function (row, i) {
            row.querySelector('.fw-semibold.small').textContent = 'Activity ' + (i + 1);
            row.querySelectorAll('[name]').forEach(function (el) {
                el.name = el.name.replace(/slots\[\d+\]/, 'slots[' + i + ']');
            });
        });
        slotCount = rows.length;
    }

    container.querySelectorAll('.slot-row').forEach(function (row) {
        bindRemove(row);
    });
})();
</script>
@endsection
