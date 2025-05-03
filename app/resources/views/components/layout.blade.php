<!-- resources/views/components/layout.blade.php -->

<html>
<head>
    <title>{{ $title ?? 'Game' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
{{ $slot }}
</body>
</html>
