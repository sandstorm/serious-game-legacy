@props([
    '$insurances' => null,
])

<h3>Versicherungen</h3>
<form>
    <div class="form__group">
        @foreach ($insurances as $insurance)
            <label>
                <input type="checkbox" name="insurances[]" value="{{ $insurance->id }}">
                {{ $insurance->type }} ({{ $insurance->annualCost->value }}€ /Jahr)
            </label>
        @endforeach
    </div>
</form>
