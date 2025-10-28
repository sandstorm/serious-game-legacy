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

    @vite(['resources/js/app.js'])
</head>
<body>
<main class="container">
    {{ $slot }}
</main>

@livewireScriptConfig
</body>
</html>
