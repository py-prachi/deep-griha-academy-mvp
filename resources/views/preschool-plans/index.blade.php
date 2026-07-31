@extends('layouts.app')
@section('title', 'Pre-School Daily Plans')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4 pe-4">

                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <h4 class="mb-0"><i class="bi bi-stars me-1"></i> Pre-School Daily Plans</h4>
                        @if($ctAssignment)
                        <a href="{{ route('preschool-plans.create', ['class_id' => $ctAssignment->class_id, 'section_id' => $ctAssignment->section_id]) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-circle me-1"></i> Add Daily Plan
                        </a>
                        @endif
                    </div>
                    @if($ctAssignment)
                    <p class="text-muted small mb-3">
                        {{ $ctAssignment->schoolClass->class_name }} {{ $ctAssignment->section->section_name }}
                        — day-by-day activity plans
                        @if($ctAssignments->count() > 1)
                            &bull; <a href="{{ route('preschool-plans.index') }}">switch class</a>
                        @endif
                    </p>
                    @else
                    <p class="text-muted small mb-3">Pre-school daily activity plans.</p>
                    @endif

                    @include('session-messages')

                    @if(!$ctAssignment && $ctAssignments->count() > 1)
                    <p class="text-muted small mb-3">You are the Class Teacher for more than one pre-school class — pick one below.</p>
                    <div class="row">
                        @foreach ($ctAssignments as $assignment)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="card-title mb-2">
                                        <i class="bi bi-diagram-2 me-1"></i>
                                        {{ $assignment->schoolClass->class_name }} {{ $assignment->section->section_name }}
                                    </h6>
                                    <a href="{{ route('preschool-plans.index', ['class_id' => $assignment->class_id, 'section_id' => $assignment->section_id]) }}"
                                       class="btn btn-sm btn-outline-primary">View Plans</a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @elseif(!$ctAssignment)
                    <div class="alert alert-warning">
                        You are not assigned as class teacher for a pre-school class (Nursery/Lower KG/Upper KG) in the current session.
                        Ask the admin to assign you via <a href="{{ route('academics.teacher-assignments') }}">Teacher Assignments</a>.
                    </div>
                    @elseif($plans->isEmpty())
                    <div class="alert alert-light text-muted">No daily plans created yet. Click "Add Daily Plan" to get started.</div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover" style="font-size:0.88rem;">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:130px;">Date</th>
                                    <th style="width:100px;">Day</th>
                                    <th style="width:120px;" class="text-center">No. of Activities</th>
                                    <th style="width:140px;" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($plans as $plan)
                                <tr>
                                    <td class="fw-semibold">{{ $plan->plan_date->format('d M Y') }}</td>
                                    <td>{{ $plan->plan_date->format('l') }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $plan->slots_count }}</span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('preschool-plans.print', $plan->id) }}"
                                           target="_blank" class="btn btn-xs btn-outline-secondary py-0 px-1" title="Print">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                        <a href="{{ route('preschool-plans.edit', $plan->id) }}"
                                           class="btn btn-xs btn-outline-secondary py-0 px-1" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" action="{{ route('preschool-plans.destroy', $plan->id) }}"
                                              class="d-inline" onsubmit="return confirm('Delete this plan?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-xs btn-outline-danger py-0 px-1" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
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
@endsection
