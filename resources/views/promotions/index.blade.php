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

                    <form method="POST" action="{{ route('promotions.reassignRollNumbers') }}" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary"
                            onclick="return confirm('Reassign roll numbers for all classes (sorted by first name)?')">
                            <i class="bi bi-sort-alpha-down me-1"></i> Reassign Roll Numbers
                        </button>
                    </form>

                    <div class="alert alert-info py-2 mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Promoting students from <strong>{{ $previousSessionName }}</strong>
                        &rarr; <strong>{{ $latestSessionName }}</strong>
                    </div>

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
                                        <th class="text-center">Students</th>
                                        <th class="text-center">Sections</th>
                                        <th>Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @isset($previousSessionClasses)
                                        @php $grandTotal = 0; $grandDone = 0; @endphp
                                        @foreach($previousSessionClasses as $school_class)
                                        @php
                                            $cid           = $school_class->schoolClass->id;
                                            $total         = $classSummary[$cid]['total']         ?? 0;
                                            $done          = $classSummary[$cid]['done']          ?? 0;
                                            $totalStudents = $classSummary[$cid]['totalStudents']  ?? 0;
                                            $doneStudents  = $classSummary[$cid]['doneStudents']   ?? 0;
                                            $allDone  = ($total > 0 && $done == $total);
                                            $noneDone = ($done == 0);
                                            $grandTotal += $totalStudents;
                                            $grandDone  += $doneStudents;
                                        @endphp
                                        <tr class="{{ $allDone ? 'table-success' : '' }}">
                                            <td><strong>{{ $school_class->schoolClass->class_name }}</strong></td>
                                            <td class="text-center">
                                                <span class="{{ $doneStudents == $totalStudents && $totalStudents > 0 ? 'text-success fw-bold' : '' }}">
                                                    {{ $doneStudents }} / {{ $totalStudents }}
                                                </span>
                                            </td>
                                            <td class="text-center">{{ $done }} / {{ $total }}</td>
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
                                        <tr class="table-dark fw-bold">
                                            <td>Total</td>
                                            <td class="text-center">{{ $grandDone }} / {{ $grandTotal }}</td>
                                            <td colspan="3"></td>
                                        </tr>
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
                                                <a href="{{ route('promotions.create', ['previousSessionId' => $previousSessionId, 'previous_section_id' => $secId, 'previous_class_id' => $class_id]) }}"
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-sort-numeric-up-alt"></i> Promote
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
                    @endif

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
