@props(['value', 'withIcon' => false])
<?php
if ($withIcon) {
    $mathSignIcon = match (true) {
        $value->value < 0 => "<i aria-hidden='true' class='text--danger icon-minus'></i><span class='sr-only'>-</span>",
        $value->value > 0 => "<i aria-hidden='true' class='text--success icon-plus'></i><span class='sr-only'>+</span>",
        default => ''
    };
    $valueNormalized = number_format(abs($value->value), 2, ',', '.');
    echo "<span class='text--currency'>" . $mathSignIcon . " " . $valueNormalized .
        " <i aria-hidden='true' class='icon-euro'></i><span class='sr-only'>€</span></span>";
} else {
    $formatted = number_format($value->value, 2, ',', '.');
    echo "<span class='text--currency'>" . $formatted . " €</span>";
}
?>