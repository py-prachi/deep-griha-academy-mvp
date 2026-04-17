@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <div class="d-flex align-items-center mb-3">
                        <a href="{{ route('promotions.index') }}" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <h4 class="mb-0">
                            <i class="bi bi-sort-numeric-up-alt me-1"></i>
                            {{ $schoolClass->class_name }} — Promotion
                        </h4>
                    </div>

                    @include('session-messages')

                    <div class="alert alert-info py-2 mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Promoting <strong>{{ $schoolClass->class_name }}</strong>
                        from <strong>{{ $previousSessionName }}</strong> &rarr; <strong>{{ $latestSessionName }}</strong>
                    </div>

                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <strong>Sections</strong>
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
                                    @php
                                        $secId      = $previousSessionSection->section->id;
                                        $alreadyDone = in_array($secId, $promotedSectionIds);
                                        $detail      = $promotedStudentDetail[$secId] ?? [];
                                    @endphp
                                    <tr>
                                        <td><strong>Section {{ $previousSessionSection->section->section_name }}</strong></td>
                                        <td>
                                            @if($alreadyDone)
                                                <span class="badge bg-success">Done</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Not Promoted</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($alreadyDone)
                                                <button class="btn btn-sm btn-outline-secondary"
                                                    type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#detail-{{ $secId }}">
                                                    <i class="bi bi-people me-1"></i> View Students
                                                </button>
                                            @else
                                                <a href="{{ route('promotions.create', ['previousSessionId' => $previousSessionId, 'previous_section_id' => $secId, 'previous_class_id' => $schoolClass->id]) }}"
                                                   class="btn btn-sm btn-primary">
                                                    <i class="bi bi-sort-numeric-up-alt me-1"></i> Promote
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @if($alreadyDone && !empty($detail))
                                    <tr class="table-light">
                                        <td colspan="3" class="p-0">
                                            <div class="collapse" id="detail-{{ $secId }}">
                                                <table class="table table-sm mb-0 ms-3" style="width:95%">
                                                    <thead class="table-secondary">
                                                        <tr>
                                                            <th>Student</th>
                                                            <th>Promoted To</th>
                                                            <th>Section</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($detail as $d)
                                                        <tr class="{{ $d['graduated'] ? 'table-success' : '' }}">
                                                            <td>{{ $d['name'] }}</td>
                                                            <td>
                                                                @if($d['graduated'])
                                                                    <span class="badge" style="background-color:#6f42c1;">Graduated</span>
                                                                @else
                                                                    {{ $d['new_class'] }}
                                                                @endif
                                                            </td>
                                                            <td>{{ $d['new_section'] }}</td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                    @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
