<input
    class="form__textfield"
    name="{{ $name }}"
    type="{{ $type ?? 'text' }}"
    placeholder="{{ $placeholder ?? '' }}"
    value="{{ $value ?? old($name) }}"
    {{ $attributes }}
/>
