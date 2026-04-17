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
                                            $sections = $classSections[$cid] ?? collect();
                                            $firstSection = $sections->first();
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
                                                @if($sections->count() == 1 && $firstSection)
                                                    {{-- Single section: go directly to promote page --}}
                                                    <a href="{{ route('promotions.create', ['previousSessionId' => $previousSessionId, 'previous_section_id' => $firstSection->section->id, 'previous_class_id' => $cid]) }}"
                                                       class="btn btn-sm {{ $allDone ? 'btn-outline-secondary' : 'btn-outline-primary' }}">
                                                        {{ $allDone ? 'View' : 'Promote' }}
                                                    </a>
                                                @else
                                                    {{-- Multiple sections: go to class sections page --}}
                                                    <a href="{{ route('promotions.class', $cid) }}"
                                                       class="btn btn-sm {{ $allDone ? 'btn-outline-secondary' : 'btn-outline-primary' }}">
                                                        {{ $allDone ? 'View' : 'Promote' }}
                                                    </a>
                                                @endif
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

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
