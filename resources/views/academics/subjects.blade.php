@extends('layouts.app')
@section('title', 'Subjects')
@section('content')
<div class="container">
    <div class="row justify-content-start">
        @include('layouts.left-menu')
        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">
            <div class="row pt-2">
                <div class="col ps-4">
                    <div class="d-flex align-items-center mb-3">
                        <a href="{{ url('academics/settings') }}" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i>
                        </a>
                        <h4 class="mb-0"><i class="bi bi-book me-1"></i> Subjects</h4>
                    </div>

                    @include('session-messages')

                    <div class="row g-4">
                        {{-- Left: subject list --}}
                        <div class="col-md-5">
                            <div class="card">
                                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                                    <strong>All Subjects</strong>
                                    <span class="badge bg-secondary">{{ $subjects->count() }}</span>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-bordered table-sm mb-0">
                                        <thead class="table-secondary">
                                            <tr>
                                                <th>Subject</th>
                                                <th>Code</th>
                                                <th class="text-center">Active</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($subjects as $subject)
                                            <tr>
                                                <td>
                                                    <button class="btn btn-link btn-sm p-0 text-start text-decoration-none"
                                                        onclick="openEdit({{ $subject->id }}, '{{ addslashes($subject->name) }}', '{{ $subject->code }}', {{ $subject->is_active ? 1 : 0 }})">
                                                        {{ $subject->name }}
                                                    </button>
                                                </td>
                                                <td><small class="text-muted">{{ $subject->code ?? '—' }}</small></td>
                                                <td class="text-center">
                                                    @if($subject->is_active)
                                                        <i class="bi bi-check-circle-fill text-success"></i>
                                                    @else
                                                        <i class="bi bi-x-circle text-muted"></i>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <form method="POST" action="{{ route('subjects.destroy', $subject) }}"
                                                        onsubmit="return confirm('Remove {{ $subject->name }}?')">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-sm btn-outline-danger py-0">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="4" class="text-center text-muted py-3">No subjects yet.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Add subject --}}
                            <div class="card mt-3">
                                <div class="card-header"><strong>Add Subject</strong></div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('subjects.store') }}">
                                        @csrf
                                        <div class="row g-2">
                                            <div class="col-7">
                                                <input type="text" name="name" class="form-control form-control-sm" placeholder="Subject name e.g. Marathi" required>
                                            </div>
                                            <div class="col-3">
                                                <input type="text" name="code" class="form-control form-control-sm" placeholder="Code e.g. MAR" maxlength="10">
                                            </div>
                                            <div class="col-2">
                                                <button class="btn btn-sm btn-success w-100"><i class="bi bi-plus"></i> Add</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Right: class assignments per subject --}}
                        <div class="col-md-7">
                            <div class="card">
                                <div class="card-header bg-dark text-white">
                                    <strong>Assign Subjects to Classes — {{ $session->session_name }}</strong>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small mb-3">For each subject, select which classes it is taught in. Changes are saved per subject.</p>
                                    @forelse($subjects->where('is_active', true) as $subject)
                                    <form method="POST" action="{{ route('subjects.saveClassSubjects') }}" class="mb-3">
                                        @csrf
                                        <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                                        <input type="hidden" name="session_id" value="{{ $sessionId }}">
                                        <div class="d-flex align-items-start gap-2">
                                            <div style="min-width:110px">
                                                <span class="badge bg-primary py-2 px-2">{{ $subject->name }}</span>
                                            </div>
                                            <div class="d-flex flex-wrap gap-2 flex-grow-1">
                                                @foreach($schoolClasses as $class)
                                                @php $assigned = in_array($class->id, $classSubjectMap[$subject->id] ?? []); @endphp
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="class_ids[]"
                                                        value="{{ $class->id }}"
                                                        id="cs_{{ $subject->id }}_{{ $class->id }}"
                                                        {{ $assigned ? 'checked' : '' }}>
                                                    <label class="form-check-label small" for="cs_{{ $subject->id }}_{{ $class->id }}">
                                                        {{ $class->class_name }}
                                                    </label>
                                                </div>
                                                @endforeach
                                            </div>
                                            <button class="btn btn-sm btn-outline-primary flex-shrink-0">Save</button>
                                        </div>
                                    </form>
                                    @empty
                                    <p class="text-muted">Add subjects first.</p>
                                    @endforelse
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

{{-- Edit subject modal --}}
<div class="modal fade" id="editSubjectModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Edit Subject</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editSubjectForm">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label form-label-sm">Name</label>
                        <input type="text" name="name" id="editName" class="form-control form-control-sm" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label form-label-sm">Code</label>
                        <input type="text" name="code" id="editCode" class="form-control form-control-sm" maxlength="10">
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" id="editActive" value="1">
                        <label class="form-check-label small" for="editActive">Active</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-sm btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
function openEdit(id, name, code, active) {
    document.getElementById('editSubjectForm').action = '/academics/subjects/' + id;
    document.getElementById('editName').value = name;
    document.getElementById('editCode').value = code || '';
    document.getElementById('editActive').checked = active == 1;
    new bootstrap.Modal(document.getElementById('editSubjectModal')).show();
}
</script>
@endsection
