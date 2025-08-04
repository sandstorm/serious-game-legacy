<input
    @class([
        "form__textfield",
        $this->getPlayerColorClass(),
    ])
    name="{{ $name }}"
    id="{{ $id }}"
    type="{{ $type ?? 'text' }}"
    placeholder="{{ $placeholder ?? '' }}"
    value="{{ $value ?? old($name) }}"
    {{ $attributes }}
/>
