<button
    type="submit"
    @class([
        "button",
        "button--type-primary",
        $this->getPlayerColorClass(),
    ])
    {{ $attributes }}
>
    {{ $slot }}
</button>
