@extends('layouts.app')
@section('title', 'Pre-School Daily Plans — Admin')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4 pe-4">

                    <h4 class="mb-1"><i class="bi bi-stars me-1"></i> Pre-School Daily Plans</h4>
                    <p class="text-muted small mb-3">Select a pre-school class to view daily plans.</p>

                    @include('session-messages')

                    {{-- Filter --}}
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-body py-3">
                            <form method="GET" action="{{ route('preschool-plans.index') }}" class="row g-2 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label small fw-semibold mb-1">Class</label>
                                    <select name="class_id" id="class_select" class="form-select form-select-sm">
                                        <option value="">— Select Class —</option>
                                        @foreach($classes as $class)
                                            @foreach($class->sections as $section)
                                            <option value="{{ $class->id }}"
                                                    data-section="{{ $section->id }}"
                                                    {{ ($selClassId == $class->id && $selSectionId == $section->id) ? 'selected' : '' }}>
                                                {{ $class->class_name }} {{ $section->section_name }}
                                            </option>
                                            @endforeach
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="section_id" id="section_id_hidden" value="{{ $selSectionId }}">
                                </div>
                                <div class="col-md-auto">
                                    @if($selClassId)
                                    <a href="{{ route('preschool-plans.index') }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-x"></i> Clear
                                    </a>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>

                    @if($selClassId && $selSectionId)

                    @if($plans->isEmpty())
                    <p class="text-muted fst-italic">No daily plans for this class.</p>
                    @else
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover" style="font-size:0.88rem;">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:130px;">Date</th>
                                    <th style="width:100px;">Day</th>
                                    <th>Teacher</th>
                                    <th style="width:120px;" class="text-center">No. of Activities</th>
                                    <th style="width:70px;" class="text-center">Print</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($plans as $plan)
                                <tr>
                                    <td class="fw-semibold">{{ $plan->plan_date->format('d M Y') }}</td>
                                    <td>{{ $plan->plan_date->format('l') }}</td>
                                    <td class="small">{{ optional($plan->teacher)->first_name }} {{ optional($plan->teacher)->last_name }}</td>
                                    <td class="text-center"><span class="badge bg-secondary">{{ $plan->slots_count }}</span></td>
                                    <td class="text-center">
                                        <a href="{{ route('preschool-plans.print', $plan->id) }}"
                                           class="btn btn-xs btn-outline-secondary py-0 px-1">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    @endif

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>

<script>
document.getElementById('class_select').addEventListener('change', function () {
    var selected = this.options[this.selectedIndex];
    document.getElementById('section_id_hidden').value = selected.dataset.section || '';
    this.form.submit();
});
</script>
@endsection
