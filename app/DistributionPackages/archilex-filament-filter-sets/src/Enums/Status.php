<?php

namespace Archilex\AdvancedTables\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Status: string implements HasColor, HasLabel
{
    case Approved = 'approved';
    case Pending = 'pending';
    case Rejected = 'rejected';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Approved => __('advanced-tables::advanced-tables.status.approved'),
            self::Pending => __('advanced-tables::advanced-tables.status.pending'),
            self::Rejected => __('advanced-tables::advanced-tables.status.rejected'),
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Approved => 'success',
            self::Pending => 'warning',
            self::Rejected => 'danger',
        };
    }
}
