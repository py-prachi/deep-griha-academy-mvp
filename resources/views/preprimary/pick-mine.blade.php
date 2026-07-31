@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h5 class="mb-1"><i class="bi bi-check2-square me-1"></i> Pre-Primary Assessment</h5>
                    <p class="text-muted small mb-3">You are the Class Teacher for more than one class — pick one below.</p>

                    @include('session-messages')

                    <div class="row">
                        @foreach ($myAssignments as $assignment)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="card-title mb-2">
                                        <i class="bi bi-diagram-2 me-1"></i>
                                        {{ $assignment->schoolClass->class_name }} {{ $assignment->section->section_name }}
                                    </h6>
                                    <a href="{{ route($targetRoute, array_filter([
                                            'class_id'   => $assignment->class_id,
                                            'section_id' => $assignment->section_id,
                                            'term'       => $term,
                                        ])) }}"
                                       class="btn btn-sm btn-outline-primary">Go</a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
