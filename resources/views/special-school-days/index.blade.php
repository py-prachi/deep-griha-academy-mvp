@extends('layouts.app')
@section('title', 'Special School Days')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">

                    <h4 class="mb-1"><i class="bi bi-calendar-plus me-1"></i> Special School Days</h4>
                    <p class="text-muted small mb-3">
                        Mark a Saturday or Sunday the school is actually open for classes — this makes attendance
                        possible on that date and counts it as a working day when calculating attendance percentages.
                        Weekdays don't need to be added here; they're already working days by default.
                    </p>

                    @include('session-messages')

                    <div class="row g-3">
                        <div class="col-md-5">
                            <div class="card shadow-sm">
                                <div class="card-header bg-light fw-semibold">Add a Special School Day</div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('special-school-days.store') }}">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label">Date (Saturday/Sunday) <span class="text-danger">*</span></label>
                                            <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                                                   value="{{ old('date') }}" required>
                                            @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Reason <span class="text-muted small">(optional)</span></label>
                                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                                   value="{{ old('name') }}" placeholder="e.g. Makeup class, Sports Day">
                                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="bi bi-plus-circle me-1"></i> Add
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-7">
                            <div class="card shadow-sm">
                                <div class="card-header bg-light fw-semibold">This Session's Special School Days</div>
                                <div class="card-body p-0">
                                    @if($specialDays->isEmpty())
                                        <p class="text-muted small p-3 mb-0">None added yet.</p>
                                    @else
                                    <table class="table table-sm mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>Reason</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($specialDays as $day)
                                            <tr>
                                                <td>
                                                    {{ $day->date->format('d M Y') }}
                                                    <span class="text-muted">({{ $day->date->format('l') }})</span>
                                                </td>
                                                <td>{{ $day->name ?: '—' }}</td>
                                                <td class="text-end">
                                                    <form method="POST" action="{{ route('special-school-days.destroy', $day->id) }}"
                                                          onsubmit="return confirm('Remove this Special School Day? Attendance already taken that day will stay, but it will stop counting as a working day.')">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-sm btn-outline-danger py-0 px-2" title="Remove">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
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
