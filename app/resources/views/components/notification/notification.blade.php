
@if ($this->notificationIsVisible)
    <div @class([
        'notification',
        'notification--type-'.$this->notificationType->value
    ])>
        <div class="notification__backdrop" wire:click="closeNotification()"></div>
        <div class="notification__content">
            <div class="notification__icon">
                <i class="icon-ereignis"></i>
            </div>
            <div class="notification__close-button">
                <button type="button" class="button button--type-text" wire:click="closeNotification()">
                    <span class="sr-only">Mitteilung schließen</span>
                    <i class="icon-close" aria-hidden="true"></i>
                </button>
            </div>

            <div class="notification__body">
                {{$this->notificationMessage}}
            </div>

            <footer class="notification__actions">
                <button type="button" class="button button--type-primary" wire:click="closeNotification()">Ok</button>
            </footer>
        </div>
    </div>
@endif
