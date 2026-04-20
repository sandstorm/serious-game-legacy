@use('Domain\CoreGameLogic\Feature\Spielzug\State\TransactionHistoryState')

@props([
    'gameEvents' => null,
    'playerId' => null,
])

@php
    $transactions = TransactionHistoryState::getTransactionHistoryForPlayer($gameEvents, $playerId);
@endphp

<div class="transaction-history">
    <h4 class="transaction-history__title">Transaktionshistorie</h4>
    @if (count($transactions) === 0)
        <p class="transaction-history__empty">Noch keine Transaktionen</p>
    @else
        <div class="transaction-history__table-wrapper">
            <table class="transaction-history__table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Zug</th>
                        <th>Anlageart</th>
                        <th>Typ</th>
                        <th>Menge</th>
                        <th>Kurs</th>
                        <th>Besitz danach</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transactions as $transaction)
                        <tr>
                            <td><i class="{{ $transaction->iconClass }}" aria-hidden="true"></i></td>
                            <td>{{ $transaction->playerTurn->value }}</td>
                            <td>{{ $transaction->assetName }}</td>
                            <td>{{ $transaction->type }}</td>
                            <td>{{ $transaction->amount }}</td>
                            <td><x-money-amount :value="$transaction->price" /></td>
                            <td>{{ $transaction->holdingAfter }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
