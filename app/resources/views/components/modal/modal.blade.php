@props(['$isVisible' => false])

@if ($isVisible)
    <div class="modal">
        <div class="modal__content">
            <header>
                @yield('title')
            </header>

            <div class="modal__body">
                @yield('content')
            </div>


            <footer class="modal__actions">
                @yield('footer')
            </footer>
        </div>
    </div>
@endif
