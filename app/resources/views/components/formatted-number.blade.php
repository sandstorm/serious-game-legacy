@props([
    'value' => 0,
    'suffix' => '',
])
{{-- number_format($value, 10, ',', '.') -> formats with German locale (comma decimal, period thousands) and enough precision --}}
{{-- rtrim(..., '0') -> strips trailing zeros (36,7500000000 → 36,75) --}}
{{-- rtrim(..., ',') -> strips trailing comma if all decimals were zero (35, → 35) --}}
@php
    $formatted = rtrim(rtrim(number_format(floatval($value), 10, ',', '.'), '0'), ',');
@endphp
{{ $formatted }}{{ $suffix }}
