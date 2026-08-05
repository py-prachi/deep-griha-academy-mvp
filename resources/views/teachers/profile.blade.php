@extends('layouts.app')

@section('content')
<style>
/* .table th:first-child,
.table td:first-child {
  position: relative;
  background-color: #f8f9fa;
} */
</style>
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-person-lines-fill"></i> Teacher
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                          <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                          <li class="breadcrumb-item"><a href="{{route('teacher.list.show')}}">Teacher List</a></li>
                          <li class="breadcrumb-item active" aria-current="page">Profile</li>
                        </ol>
                    </nav>
                    <div class="mb-4">
                        <div class="row">
                            <div class="col-sm-4 col-md-3">
                                <div class="card bg-light">
                                    <div class="px-5 pt-2">
                                        @if (isset($teacher->photo))
                                            <img src="{{asset('/storage'.$teacher->photo)}}" class="rounded-3 card-img-top" alt="Profile photo">
                                        @else
                                            <img src="{{asset('imgs/profile.png')}}" class="rounded-3 card-img-top" alt="Profile photo">
                                        @endif
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title">{{$teacher->first_name}} {{$teacher->last_name}}</h5>
                                    </div>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item">Gender: {{$teacher->gender}}</li>
                                        <li class="list-group-item">Phone: {{$teacher->phone}}</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-sm-8 col-md-9">
                                <div class="p-3 mb-3 border rounded bg-white">
                                    <h6>Teacher Information</h6>
                                    <table class="table table-responsive mt-3">
                                        <tbody>
                                            <tr>
                                                <th scope="row">First Name:</th>
                                                <td>{{$teacher->first_name}}</td>
                                                <th>Last Name:</th>
                                                <td>{{$teacher->last_name}}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Email:</th>
                                                <td style="word-break:break-all;white-space:normal;">{{$teacher->email}}</td>
                                                <th scope="row">Nationality:</th>
                                                <td>{{$teacher->nationality}}</td>
                                            </tr>
                                            <tr>
                                            </tr>
                                            <tr>
                                                <th scope="row">Address:</th>
                                                <td>{{$teacher->address}}</td>
                                                <th>Address2:</th>
                                                <td>{{$teacher->address2}}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">City:</th>
                                                <td>{{$teacher->city}}</td>
                                                <th>PIN Code:</th>
                                                <td>{{$teacher->zip}}</td>
                                            </tr>
                                            <tr>
                                                <th scope="row">Phone:</th>
                                                <td>{{$teacher->phone}}</td>
                                                <th>Gender:</th>
                                                <td>{{$teacher->gender}}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Class Assignments card --}}
                                <div class="p-3 border rounded bg-white">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0"><i class="bi bi-person-badge me-1"></i> Current Class Assignments</h6>
                                        @can('edit users')
                                        <a href="{{ route('academics.teacher-assignments') }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil me-1"></i> Manage Assignments
                                        </a>
                                        @endcan
                                    </div>

                                    <div class="mb-3">
                                        <div class="text-muted small fw-semibold mb-1">Class Teacher</div>
                                        @if($classTeacher)
                                            <span class="badge bg-success fs-6 fw-normal">
                                                {{ optional($classTeacher->schoolClass)->class_name }} —
                                                {{ optional($classTeacher->section)->section_name }}
                                            </span>
                                        @else
                                            <span class="text-muted small">Not assigned as class teacher</span>
                                        @endif
                                    </div>

                                    <div>
                                        <div class="text-muted small fw-semibold mb-1">Subject Teacher</div>
                                        @if($subjectTeachers->isEmpty())
                                            <span class="text-muted small">No subject assignments</span>
                                        @else
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($subjectTeachers as $st)
                                                <span class="badge bg-light text-dark border" style="font-size:0.8rem;">
                                                    {{ optional($st->subject)->name }}
                                                    <span class="text-muted">·</span>
                                                    {{ optional($st->schoolClass)->class_name }} {{ optional($st->section)->section_name }}
                                                </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
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
