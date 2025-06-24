<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

use App\Livewire\ValueObject\NotificationTypeEnum;

trait HasNotification
{
    public bool $notificationIsVisible = false;
    public string $notificationMessage = '';
    public NotificationTypeEnum $notificationType = NotificationTypeEnum::INFO;

    public function showNotification(?string $message, NotificationTypeEnum $notificationType): void
    {
        if ($message === null || $message === '') {
            return;
        }

        $this->notificationType = $notificationType;
        $this->notificationMessage = $message;
        $this->notificationIsVisible = true;
    }

    public function closeNotification(): void
    {
        $this->notificationIsVisible = false;
        $this->notificationMessage = '';
    }
}
