
@if ($this->notificationIsVisible)
    <div @class([
        'notification',
        'notification--type-'.$this->notificationType->value
    ])>
        <div class="notification__backdrop" wire:click="closeNotification()"></div>
        <div class="notification__content">
            <div class="notification__body">
                {{$this->notificationMessage}}
            </div>

            <footer class="notification__actions">
                <button type="button" class="button button--type-primary" wire:click="closeNotification()">Ok</button>
            </footer>
        </div>
    </div>
@endif
