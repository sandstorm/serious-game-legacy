<?php

namespace Archilex\AdvancedTables\Filters;

class SelectFilter extends TextFilter
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->multiple();

        $this->asSelect();
    }
}
