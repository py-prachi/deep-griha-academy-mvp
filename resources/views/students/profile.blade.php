@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-person-lines-fill"></i> Student
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                          <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                          <li class="breadcrumb-item"><a href="{{route('student.list.show')}}">Student List</a></li>
                          <li class="breadcrumb-item active" aria-current="page">Profile</li>
                        </ol>
                    </nav>

                    @php
                        $p = $student->parent_info ?? null;
                        $a = $student->admission   ?? null;

                        $father_name    = $p->father_name    ?? $a->father_name    ?? '—';
                        $mother_name    = $p->mother_name    ?? $a->mother_name    ?? '—';
                        $father_phone   = $p->father_phone   ?? $a->father_phone   ?? $a->contact_mobile ?? '—';
                        $mother_phone   = $p->mother_phone   ?? $a->mother_phone   ?? '—';
                        $parent_address = $p->parent_address ?? $a->full_address   ?? '—';

                        $admission_no = $student->dga_admission_no
                                     ?? $student->general_id
                                     ?? ($a ? 'DGA-' . $a->id : '—');
                    @endphp

                    <div class="mb-4">
                        <div class="row">
                            <div class="col-sm-4 col-md-3">
                                <div class="card bg-light">
                                    <div class="px-5 pt-2">
                                        @if (isset($student->photo))
                                            <img src="{{asset('/storage'.$student->photo)}}" class="rounded-3 card-img-top" alt="Profile photo">
                                        @else
                                            <img src="{{asset('imgs/profile.png')}}" class="rounded-3 card-img-top" alt="Profile photo">
                                        @endif
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title">{{$student->first_name}} {{$student->last_name}}</h5>
                                        <p class="card-text">#ID: {{ $promotion_info->id_card_number ?? $admission_no }}</p>
                                    </div>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item">Gender: {{$student->gender ?? '—'}}</li>
                                        <li class="list-group-item">Phone: {{$student->phone ?? '—'}}</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="col-sm-8 col-md-9">

                                <div class="p-3 mb-3 border rounded bg-white">
                                    <h6>Student Information</h6>
                                    <table class="table table-responsive mt-3" style="table-layout:fixed;word-break:break-word;"><colgroup><col style="width:18%"><col style="width:32%"><col style="width:18%"><col style="width:32%"></colgroup>
                                        <tbody>
                                            <tr>
                                                <th scope="row">First Name:</th>
                                                <td>{{$student->first_name ?? '—'}}</td>
                                                <th>Last Name:</th>
                                                <td>{{$student->last_name ?? '—'}}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Email:</th>
                                                <td>{{$student->email ?? '—'}}</td>
                                                <th>Birthday:</th>
                                                <td>{{ $student->birthday ? \Carbon\Carbon::parse($student->birthday)->format('d M Y') : '—' }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Nationality:</th>
                                                <td>{{$student->nationality ?? '—'}}</td>
                                                <th>Religion:</th>
                                                <td>{{$student->religion ?? '—'}}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Address:</th>
                                                <td colspan="3">{{$student->address ?? '—'}}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Address2:</th>
                                                <td colspan="3">{{$student->address2 ?? '—'}}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">City:</th>
                                                <td>{{$student->city ?? '—'}}</td>
                                                <th>PIN Code:</th>
                                                <td>{{$student->zip ?? '—'}}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Transport:</th>
                                                <td>{{ $a && $a->transport_required ? 'Yes' : 'No' }}</td>
                                                <th>Blood Type:</th>
                                                <td>{{$student->blood_type ?? '—'}}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Phone:</th>
                                                <td>{{$student->phone ?? '—'}}</td>
                                                <th>Gender:</th>
                                                <td>{{$student->gender ?? '—'}}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="p-3 mb-3 border rounded bg-white">
                                    <h6>Parents' Information</h6>
                                    <table class="table table-responsive mt-3" style="table-layout:fixed;word-break:break-word;"><colgroup><col style="width:18%"><col style="width:32%"><col style="width:18%"><col style="width:32%"></colgroup>
                                        <tbody>
                                            <tr>
                                                <th scope="row">Father's Name:</th>
                                                <td>{{ $father_name }}</td>
                                                <th>Mother's Name:</th>
                                                <td>{{ $mother_name }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Father's Phone:</th>
                                                <td>{{ $father_phone }}</td>
                                                <th>Mother's Phone:</th>
                                                <td>{{ $mother_phone }}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Address:</th>
                                                <td colspan="3">{{ $parent_address }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    @if(!$p && $a)
                                        <p class="text-muted small mb-0 mt-1">
                                            <i class="bi bi-info-circle"></i>
                                            Sourced from <a href="{{ route('admissions.show', $a->id) }}">Admission record</a>.
                                        </p>
                                    @endif
                                </div>

                                <div class="p-3 mb-3 border rounded bg-white">
                                    <h6>Academic Information</h6>
                                    @if($promotion_info)
                                        <table class="table table-responsive mt-3" style="table-layout:fixed;word-break:break-word;"><colgroup><col style="width:18%"><col style="width:32%"><col style="width:18%"><col style="width:32%"></colgroup>
                                            <tbody>
                                                <tr>
                                                    <th scope="row">Class:</th>
                                                    <td>{{ $promotion_info->section->schoolClass->class_name ?? '—' }}</td>
                                                    <th>Admission No.:</th>
                                                    <td>{{ $admission_no }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">Section:</th>
                                                    <td>{{ $promotion_info->section->section_name ?? '—' }}</td>
                                                    <th>Roll No.:</th>
                                                    <td>{{ $promotion_info->roll_number ?? '—' }}</td>
                                                </tr>
                                                <tr>
                                                    <th scope="row">Fee Category:</th>
                                                    <td>{{ ucfirst($student->fee_category ?? '—') }}</td>
                                                    <th scope="row">At DGA since:</th>
                                                    <td>
                                                        @if($a && $a->confirmed_date)
                                                            @php
                                                                $diff  = \Carbon\Carbon::parse($a->confirmed_date)->diff(now());
                                                                $parts = [];
                                                                if ($diff->y > 0) $parts[] = $diff->y . ' year' . ($diff->y !== 1 ? 's' : '');
                                                                if ($diff->m > 0) $parts[] = $diff->m . ' month' . ($diff->m !== 1 ? 's' : '');
                                                                if (empty($parts)) $parts[] = max(1, $diff->d) . ' day' . ($diff->d !== 1 ? 's' : '');
                                                            @endphp
                                                            <span class="badge bg-primary">{{ implode(' ', $parts) }}</span>
                                                            <small class="text-muted d-block">since {{ \Carbon\Carbon::parse($a->confirmed_date)->format('d M Y') }}</small>
                                                        @else
                                                            —
                                                        @endif
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    @else
                                        <p class="text-muted mt-2 mb-1">
                                            <i class="bi bi-info-circle"></i>
                                            No class assignment found for the current session.
                                            Please assign via <a href="{{ route('promotions.create') }}">Promotions</a>.
                                        </p>
                                    @endif
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
