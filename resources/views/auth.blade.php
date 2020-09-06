<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }} ― Canvas</title>

    <link rel="stylesheet" type="text/css" href="{{ mix('css/app.css', 'vendor/canvas') }}">
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link rel="stylesheet" href="//fonts.googleapis.com/css?family=Karla|Merriweather:400,700">

    <style>
        body,html{
            height:100%;
        }
    </style>
</head>
<body>
<div class="container h-100">
    @yield('content')
</div>
</body>
</html>
