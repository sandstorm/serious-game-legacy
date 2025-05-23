@props(['$closeModal' => ''])

<div class="modal">
    <div class="modal__backdrop" wire:click={{$closeModal}}></div>
    <div class="modal__content">
        <header>
            <span>@yield('title')</span> <button type="button" class="button" wire:click={{$closeModal}}>x</button>
        </header>

        <div class="modal__body">
            @yield('content')
        </div>

        <footer class="modal__actions">
            @yield('footer')
        </footer>
    </div>
</div>
