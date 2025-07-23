@props([
    'change' => null,
    'iconClass' => null,
])

@if ($change < 0)
    <i class="text--danger icon-minus"></i>
    @for($i = $change; $i < 0; $i++)
        <i @class([$iconClass])></i>
    @endfor
@else
    <i class="text--success icon-plus"></i>
    @for($i = 0; $i < $change; $i++)
        <i @class([$iconClass])></i>
    @endfor
@endif


