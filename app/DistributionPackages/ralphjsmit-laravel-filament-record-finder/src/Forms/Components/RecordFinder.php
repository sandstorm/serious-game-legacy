<?php

namespace RalphJSmit\Filament\RecordFinder\Forms\Components;

use Filament\Forms;
use Filament\Support as Support;
use RalphJSmit\Filament\RecordFinder\Forms\Components\RecordFinder as Concerns;
use RalphJSmit\Filament\RecordFinder\Serialize;

class RecordFinder extends Forms\Components\Field implements Forms\Components\Contracts\HasAffixActions
{
    use Concerns\CanBeInline;
    use Concerns\CanFormatState;
    use Concerns\HasAffixes;
    use Concerns\HasModelLabel;
    use Concerns\HasOpenModalAction;
    use Concerns\HasRecordState;
    use Concerns\HasRelationship;
    use Concerns\HasTable;
    use Concerns\IsMultiple;
    use Concerns\IsReorderable;
    use Serialize\Concerns\HasSerialization;
    use Support\Concerns\HasPlaceholder;

    protected string $view = 'filament-record-finder::forms.components.record-finder';

    protected function setUp(): void
    {
        parent::setUp();

        $this->registerActions([
            fn (self $component): Forms\Components\Actions\Action => $component->getOpenModalAction(),
        ]);
    }
}
