@php
    $gradeOptions = [
        'Nursery', 'Lower KG', 'Upper KG',
        'Class 1', 'Class 2', 'Class 3', 'Class 4',
        'Class 5', 'Class 6', 'Class 7', 'Class 8',
        'Other',
    ];
@endphp
<option value="">Select grade</option>
@foreach($gradeOptions as $gradeOption)
    <option value="{{ $gradeOption }}">{{ $gradeOption }}</option>
@endforeach
