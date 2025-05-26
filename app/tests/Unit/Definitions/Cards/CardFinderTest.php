<?php

namespace Tests\Definitions\Cards;

use Domain\Definitions\Cards\CardFinder;
use Domain\Definitions\Cards\ValueObject\CardId;

beforeEach(function () {

});

describe('getCardById', function () {

    it('returns the correct card', function () {
        $cardId = new CardId('buk1');
        $actualCard = CardFinder::getCardById($cardId);
        expect($actualCard->id)->toEqual($cardId);
    });

    it('throws exception when the card does not exist', function () {
        $cardId = new CardId('doesnotexist');
        $actualCard = CardFinder::getCardById($cardId);
    })->throws(\RuntimeException::class, 'Card [CardId: doesnotexist] does not exist', 1747645954);

});
