@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-archive"></i> Cancelled Admissions
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{route('admissions.index')}}">Admissions</a></li>
                            <li class="breadcrumb-item active">Cancelled</li>
                        </ol>
                    </nav>
                    @include('session-messages')

                    <div class="bg-white border shadow-sm p-3">
                        <table class="table table-responsive">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Class</th>
                                    <th>Father's Phone</th>
                                    <th>Inquiry Date</th>
                                    <th>Cancelled On</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($admissions as $admission)
                                <tr>
                                    <td>{{ $admission->student_name }}</td>
                                    <td>{{ $admission->schoolClass->class_name ?? '-' }}</td>
                                    <td>{{ $admission->father_phone ?? $admission->contact_mobile ?? '-' }}</td>
                                    <td>{{ $admission->inquiry_date ? $admission->inquiry_date->format('d M Y') : '-' }}</td>
                                    <td>{{ $admission->deleted_at ? $admission->deleted_at->format('d M Y') : '-' }}</td>
                                    <td>{{ $admission->cancel_reason ?? '-' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No cancelled admissions.</td>
                                </tr>
                                @endforelse
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
