@props([
    'report',
])

@php
    use Domain\CoreGameLogic\Feature\GameReport\Dto\MoneyCategory;

    /** @var \Domain\CoreGameLogic\Feature\GameReport\Dto\GameReport $report */
    $groups = [
        'Einnahmen' => [
            MoneyCategory::STARTKAPITAL,
            MoneyCategory::GEHALT,
            MoneyCategory::KAPITALERTRAEGE,
            MoneyCategory::MINIJOBS,
            MoneyCategory::VERKAUF_VON_ANLAGEN,
            MoneyCategory::KREDITE,
        ],
        'Ausgaben' => [
            MoneyCategory::STEUERN,
            MoneyCategory::LEBENSHALTUNG,
            MoneyCategory::LEBENSZIEL,
            MoneyCategory::BILDUNG_UND_KARRIERE,
            MoneyCategory::FREIZEIT_UND_SOZIALES,
            MoneyCategory::KAUF_VON_ANLAGEN,
            MoneyCategory::KREDITTILGUNG,
            MoneyCategory::VERSICHERUNGEN,
            MoneyCategory::STRAFEN,
            MoneyCategory::INSOLVENZ,
        ],
        'Sonstiges' => [
            MoneyCategory::EREIGNISSE,
            MoneyCategory::KONJUNKTUR,
            MoneyCategory::JAHRESABRECHNUNG,
            MoneyCategory::SONSTIGES,
        ],
    ];
    // only show categories somebody actually had money in
    $isUsed = function (MoneyCategory $category) use ($report): bool {
        foreach ($report->players as $player) {
            if (abs($player->breakdown->get($category)->value) >= 0.005) {
                return true;
            }
        }
        return false;
    };
@endphp

<div class="game-report__table-wrapper">
    <table class="game-report__money-table">
        <thead>
            <tr>
                <th scope="col"><span class="sr-only">Kategorie</span></th>
                @foreach ($report->players as $player)
                    <th scope="col" class="{{ $player->colorClass }}"><span class="game-report__dot"></span>{{ $player->name }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($groups as $groupName => $categories)
                @php $usedCategories = array_filter($categories, $isUsed); @endphp
                @if (count($usedCategories) > 0)
                    <tr class="game-report__money-group">
                        <th scope="rowgroup" colspan="{{ count($report->players) + 1 }}">{{ $groupName }}</th>
                    </tr>
                    @foreach ($usedCategories as $category)
                        <tr>
                            <th scope="row">{{ $category->value }}</th>
                            @foreach ($report->players as $player)
                                <td><x-money-amount :value="$player->breakdown->get($category)" /></td>
                            @endforeach
                        </tr>
                    @endforeach
                @endif
            @endforeach
        </tbody>
        <tfoot>
            <tr class="game-report__money-total">
                <th scope="row">Guthaben am Ende</th>
                @foreach ($report->players as $player)
                    <td><x-money-amount :value="$player->finalGuthaben" /></td>
                @endforeach
            </tr>
        </tfoot>
    </table>
</div>

<div class="game-report__top-expenses">
    @foreach ($report->players as $player)
        <div class="game-report__top-expenses-player {{ $player->colorClass }}">
            <h4><span class="game-report__dot"></span>Teuerste Karten von {{ $player->name }}</h4>
            @if (count($player->topCardExpenses) === 0)
                <p>Keine Karte hat Geld gekostet.</p>
            @else
                <ol>
                    @foreach ($player->topCardExpenses as $expense)
                        <li>{{ $expense->title }} <small>(Jahr {{ $expense->year->value }})</small> <x-money-amount :value="$expense->amount" /></li>
                    @endforeach
                </ol>
            @endif
        </div>
    @endforeach
</div>
