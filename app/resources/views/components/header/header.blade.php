@props(['title' => null])

<header class="game-header">
    <div>
        {{ $title }}
    </div>
    <a href="{{ route('logout') }}" class="button button--type-text">
        Logout
    </a>
</header>
