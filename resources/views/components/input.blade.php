@props(['name', 'label', 'type' => 'text', 'value' => null])

<div class="mb-4">
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    <input type="{{ $type }}"
           id="{{ $name }}"
           name="{{ $name }}"
           value="{{ $type === 'password' ? '' : old($name, $value) }}"
           {{ $attributes->class(['form-input', 'form-input-error' => $errors->has($name)]) }}>
    @error($name)
        <p class="form-error">{{ $message }}</p>
    @enderror
</div>