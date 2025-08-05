<input
    @class([
        "form__textfield form-group__input",
        $this->getPlayerColorClass(),
    ])
    name="{{ $name }}"
    id="{{ $id }}"
    type="{{ $type ?? 'text' }}"
    placeholder="{{ $placeholder ?? '' }}"
    value="{{ $value ?? old($name) }}"
    {{ $attributes }}
/>
