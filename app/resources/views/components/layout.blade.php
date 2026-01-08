@props(['removePadding' => false])

<html lang="de">
<head>
    <title>
        Legacy
        @if (isset($title)) - {{ $title }} @endif
    </title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="app-config-js" content="{{ json_encode([
        'reverbAppKey' => config('broadcasting.connections.reverb.key'),
    ]) }}" />

    <link rel="icon" type="image/svg+xml" href="{{asset('./images/legacy-favicon-dark.svg')}}">
    <link rel="icon" type="image/svg+xml" href="{{asset('./images/legacy-favicon-light.svg')}}" media="(prefers-color-scheme: dark)">
    <link rel="icon" type="image/svg+xml" href="{{asset('./images/legacy-favicon-dark.svg')}}" media="(prefers-color-scheme: light)">

    @vite(['resources/js/app.js'])
</head>
<body>
<main class="container {{ $removePadding ? 'container--no-padding' : '' }} {{ isset($footer) ? 'container--with-footer' : '' }}">
    {{ $slot }}
</main>

@if (isset($footer))
    {{ $footer }}
@endif

@livewireScriptConfig
</body>
</html>
