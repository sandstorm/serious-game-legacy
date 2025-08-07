@extends ('components.modal.modal')

@use('\Domain\Definitions\Konjunkturphase\ValueObject\CategoryId')

@props([
    'ereignisCard' => null,
])

@section('icon')
    <i class="icon-ereignis text--danger" aria-hidden="true"></i>
@endsection

@section('title')
    <div class="card__actions-header">
        <div>
            {{ $ereignisCard->getTitle() }}
        </div>
        <div class="card__actions-header-category">
            @if ($ereignisCard->getCategory() === CategoryId::SOZIALES_UND_FREIZEIT->value)
                <i class="icon-freizeit-und-soziales"></i>
            @endif
            @if ($ereignisCard->getCategory() === CategoryId::BILDUNG_UND_KARRIERE->value)
                <i class="icon-bildung-und-karriere"></i>
            @endif
            {{ $ereignisCard->getCategory() }}
        </div>
    </div>
@endsection

@section('content')
    <p>
        {{ $ereignisCard->getDescription() }}
    </p>
@endsection

@section('footer')
    <div class="card__actions-footer">
        <x-gameboard.resourceChanges.resource-changes style-class="horizontal" :resource-changes="$ereignisCard->getResourceChanges()" />
        <button type="button" class="button button--type-primary" wire:click="closeEreignisCard()">Akzeptieren</button>
    </div>
@endsection
