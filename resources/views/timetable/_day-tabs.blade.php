@php
    $dayShort = [1=>'Mon',2=>'Tue',3=>'Wed',4=>'Thu',5=>'Fri',6=>'Sat'];
    $activeDay = isset($activeDay) ? $activeDay : 1;
    // If today is Sunday (7) or beyond Saturday, default to Monday
    if ($activeDay > 6) $activeDay = 1;
@endphp

<ul class="nav nav-tabs mb-0" role="tablist">
    @foreach($days as $dayNum => $dayName)
    <li class="nav-item" role="presentation">
        <button class="nav-link {{ $dayNum == $activeDay ? 'active' : '' }}"
            data-bs-toggle="tab" data-bs-target="#vday-{{ $dayNum }}"
            type="button" role="tab">
            {{ $dayShort[$dayNum] ?? $dayName }}
            @if($dayNum == $activeDay)
                <span class="badge bg-success ms-1" style="font-size:0.6rem;">Today</span>
            @endif
        </button>
    </li>
    @endforeach
</ul>

<div class="tab-content border border-top-0 bg-white shadow-sm mb-3">
    @foreach($days as $dayNum => $dayName)
    @php
        $dayPeriods = isset($periodsByDay[$dayNum]) ? $periodsByDay[$dayNum] : collect();
        $dayGrid    = isset($grid[$dayNum]) ? $grid[$dayNum] : [];
    @endphp
    <div class="tab-pane fade {{ $dayNum == $activeDay ? 'show active' : '' }}" id="vday-{{ $dayNum }}" role="tabpanel">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width:160px;">Period</th>
                    <th>Subject</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dayPeriods as $period)
                @php $routine = isset($dayGrid[$period->id]) ? $dayGrid[$period->id] : null; @endphp
                <tr class="{{ $period->is_break ? 'table-light' : '' }}">
                    <td class="small text-nowrap">
                        <span class="fw-semibold">{{ $period->label }}</span>
                        <span class="text-muted d-block" style="font-size:0.72rem;">{{ $period->start_time }}–{{ $period->end_time }}</span>
                    </td>
                    @if($period->is_break)
                        <td class="text-muted small fst-italic">{{ $period->label }}</td>
                    @else
                        <td class="fw-semibold small">
                            {{ $routine ? optional(optional($routine->course)->subject)->name : '—' }}
                        </td>
                    @endif
                </tr>
                @empty
                <tr><td colspan="2" class="text-muted small p-3">No timetable set up for this day yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endforeach
</div>
