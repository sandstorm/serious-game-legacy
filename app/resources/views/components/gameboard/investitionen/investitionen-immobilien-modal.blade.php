@extends ('components.modal.modal', ['closeModal' => "toggleImmobilienModal()"])

@use('Domain\CoreGameLogic\Feature\Konjunkturphase\State\ImmobilienPriceState')

@props([
    'immobilien' => [],
    'immobilienOwned' => [],
])

@section('icon')
    <x-gameboard.phase-icon />
@endsection

@section('title')
    @if ($this->buyImmobilieIsVisible)
        <span>
            Kauf - Immobilie <i class="icon-immobilien" aria-hidden="true"></i>
        </span>
    @elseif ($this->sellImmobilieIsVisible)
        <span>
            Verkauf - Immobilie <i class="icon-immobilien" aria-hidden="true"></i>
        </span>
    @else
        <span>
            Immobilien <i class="icon-immobilien" aria-hidden="true"></i>
        </span>
        <span class="font-size--base">
            Investitionen
        </span>
    @endif
@endsection

@section('content')
    @if ($this->buyImmobilieIsVisible)
        <div class="immoblien">
            @foreach($immobilien as $immobilie)
                <div @class(["card", "card--disabled" => !$this->canBuyImmobilie($immobilie->getId())->canExecute])>
                    <h4 class="card__title">{{ $immobilie->getTitle() }}</h4>
                    <div class="card__content card__content--center">
                        <div class="resource-changes">
                            <div class="resource-change">
                                {!! $immobilie->getAnnualRent()->formatWithIcon() !!}
                            </div>
                        </div>
                        <span class="font-size--sm">Jährliche Miete</span>
                        <x-gameboard.resourceChanges.resource-changes :resource-changes="$immobilie->getResourceChanges()" />
                        <span class="font-size--sm">Kaufpreis</span>

                        <button type="button"
                            @class([
                                "button",
                                "button--type-primary",
                                "button--disabled" => !$this->canBuyImmobilie($immobilie->getId())->canExecute,
                                $this->getPlayerColorClass()
                            ])
                            wire:click="buyImmobilie('{{ $immobilie->getId()->value }}')"
                        >
                            Kaufen
                            <div class="button__suffix">
                                <div>
                                    <i class="icon-minus text--danger" aria-hidden="true"></i><i class="icon-zeitstein" aria-hidden="true"></i>
                                    <span class="sr-only">, kostet einen Zeitstein</span>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @elseif ($this->sellImmobilieIsVisible)
        @if ($immobilienOwned)
            <table>
                <thead>
                <tr>
                    <th></th>
                    <th>Titel</th>
                    <th>Kaufpreis</th>
                    <th>Mietertrag</th>
                    <th>Verkaufspreis</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($immobilienOwned as $immobilie)
                    <tr>
                        <td><i class="icon-immobilien" aria-hidden="true"></i></td>
                        <td>{{ $immobilie->getTitle() }}</td>
                        <td>{!! $immobilie->getPurchasePrice()->format() !!}</td>
                        <td>{!! $immobilie->getAnnualRent()->format() !!}</td>
                        <td>{!! ImmobilienPriceState::getCurrentImmobiliePrice($gameEvents, $immobilie)->formatWithIcon() !!}</td>
                        <td>
                            <button
                                type="button"
                                @class([
                                    "button",
                                    "button--type-primary",
                                    "button--size-small",
                                    $this->getPlayerColorClass(),
                                    "button--disabled" => !$this->canSellImmobilie($immobilie->getId())->canExecute,
                                ])
                                wire:click="sellImmobilie('{{ $immobilie->getId()->value }}')"
                            >
                                Verkaufen
                                <div class="button__suffix">
                                    <div>
                                        <i class="icon-minus text--danger" aria-hidden="true"></i><i class="icon-zeitstein" aria-hidden="true"></i>
                                        <span class="sr-only">, kostet einen Zeitstein</span>
                                    </div>
                                </div>
                            </button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @else
            <h4>Keine Immobilien zum Verkauf vorhanden.</h4>
        @endif
    @else
        <p>
            Sachwerte wie Wohn- oder Gewerbegebäude, die in erster Linie
            Erträge durch Mieteinnahmen und potenziellen Wertzuwachs bieten. Sie gelten
            als vergleichsweise inflationsgeschützt, sind aber illiquide, kapitalintensiv und
            anfällig für Standort- sowie Zinsrisiken.
        </p>
        <div class="investitionen-types">
            <div class="investitionen-type">
                <div>
                    <h4>Immobilie</h4>
                </div>
                <div class="investitionen-type__actions">
                    <button
                        type="button"
                        @class([
                            "button",
                            "button--type-primary",
                            $this->getPlayerColorClass(),
                        ])
                        wire:click="showBuyImmobilie()"
                    >
                        kaufen
                    </button>
                    <button
                        type="button"
                        @class([
                            "button",
                            "button--type-outline-primary",
                            $this->getPlayerColorClass(),
                        ])
                        wire:click="showSellImmobilie()"
                    >
                        verkaufen
                    </button>
                </div>
            </div>
        </div>
    @endif
@endsection
