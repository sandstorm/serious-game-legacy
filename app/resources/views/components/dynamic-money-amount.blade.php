@props(['expression'])
<span class="text--currency">
    <span x-text="new Intl.NumberFormat('de-DE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format({!! $expression !!})"></span>
    <i aria-hidden="true" class="icon-euro"></i><span class="sr-only">€</span>
</span>
