<?php

declare(strict_types=1);

namespace App\Livewire\Traits;

enum NotificationType: string
{
    case INFO = 'info';
    case WARNING = 'warning';
    case ERROR = 'error';
}

trait HasNotification
{
    public bool $notificationIsVisible = false;
    public string $notificationMessage = '';
    public NotificationType $notificationType = NotificationType::INFO;

    public function showNotification(?string $message, NotificationType $notificationType): void
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
