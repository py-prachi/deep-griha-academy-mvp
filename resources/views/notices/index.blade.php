@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">

        @include('layouts.left-menu')

        <div class="col-lg-10">

            <h2 class="mb-3">Notices</h2>

            <a href="{{ route('notices.create') }}"
               class="btn btn-primary mb-3">
               + Create Notice
            </a>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Class</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($notices as $notice)

                    <tr>
                        <td>{{ $notice->title ?? '-' }}</td>

                        <td>
                            {{ $notice->class_id ?? 'All' }}
                        </td>

                        <td>
                            {{ $notice->created_at->format('d M Y') }}
                        </td>

                        <td>

                            <!-- Edit -->
                            <a href="{{ route('notices.edit', $notice->id) }}"
                               class="btn btn-sm btn-warning">
                               Edit
                            </a>

                            <!-- Delete -->
                            <form action="{{ route('notices.destroy', $notice->id) }}"
                                  method="POST"
                                  style="display:inline">

                                @csrf
                                @method('DELETE')

                                <button class="btn btn-sm btn-danger"
                                  onclick="return confirm('Delete this notice?')">
                                  Delete
                                </button>

                            </form>

                        </td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="4" class="text-center">
                            No notices found
                        </td>
                    </tr>

                @endforelse
                </tbody>
            </table>

        </div>
    </div>
</div>
@endsection
