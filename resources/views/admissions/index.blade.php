@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <h1 class="display-6 mb-3">
                        <i class="bi bi-person-plus-fill"></i> Admissions
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                            <li class="breadcrumb-item active">Admissions</li>
                        </ol>
                    </nav>
                    @include('session-messages')

                    {{-- Filters --}}
                    <form class="row g-2 mb-4" method="GET" action="{{ route('admissions.index') }}">
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">All Statuses</option>
                                @foreach($statuses as $key => $label)
                                    <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="class_id" class="form-select">
                                <option value="">All Classes</option>
                                @foreach($school_classes as $class)
                                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->class_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control" placeholder="Search by name..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary me-2"><i class="bi bi-search"></i> Filter</button>
                            <a href="{{ route('admissions.index') }}" class="btn btn-outline-secondary">Clear</a>
                        </div>
                    </form>

                    {{-- New Inquiry Button --}}
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">{{ $admissions->total() }} record(s) found</span>
                        <a href="{{ route('admissions.create') }}" class="btn btn-success">
                            <i class="bi bi-person-plus"></i> New Inquiry
                        </a>
                    </div>

                    {{-- Admissions Table --}}
                    <div class="bg-white border shadow-sm p-3">
                        <table class="table table-responsive table-hover">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Class</th>
                                    <th>Father's Phone</th>
                                    <th>City</th>
                                    <th>Inquiry Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($admissions as $admission)
                                <tr>
                                    <td>{{ $admission->student_name }}</td>
                                    <td>{{ $admission->schoolClass->class_name ?? '-' }}</td>
                                    <td>{{ $admission->father_phone ?? $admission->contact_mobile ?? '-' }}</td>
                                    <td>{{ $admission->city ?? $admission->village ?? '-' }}</td>
                                    <td>{{ $admission->inquiry_date ? $admission->inquiry_date->format('d M Y') : '-' }}</td>
                                    <td>
                                        @if($admission->status == 'inquiry')
                                            <span class="badge bg-secondary">Inquiry</span>
                                        @elseif($admission->status == 'pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @elseif($admission->status == 'confirmed')
                                            <span class="badge bg-success">Confirmed</span>
                                        @endif

                                        {{-- Incomplete documents warning --}}
                                        @if($admission->hasIncompleteDocuments())
                                            <span class="badge bg-warning text-dark ms-1" title="Documents incomplete">
                                                <i class="bi bi-exclamation-triangle"></i>
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admissions.show', $admission->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No admissions found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $admissions->appends(request()->query())->links() }}
                    </div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
