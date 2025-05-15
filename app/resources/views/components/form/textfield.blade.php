<input
    class="form__textfield"
    name="{{ $name }}"
    id="{{ $id }}"
    type="{{ $type ?? 'text' }}"
    placeholder="{{ $placeholder ?? '' }}"
    value="{{ $value ?? old($name) }}"
    {{ $attributes }}
/>
