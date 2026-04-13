<table class="table table-sm mb-1" id="{{ $weekday === 0 ? 'period-setup-default' : '' }}">
    <thead class="table-light">
        <tr>
            <th style="width:28px;">#</th>
            <th>Label</th>
            <th style="width:90px;">Start</th>
            <th style="width:90px;">End</th>
            <th style="width:65px;">Break?</th>
            <th style="width:38px;"></th>
        </tr>
    </thead>
    <tbody>
        @foreach($periods as $i => $period)
        <tr>
            <td class="text-muted small align-middle">{{ $i + 1 }}</td>
            <td>
                <input type="text" class="form-control form-control-sm" value="{{ $period->label }}"
                    onchange="updatePeriod({{ $period->id }}, 'label', this.value)">
            </td>
            <td>
                <input type="time" class="form-control form-control-sm" value="{{ $period->start_time }}"
                    onchange="updatePeriod({{ $period->id }}, 'start_time', this.value)">
            </td>
            <td>
                <input type="time" class="form-control form-control-sm" value="{{ $period->end_time }}"
                    onchange="updatePeriod({{ $period->id }}, 'end_time', this.value)">
            </td>
            <td class="text-center align-middle">
                <input type="checkbox" class="form-check-input" {{ $period->is_break ? 'checked' : '' }}
                    onchange="updatePeriod({{ $period->id }}, 'is_break', this.checked ? 1 : 0)">
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1"
                    onclick="deletePeriod({{ $period->id }}, this)" title="Delete">
                    <i class="bi bi-trash" style="font-size:0.8rem;"></i>
                </button>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
