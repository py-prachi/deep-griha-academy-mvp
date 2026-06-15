@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-person-lines-fill"></i> Teacher List
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Teacher List</li>
                        </ol>
                    </nav>
                    @include('session-messages')
                    <div class="mb-4 p-3 bg-white border shadow-sm">
                        <table class="table table-responsive">
                            <thead>
                                <tr>
                                    <th scope="col">Photo</th>
                                    <th scope="col">First Name</th>
                                    <th scope="col">Last Name</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Phone</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($teachers as $teacher)
                                <tr>
                                    <td>
                                        @if (isset($teacher->photo))
                                            <img src="{{asset('/storage'.$teacher->photo)}}" class="rounded" alt="Profile picture" height="30" width="30">
                                        @else
                                            <i class="bi bi-person-square"></i>
                                        @endif
                                    </td>
                                    <td>{{$teacher->first_name}}</td>
                                    <td>{{$teacher->last_name}}</td>
                                    <td>{{$teacher->email}}</td>
                                    <td>{{$teacher->phone}}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{url('teachers/view/profile/'.$teacher->id)}}" role="button" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> Profile</a>
                                            <a href="{{ route('timetable.teacher', ['teacher_id' => $teacher->id]) }}" role="button" class="btn btn-sm btn-outline-secondary"><i class="bi bi-calendar4-week"></i> Timetable</a>
                                            @can('edit users')
                                            <a href="{{route('teacher.edit.show', ['id' => $teacher->id])}}" role="button" class="btn btn-sm btn-outline-primary"><i class="bi bi-pen"></i> Edit</a>
                                            @endcan
                                        </div>
                                        @can('edit users')
                                        <form method="POST" action="{{ route('admin.resetPassword', $teacher->id) }}" class="d-inline"
                                            onsubmit="return confirm('Reset password to default for {{ $teacher->first_name }}?')">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-warning py-0 px-2 ms-1"><i class="bi bi-key"></i> Reset PW</button>
                                        </form>
                                        <button class="btn btn-sm btn-outline-danger py-0 px-2 ms-1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteTeacherModal{{ $teacher->id }}">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                        @endcan

                                        {{-- Delete confirmation modal --}}
                                        <div class="modal fade" id="deleteTeacherModal{{ $teacher->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-sm">
                                                <form method="POST" action="{{ route('teacher.delete', $teacher->id) }}">
                                                    @csrf @method('DELETE')
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-danger text-white">
                                                            <h6 class="modal-title"><i class="bi bi-exclamation-triangle me-1"></i> Delete Teacher</h6>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p class="mb-1">Delete <strong>{{ $teacher->first_name }} {{ $teacher->last_name }}</strong>?</p>
                                                            <p class="text-muted small mb-0">This will remove their class and subject assignments. This cannot be undone.</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
