@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h1 class="display-6">
                            <i class="bi bi-person-badge"></i> {{ $admission->student_name }}
                        </h1>
                        <div>
                            @if($admission->status == 'inquiry')
                                <span class="badge bg-secondary fs-6">Inquiry</span>
                            @elseif($admission->status == 'pending')
                                <span class="badge bg-warning text-dark fs-6">Pending</span>
                            @elseif($admission->status == 'confirmed')
                                <span class="badge bg-success fs-6">Confirmed</span>
                            @endif
                        </div>
                    </div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{route('admissions.index')}}">Admissions</a></li>
                            <li class="breadcrumb-item active">{{ $admission->student_name }}</li>
                        </ol>
                    </nav>
                    @include('session-messages')

                    {{-- STATUS ACTION BUTTONS --}}
                    <div class="bg-white border shadow-sm p-3 mb-4">
                        <h6 class="mb-3">Actions</h6>
                        <div class="d-flex flex-wrap gap-2">

                            {{-- Edit always available if not confirmed --}}
                            @if($admission->status != 'confirmed')
                                <a href="{{ route('admissions.edit', $admission->id) }}" class="btn btn-outline-primary">
                                    <i class="bi bi-pen"></i> Edit Details
                                </a>
                            @endif

                            {{-- Inquiry → Pending --}}
                            @if($admission->status == 'inquiry')
                                <form method="POST" action="{{ route('admissions.markPending', $admission->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-warning">
                                        <i class="bi bi-clock"></i> Mark as Pending
                                    </button>
                                </form>
                            @endif

                            {{-- Pending or Inquiry → Confirm --}}
                            @if(in_array($admission->status, ['inquiry', 'pending']))
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#confirmModal">
                                    <i class="bi bi-check-circle"></i> Confirm Admission
                                </button>
                            @endif

                            {{-- Cancel --}}
                            @if($admission->status != 'confirmed')
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                    <i class="bi bi-x-circle"></i> Cancel Admission
                                </button>
                            @endif

                            {{-- Admission number --}}
                            @if($admission->dga_admission_no)
                                <span class="badge bg-info text-dark fs-6 p-2">
                                    <i class="bi bi-hash"></i> {{ $admission->dga_admission_no }}
                                </span>
                            @elseif($admission->general_id)
                                <span class="badge bg-info text-dark fs-6 p-2">
                                    <i class="bi bi-hash"></i> General ID: {{ $admission->general_id }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="row g-4">
                        {{-- LEFT COLUMN --}}
                        <div class="col-md-8">

                            {{-- Student Info --}}
                            <div class="bg-white border shadow-sm p-4 mb-4">
                                <h5 class="border-bottom pb-2"><i class="bi bi-person"></i> Student Information</h5>
                                <div class="row g-2 mt-2">
                                    <div class="col-md-6"><strong>Name:</strong> {{ $admission->student_name }}</div>
                                    <div class="col-md-6"><strong>Date of Birth:</strong> {{ $admission->date_of_birth ? $admission->date_of_birth->format('d M Y') : '-' }}</div>
                                    <div class="col-md-6"><strong>Gender:</strong> {{ ucfirst($admission->gender ?? '-') }}</div>
                                    <div class="col-md-6"><strong>Caste:</strong> {{ $admission->caste ?? '-' }}</div>
                                    <div class="col-md-6"><strong>Religion:</strong> {{ $admission->religion ?? '-' }}</div>
                                    <div class="col-md-6"><strong>Nationality:</strong> {{ $admission->nationality ?? '-' }}</div>
                                    <div class="col-md-6"><strong>Place of Birth:</strong> {{ $admission->place_of_birth ?? '-' }}</div>
                                    <div class="col-md-6"><strong>Language at Home:</strong> {{ $admission->language_spoken_at_home ?? '-' }}</div>
                                    <div class="col-md-6"><strong>Previous School:</strong> {{ $admission->previous_school ?? '-' }}</div>
                                    <div class="col-md-6"><strong>Class:</strong> {{ $admission->schoolClass->class_name ?? '-' }}</div>
                                    <div class="col-md-6"><strong>Section:</strong> {{ $admission->section->section_name ?? 'Not assigned' }}</div>
                                    <div class="col-md-6"><strong>Academic Year:</strong> {{ $admission->academic_year }}</div>
                                    <div class="col-md-6"><strong>Fee Category:</strong>
                                        @if($admission->fee_category)
                                            <span class="badge bg-primary">{{ strtoupper($admission->fee_category) }}</span>
                                        @else
                                            <span class="text-muted">Not set</span>
                                        @endif
                                    </div>
                                    <div class="col-md-6"><strong>Inquiry Date:</strong> {{ $admission->inquiry_date ? $admission->inquiry_date->format('d M Y') : '-' }}</div>
                                    @if($admission->confirmed_date)
                                    <div class="col-md-6"><strong>Confirmed Date:</strong> {{ $admission->confirmed_date->format('d M Y') }}</div>
                                    @endif
                                </div>
                            </div>

                            {{-- Family Info --}}
                            <div class="bg-white border shadow-sm p-4 mb-4">
                                <h5 class="border-bottom pb-2"><i class="bi bi-people"></i> Family Information</h5>
                                <div class="row g-2 mt-2">
                                    <div class="col-md-6"><strong>Father's Name:</strong> {{ $admission->father_name ?? '-' }}</div>
                                    <div class="col-md-6"><strong>Father's Occupation:</strong> {{ $admission->father_occupation ?? '-' }}</div>
                                    <div class="col-md-6"><strong>Mother's Name:</strong> {{ $admission->mother_name ?? '-' }}</div>
                                    <div class="col-md-6"><strong>Mother's Occupation:</strong> {{ $admission->mother_occupation ?? '-' }}</div>
                                    <div class="col-md-6"><strong>Sibling:</strong> {{ $admission->sibling_name_age ?? '-' }}</div>
                                    <div class="col-md-6"><strong>Guardian:</strong> {{ $admission->guardian_name ?? '-' }}</div>
                                    <div class="col-md-6"><strong>Guardian Occupation:</strong> {{ $admission->guardian_occupation ?? '-' }}</div>
                                    <div class="col-md-12"><strong>Guardian Address:</strong> {{ $admission->guardian_address ?? '-' }}</div>
                                </div>
                            </div>

                            {{-- Contact & Address --}}
                            <div class="bg-white border shadow-sm p-4 mb-4">
                                <h5 class="border-bottom pb-2"><i class="bi bi-geo-alt"></i> Address &amp; Contact</h5>
                                <div class="row g-2 mt-2">
                                    <div class="col-md-12"><strong>Address:</strong> {{ $admission->full_address ?? '-' }}</div>
                                    <div class="col-md-4"><strong>Village:</strong> {{ $admission->village ?? '-' }}</div>
                                    <div class="col-md-4"><strong>Distance:</strong> {{ $admission->distance_from_school ?? '-' }}</div>
                                    <div class="col-md-4"><strong>Father's Phone:</strong> {{ $admission->father_phone ?? $admission->contact_mobile ?? '-' }}</div>
                                    <div class="col-md-4"><strong>Mother's Phone:</strong> {{ $admission->mother_phone ?? '-' }}</div>
                                    <div class="col-md-4"><strong>Emergency:</strong> {{ $admission->contact_emergency ?? '-' }}</div>
                                    <div class="col-md-4"><strong>City:</strong> {{ $admission->city ?? '-' }}</div>
                                    <div class="col-md-4"><strong>PIN Code:</strong> {{ $admission->zip ?? '-' }}</div>
                                </div>
                            </div>

                            {{-- Medical --}}
                            <div class="bg-white border shadow-sm p-4 mb-4">
                                <h5 class="border-bottom pb-2"><i class="bi bi-heart-pulse"></i> Transport &amp; Medical</h5>
                                <div class="row g-2 mt-2">
                                    <div class="col-md-4"><strong>Transport:</strong> {{ $admission->transport_required ? 'Yes' : 'No' }}</div>
                                    <div class="col-md-8"><strong>Allergies/Medical:</strong> {{ $admission->allergies_medical ?? '-' }}</div>
                                    <div class="col-md-6"><strong>Doctor:</strong> {{ $admission->doctor_name_phone ?? '-' }}</div>
                                    <div class="col-md-6"><strong>Blood Group:</strong> {{ $admission->blood_type ?? '-' }}</div>
                                </div>
                            </div>

                        </div>

                        {{-- RIGHT COLUMN — Document Checklist --}}
                        <div class="col-md-4">
                            <div class="bg-white border shadow-sm p-4 mb-4">
                                <h5 class="border-bottom pb-2">
                                    <i class="bi bi-folder-check"></i> Documents
                                    @if($admission->hasIncompleteDocuments())
                                        <span class="badge bg-warning text-dark ms-2">Incomplete</span>
                                    @else
                                        <span class="badge bg-success ms-2">Complete</span>
                                    @endif
                                </h5>
                                @foreach($admission->documents as $doc)
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <span class="small">{{ $doc_labels[$doc->document_type] ?? $doc->document_type }}</span>
                                    <form method="POST" action="{{ route('admissions.updateDocument', [$admission->id, $doc->id]) }}">
                                        @csrf
                                        <select name="status" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                                            <option value="received" {{ $doc->status == 'received' ? 'selected' : '' }}>✅ Received</option>
                                            <option value="pending"  {{ $doc->status == 'pending'  ? 'selected' : '' }}>⏳ Pending</option>
                                            <option value="na"       {{ $doc->status == 'na'       ? 'selected' : '' }}>N/A</option>
                                        </select>
                                    </form>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            @include('layouts.footer')
        </div>
    </div>
</div>

{{-- CONFIRM MODAL --}}
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-check-circle"></i> Confirm Admission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admissions.confirm', $admission->id) }}">
                @csrf
                <div class="modal-body">
                    <p>You are confirming admission for <strong>{{ $admission->student_name }}</strong>. This will create a student account.</p>

                    <div class="mb-3">
                        <label class="form-label">Fee Category <span class="text-danger">*</span></label>
                        <select name="fee_category" class="form-select" id="feeCategorySelect" required>
                            <option value="">Select category</option>
                            <option value="general">General</option>
                            <option value="rte">RTE (₹0 tuition)</option>
                            <option value="coc">COC (DGA Internal)</option>
                            <option value="discount">Discount</option>
                        </select>
                    </div>

                    <div class="mb-3" id="discountField" style="display:none;">
                        <label class="form-label">Discounted Amount (₹)</label>
                        <input type="number" name="discounted_amount" class="form-control" step="0.01">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Assign Section <span class="text-danger">*</span></label>
                        <select name="section_id" class="form-select" required>
                            <option value="">Select section</option>
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->section_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- General ID for Class 1+ --}}
                    @php
                        $prePrimary = ['Nursery', 'Lower KG', 'Upper KG'];
                        $isPrePrimary = in_array($admission->schoolClass->class_name ?? '', $prePrimary);
                    @endphp
                    @if(!$isPrePrimary)
                    <div class="mb-3">
                        <label class="form-label">General Register ID (from ZP Portal)</label>
                        <input type="text" name="general_id" class="form-control" placeholder="11-digit ID">
                    </div>
                    @endif

                    @if($admission->hasIncompleteDocuments())
                    <div class="alert alert-warning mb-3">
                        <i class="bi bi-exclamation-triangle"></i> Some documents are still pending. You can still confirm but please collect them soon.
                    </div>
                    @endif

                    {{-- First Payment (optional) --}}
                    <div id="firstPaymentSection">
                        <hr>
                        <h6 class="text-muted mb-3"><i class="bi bi-cash-coin me-1"></i> First Payment <span class="badge bg-secondary fw-normal">Optional</span></h6>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small">Payment Date</label>
                                <input type="date" name="payment_date" class="form-control form-control-sm" value="{{ now()->toDateString() }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Amount (₹)</label>
                                <input type="number" name="amount_paid" class="form-control form-control-sm" step="0.01" min="0" placeholder="Leave blank to skip">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small">Payment Mode</label>
                                <select name="payment_mode" class="form-select form-select-sm" id="paymentModeSelect">
                                    <option value="cash">Cash</option>
                                    <option value="qr">QR / UPI</option>
                                    <option value="cheque">Cheque</option>
                                </select>
                            </div>
                        </div>
                        <div id="chequeFields" style="display:none;" class="row g-2 mt-1">
                            <div class="col-md-4">
                                <label class="form-label small">Cheque No</label>
                                <input type="text" name="cheque_no" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Cheque Date</label>
                                <input type="date" name="cheque_date" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small">Bank Name</label>
                                <input type="text" name="bank_name" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div id="qrFields" style="display:none;" class="mt-1">
                            <label class="form-label small">Transaction Reference</label>
                            <input type="text" name="transaction_ref" class="form-control form-control-sm" placeholder="UPI transaction ID">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Confirm Admission</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- CANCEL MODAL --}}
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-x-circle"></i> Cancel Admission</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admissions.cancel', $admission->id) }}">
                @csrf
                <div class="modal-body">
                    <p>Are you sure you want to cancel the admission for <strong>{{ $admission->student_name }}</strong>? This will archive the record.</p>
                    <div class="mb-3">
                        <label class="form-label">Reason for Cancellation <span class="text-danger">*</span></label>
                        <textarea name="cancel_reason" class="form-control" rows="3" required placeholder="Please provide a reason..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Go Back</button>
                    <button type="submit" class="btn btn-danger">Yes, Cancel Admission</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('feeCategorySelect').addEventListener('change', function() {
    document.getElementById('discountField').style.display = this.value === 'discount' ? 'block' : 'none';
    document.getElementById('firstPaymentSection').style.display = this.value === 'rte' ? 'none' : 'block';
});
document.getElementById('paymentModeSelect').addEventListener('change', function() {
    document.getElementById('chequeFields').style.display = this.value === 'cheque' ? 'flex' : 'none';
    document.getElementById('qrFields').style.display    = this.value === 'qr'     ? 'block' : 'none';
});
</script>
@endsection
