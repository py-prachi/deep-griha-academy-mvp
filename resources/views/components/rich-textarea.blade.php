@props(['name', 'value' => '', 'toolbar' => true, 'minHeight' => 140, 'required' => false, 'placeholder' => ''])
<div class="rich-textarea" data-toolbar="{{ $toolbar ? '1' : '0' }}" data-min-height="{{ $minHeight }}" data-placeholder="{{ $placeholder }}">
    <div class="rich-textarea-editor {{ $errors->has($name) ? 'is-invalid' : '' }}"></div>
    <textarea name="{{ $name }}" class="d-none rich-textarea-source" {{ $required ? 'required' : '' }}>{{ $value }}</textarea>
    @error($name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>
