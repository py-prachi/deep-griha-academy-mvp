@extends('layouts.app')
@section('title', 'Class Strength Report')
@section('content')
<div class="container-fluid">
    <div class="card mb-3">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Class Strength Report</h5>
            <a href="?pdf=1" class="btn btn-sm btn-light"><i class="fas fa-download me-1"></i> Download PDF</a>
        </div>
    </div>
    @php
        $grandTotal    = array_sum(array_column((array)$strength, 'total'));
        $grandGeneral  = array_sum(array_column((array)$strength, 'general'));
        $grandRte      = array_sum(array_column((array)$strength, 'rte'));
        $grandCoc      = array_sum(array_column((array)$strength, 'coc'));
        $grandDiscount = array_sum(array_column((array)$strength, 'discount'));
    @endphp
    <div class="row mb-3">
        <div class="col-md-2"><div class="card text-white bg-primary"><div class="card-body text-center py-2"><div class="small">Total Students</div><h4 class="mb-0">{{ $grandTotal }}</h4></div></div></div>
        <div class="col-md-2"><div class="card text-white bg-secondary"><div class="card-body text-center py-2"><div class="small">General</div><h4 class="mb-0">{{ $grandGeneral }}</h4></div></div></div>
        <div class="col-md-2"><div class="card text-white bg-info"><div class="card-body text-center py-2"><div class="small">RTE</div><h4 class="mb-0">{{ $grandRte }}</h4></div></div></div>
        <div class="col-md-2"><div class="card text-white bg-dark"><div class="card-body text-center py-2"><div class="small">COC</div><h4 class="mb-0">{{ $grandCoc }}</h4></div></div></div>
        <div class="col-md-2"><div class="card text-white bg-warning"><div class="card-body text-center py-2"><div class="small">Discount</div><h4 class="mb-0">{{ $grandDiscount }}</h4></div></div></div>
        <div class="col-md-2"><div class="card text-white bg-success"><div class="card-body text-center py-2"><div class="small">Classes</div><h4 class="mb-0">{{ count($strength) }}</h4></div></div></div>
    </div>
    <div class="card">
        <div class="card-body">
            @if(empty($strength))
                <p class="text-center text-muted py-4">No strength data found for current session.</p>
            @else
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr><th>Class</th><th>Division</th><th class="text-end">Total</th><th class="text-end">General</th><th class="text-end">RTE</th><th class="text-end">COC</th><th class="text-end">Discount</th></tr>
                    </thead>
                    <tbody>
                        @foreach($strength as $row)
                        <tr>
                            <td><strong>{{ $row->class_name }}</strong></td>
                            <td>{{ $row->section_name }}</td>
                            <td class="text-end fw-bold">{{ $row->total }}</td>
                            <td class="text-end">{{ $row->general }}</td>
                            <td class="text-end">{{ $row->rte }}</td>
                            <td class="text-end">{{ $row->coc }}</td>
                            <td class="text-end">{{ $row->discount }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-dark fw-bold">
                            <td colspan="2" class="text-end">Grand Total</td>
                            <td class="text-end">{{ $grandTotal }}</td>
                            <td class="text-end">{{ $grandGeneral }}</td>
                            <td class="text-end">{{ $grandRte }}</td>
                            <td class="text-end">{{ $grandCoc }}</td>
                            <td class="text-end">{{ $grandDiscount }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
