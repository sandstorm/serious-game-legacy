<?php

arch()
    ->note('do not use any legacy functions / functions forbidden in prod')
    ->preset()->php();

arch()
    ->note('We always want declare(strict_types=1) in ALL files')
    ->expect('App')
    ->toUseStrictTypes();

arch()->expect('App')
    ->toUseStrictEquality();

arch()
    ->note('We always want declare(strict_types=1) in ALL files')
    ->expect('Domain')
    ->toUseStrictTypes();

arch()->expect('Domain')
    ->toUseStrictEquality();
