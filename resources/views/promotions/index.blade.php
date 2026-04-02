@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h4 class="mb-3"><i class="bi bi-sort-numeric-up-alt me-1"></i> Promotions</h4>

                    @include('session-messages')

                    {{-- All-classes overview --}}
                    <div class="card mb-4">
                        <div class="card-header bg-dark text-white">
                            <strong>All Classes — Promotion Status</strong>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-bordered table-hover mb-0">
                                <thead class="table-secondary">
                                    <tr>
                                        <th>Class</th>
                                        <th>Sections</th>
                                        <th>Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @isset($previousSessionClasses)
                                        @foreach($previousSessionClasses as $school_class)
                                        @php
                                            $cid   = $school_class->schoolClass->id;
                                            $total = $classSummary[$cid]['total'] ?? 0;
                                            $done  = $classSummary[$cid]['done']  ?? 0;
                                            $allDone = ($total > 0 && $done == $total);
                                            $noneDone = ($done == 0);
                                        @endphp
                                        <tr class="{{ $allDone ? 'table-success' : '' }}">
                                            <td><strong>{{ $school_class->schoolClass->class_name }}</strong></td>
                                            <td>{{ $done }} / {{ $total }} sections promoted</td>
                                            <td>
                                                @if($allDone)
                                                    <span class="badge bg-success">Done</span>
                                                @elseif($noneDone)
                                                    <span class="badge bg-warning text-dark">Not Started</span>
                                                @else
                                                    <span class="badge bg-info text-dark">In Progress</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('promotions.index', ['class_id' => $cid]) }}"
                                                   class="btn btn-sm {{ $class_id == $cid ? 'btn-primary' : 'btn-outline-primary' }}">
                                                    {{ $allDone ? 'View' : 'Promote' }}
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    @endisset
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Section detail for selected class --}}
                    @if($class_id && isset($previousSessionSections) && count($previousSessionSections) > 0)
                    @php
                        $selectedClassName = '';
                        foreach($previousSessionClasses as $sc) {
                            if ($sc->schoolClass->id == $class_id) { $selectedClassName = $sc->schoolClass->class_name; break; }
                        }
                    @endphp
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <strong>{{ $selectedClassName }} — Sections</strong>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-bordered mb-0">
                                <thead class="table-secondary">
                                    <tr>
                                        <th>Section</th>
                                        <th>Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($previousSessionSections as $previousSessionSection)
                                    @php $alreadyDone = in_array($previousSessionSection->section->id, $promotedSectionIds); @endphp
                                    <tr>
                                        <td>Section {{ $previousSessionSection->section->section_name }}</td>
                                        <td>
                                            @if($alreadyDone)
                                                <span class="badge bg-success">Promoted</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Not Promoted</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($alreadyDone)
                                                <span class="text-muted small">No action needed</span>
                                            @else
                                                <a href="{{ route('promotions.create', ['previousSessionId' => $previousSessionId, 'previous_section_id' => $previousSessionSection->section->id, 'previous_class_id' => $class_id]) }}"
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-sort-numeric-up-alt"></i> Promote
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
