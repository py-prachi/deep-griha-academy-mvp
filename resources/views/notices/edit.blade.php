@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-start">

        @include('layouts.left-menu')

        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-10 col-xl-10 col-xxl-10">

            <div class="row pt-2">
                <div class="col ps-4">

                    <h1 class="display-6 mb-3">
                        <i class="bi bi-megaphone"></i> Edit Notice
                    </h1>

                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home') }}">Home</a>
                            </li>
                            <li class="breadcrumb-item active">
                                Edit Notice
                            </li>
                        </ol>
                    </nav>

                    @include('session-messages')

                    <div class="row">

                        <form action="{{ route('notices.update', $notice->id) }}"
                              method="POST">

                            @csrf
                            @method('PUT')

                            <!-- Title -->
                            <div class="mb-3">
                                <label class="form-label">Title</label>

                                <input type="text"
                                       name="title"
                                       value="{{ old('title', $notice->title) }}"
                                       class="form-control"
                                       >
                            </div>


                            <!-- Description (CKEditor) -->
                            <div class="mb-3">
                                <label class="form-label">Description</label>

                                <textarea name="description"
                                          id="editor"
                                          class="form-control"
                                          rows="5"
                                          >{{ old('description', $notice->description) }}</textarea>
                            </div>


                            <!-- Class Dropdown -->
                            <div class="mb-3">
                                <label class="form-label">Publish For</label>

                                <select name="class_id" class="form-select">

                                    <option value="">
                                        All Classes (Everyone)
                                    </option>

                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}"
                                            {{ $notice->class_id == $class->id ? 'selected' : '' }}>
                                            {{ $class->class_name }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>


                            <!-- Save Button -->
                            <button type="submit"
                                    class="btn btn-outline-primary">

                                <i class="bi bi-check2"></i> Update Notice
                            </button>

                        </form>

                    </div>
                </div>
            </div>

            @include('layouts.footer')

        </div>
    </div>
</div>


{{-- CKEditor --}}
<script>
    if (typeof CKEDITOR !== 'undefined') {
        CKEDITOR.replace('editor');
    }
</script>

<script>
    if (typeof CKEDITOR !== 'undefined') {
        CKEDITOR.replace('editor');
    }
</script>


@endsection
