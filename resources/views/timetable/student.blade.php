@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">

                    <h5 class="mb-1">
                        <i class="bi bi-calendar4-week me-1"></i> My Timetable
                        @if($promotion)
                            &mdash; {{ optional($promotion->schoolClass)->class_name }}
                            {{ optional($promotion->section)->section_name ? ' ' . optional($promotion->section)->section_name : '' }}
                        @endif
                    </h5>
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb small mb-0">
                            <li class="breadcrumb-item"><a href="{{ url('home') }}">Home</a></li>
                            <li class="breadcrumb-item active">My Timetable</li>
                        </ol>
                    </nav>

                    @if($error)
                    <div class="alert alert-warning border" style="max-width:480px;">
                        <i class="bi bi-exclamation-triangle me-1"></i> {{ $error }}
                    </div>
                    @else
                    @include('timetable._day-tabs', ['activeDay' => \Carbon\Carbon::today()->isoWeekday()])
                    @endif

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>
@endsection
