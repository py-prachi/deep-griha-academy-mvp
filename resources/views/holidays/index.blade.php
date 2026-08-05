@extends('layouts.app')
@section('title', 'Holidays')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">

                    <h4 class="mb-1"><i class="bi bi-calendar-x me-1"></i> Holidays</h4>
                    <p class="text-muted small mb-3">
                        These dates are excluded from working-day and attendance-percentage calculations,
                        and show on the shared calendar for admin, teachers, and students.
                    </p>

                    @include('session-messages')

                    <div class="row g-3">
                        <div class="col-md-5">
                            <div class="card shadow-sm">
                                <div class="card-header bg-light fw-semibold">Add a Holiday</div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('holidays.store') }}">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label">Date <span class="text-danger">*</span></label>
                                            <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
                                                   value="{{ old('date') }}" required>
                                            @error('date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">
                                                End Date <span class="text-muted small">(optional — for a holiday period like summer vacation)</span>
                                            </label>
                                            <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror"
                                                   value="{{ old('end_date') }}">
                                            @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            <div class="form-text">Leave blank to add just the one date above. Fill it in to record every day in between too — e.g. 01 May to 31 May for summer break.</div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Name <span class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                                   value="{{ old('name') }}" placeholder="e.g. Diwali, Summer Vacation" required>
                                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="bi bi-plus-circle me-1"></i> Add Holiday
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-7">
                            <div class="card shadow-sm">
                                <div class="card-header bg-light fw-semibold">This Session's Holidays</div>
                                <div class="card-body p-0">
                                    @if(empty($holidayGroups))
                                        <p class="text-muted small p-3 mb-0">No holidays added yet.</p>
                                    @else
                                    <table class="table table-sm mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>Name</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($holidayGroups as $group)
                                            @php $isRange = !$group['start']->isSameDay($group['end']); @endphp
                                            <tr>
                                                <td>
                                                    @if($isRange)
                                                        {{ $group['start']->format('d M Y') }} – {{ $group['end']->format('d M Y') }}
                                                        <span class="badge bg-light text-dark ms-1">{{ count($group['ids']) }} days</span>
                                                    @else
                                                        {{ $group['start']->format('d M Y') }}
                                                        <span class="text-muted">({{ $group['start']->format('l') }})</span>
                                                    @endif
                                                </td>
                                                <td>{{ $group['name'] }}</td>
                                                <td class="text-end">
                                                    @if($isRange)
                                                    <form method="POST" action="{{ route('holidays.destroyRange') }}"
                                                          onsubmit="return confirm('Remove all {{ count($group['ids']) }} days of this holiday period?')">
                                                        @csrf @method('DELETE')
                                                        <input type="hidden" name="ids" value="{{ implode(',', $group['ids']) }}">
                                                        <button class="btn btn-sm btn-outline-danger py-0 px-2" title="Remove entire period">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                    @else
                                                    <form method="POST" action="{{ route('holidays.destroy', $group['ids'][0]) }}"
                                                          onsubmit="return confirm('Remove this holiday?')">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-sm btn-outline-danger py-0 px-2" title="Remove">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                    @endif
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
