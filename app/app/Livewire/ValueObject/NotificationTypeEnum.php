<?php

declare(strict_types=1);

namespace App\Livewire\ValueObject;

enum NotificationTypeEnum: string
{
    case INFO = 'info';
    case WARNING = 'warning';
    case ERROR = 'error';
}
