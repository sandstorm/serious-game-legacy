<!-- resources/views/components/layout.blade.php -->

<html>
<head>
    <title>{{ $title ?? 'Game' }}</title>

    <meta name="app-config-js" content="{{ json_encode([
        'reverbAppKey' => config('broadcasting.connections.reverb.key'),
    ]) }}" />

    @vite(['resources/js/app.js'])
</head>
<body>
{{ $slot }}
</body>
</html>
