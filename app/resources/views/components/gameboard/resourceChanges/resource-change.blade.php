@props([
    'srLabel' => null,
    'change' => null,
    'iconClass' => null,
])

<div class="resource-change">
    @if ($change < 0)
        <i class="text--danger icon-minus" aria-hidden="true"></i>
        @for($i = $change; $i < 0; $i++)
            <i @class([$iconClass]) aria-hidden="true"></i>
        @endfor
    @else
        <i class="text--success icon-plus" aria-hidden="true"></i>
        @for($i = 0; $i < $change; $i++)
            <i @class([$iconClass]) aria-hidden="true"></i>
        @endfor
    @endif
    <span class="sr-only">{{ $change }} {{ $srLabel }} </span>
</div>


