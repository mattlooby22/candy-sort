<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Candy Sort Puzzle Solver</title>

    @livewireStyles
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <livewire:candy-sort />

    @livewireScripts
</body>
</html>
