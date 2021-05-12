<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') &dash; {{ config('app.name', 'TaskGo') }}</title>
    <script src="{{ asset('js/app.js') }}" defer></script>
    <link rel="icon" href="{{ asset(Storage::url('logo/favicon.png')) }}" type="image/png">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/libs/@fortawesome/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/site-light.css') }}" id="stylesheet">
</head>
<body class="application application-offset">
<div id="app">
    <div class="container-fluid container-application">
        <div class="main-content position-relative">
            <div class="page-content">
                <div class="min-vh-100 py-5 d-flex align-items-center">
                    <div class="w-100">
                        <div class="row justify-content-center">
                            @yield('language-bar')
                            @yield('content')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{{asset('assets/js/site.core.js')}}"></script>
<script src="{{asset('assets/js/site.js')}}"></script>
</body>
</html>
