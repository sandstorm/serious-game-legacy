<?php

arch()
    ->note('do not use any legacy functions / functions forbidden in prod')
    ->preset()->php();

arch()
    ->note('no dangerous method calls')
    ->preset()->security()
    ->ignoring(\RalphJSmit\Filament\RecordFinder\Serialize\Concerns\HasUnserialization::class);


arch()
    ->note('laravel good practices (let us see how helpful this is, or if we should deactivate it again!)')
    ->preset()->laravel()
    ->ignoring([
        \App\Providers\Filament\AdminPanelProvider::class,
        \App\Providers\AppServiceProvider::class,
    ]);


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
