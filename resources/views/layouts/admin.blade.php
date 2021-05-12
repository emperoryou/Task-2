<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title') &dash; {{(Utility::getValByName('header_text')) ? Utility::getValByName('header_text') : config('app.name') }}</title>
    <link rel="icon" href="{{ asset(Storage::url('logo/favicon.png')) }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="{{ asset('assets/libs/@fortawesome/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/fullcalendar/dist/fullcalendar.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/animate.css/animate.min.css') }}">
    @if(Auth::user()->mode == 'light')
        <link rel="stylesheet" href="{{ asset('assets/css/site-light.css') }}">
    @else
        <link rel="stylesheet" href="{{ asset('assets/css/site-dark.css') }}">
    @endif
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
    @stack('css')
</head>
<body class="application application-offset">
<div class="container-fluid container-application">
    <div class="sidenav pb-2" id="sidenav-main">
        @include('partials.admin.sidebar')
    </div>
    <div class="main-content position-relative">
        @include('partials.admin.navbar')
        <div class="page-content">
            @if (trim($__env->yieldContent('title')) != 'Task Calendar')
                <div class="page-title">
                    <div class="row justify-content-between align-items-center">
                        <div class="col-xs-12 col-sm-12 col-md-4 d-flex align-items-center justify-content-between justify-content-md-start mb-3 mb-md-0">
                            <div class="d-inline-block">
                                <h5 class="h4 d-inline-block font-weight-400 mb-0 text-white">@yield('title')</h5>
                                @if (trim($__env->yieldContent('role')))
                                    <p class="text text-white m-0">(@yield('role'))</p>
                                @endif
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-8 d-flex align-items-center justify-content-between justify-content-md-end">
                            @yield('action-button')
                        </div>
                    </div>
                </div>
            @endif

            @yield('content')
        </div>
    </div>
</div>

{{--Footer--}}
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="footer pt-5 pb-4 footer-light" id="footer-main">
                <div class="row text-center text-sm-left align-items-sm-center">
                    <div class="col-sm-6">
                        <p class="text-sm mb-0">{{ \App\Utility::getValByName('footer_text') }}</p>
                    </div>
                    <div class="col-sm-6 mb-md-0">
                        <ul class="nav justify-content-center justify-content-md-end">
                            <li class="nav-item dropdown border-right">
                                <a class="nav-link" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="h6 text-sm mb-0"><i class="fas fa-globe-asia"></i> {{ Str::upper(Auth::user()->lang) }}</span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-sm dropdown-menu-right">
                                    @foreach(\App\Utility::languages() as $lang)
                                        <a href="{{route('lang.change',$lang)}}" class="dropdown-item {{ (basename(App::getLocale()) == $lang) ? 'active' : '' }} ">{{ Str::upper($lang) }}</a>
                                    @endforeach
                                </div>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('change.mode') }}">{{(Auth::user()->mode == 'light') ? __('Dark Mode') : __('Light Mode')}}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ \App\Utility::getValByName('footer_value_1') }}">{{ \App\Utility::getValByName('footer_link_1') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ \App\Utility::getValByName('footer_value_2') }}">{{ \App\Utility::getValByName('footer_link_2') }}</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ \App\Utility::getValByName('footer_value_3') }}">{{ \App\Utility::getValByName('footer_link_3') }}</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{--Common Modal--}}
<div class="modal fade" tabindex="-1" role="dialog" id="commonModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body"></div>
        </div>
    </div>
</div>
{{--End Common Modal--}}

{{--Side Modal--}}
<div class="modal fade fixed-right" id="commonModal-right" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="scrollbar-inner">
        <div class="min-h-300 mh-300">
        </div>
    </div>
</div>
{{--Side Modal End--}}

<!-- Omnisearch -->
<div id="omnisearch" class="omnisearch">
    <div class="container">
        <div class="omnisearch-form">
            <div class="form-group">
                <div class="input-group input-group-merge input-group-flush">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                    <input type="text" class="form-control search_keyword" placeholder="{{__('Search by Task Name..')}}">
                </div>
            </div>
        </div>
        <div class="omnisearch-suggestions">
            <h6 class="heading">{{__('Search Suggestions')}}</h6>
            <div class="row">
                <div class="col-sm-12">
                    <ul class="list-unstyled mb-0 search_output">
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

</body>

<!-- Scripts -->
<script src="{{ asset('assets/js/site.core.js') }}"></script>

<script src="{{ asset('assets/libs/moment/min/moment.min.js') }}"></script>
<script src="{{ asset('assets/libs/fullcalendar/dist/fullcalendar.min.js') }}"></script>
<script src="{{ asset('assets/libs/progressbar.js/dist/progressbar.min.js') }}"></script>
<script src="{{ asset('assets/libs/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
<script src="{{ asset('assets/libs/autosize/dist/autosize.min.js') }}"></script>

@stack('theme-script')

<script src="{{ asset('assets/js/site.js') }}"></script>
<script src="{{ asset('assets/js/letter.avatar.js') }}"></script>
<script src="{{ asset('assets/js/custom.js') }}"></script>

@stack('script')

@if(Session::has('success'))
    <script>
        show_toastr('{{__('Success')}}', '{!! session('success') !!}', 'success');
    </script>
    {{ Session::forget('success') }}
@endif
@if(Session::has('error'))
    <script>
        show_toastr('{{__('Error')}}', '{!! session('error') !!}', 'error');
    </script>
    {{ Session::forget('error') }}
@endif

<script>
    @if(Auth::user()->type != 'admin')
    $(document).ready(function () {
        search_data();
        $(document).on('keyup', '.search_keyword', function () {
            search_data($(this).val());
        });
    });
    @endif

    @if(Auth::user()->type != 'admin')
    // Common main search
    function search_data(keyword = '') {
        $.ajax({
            url: '{{ route('search.json') }}',
            data: {keyword: keyword},
            success: function (data) {
                $('.search_output').html(data);
            }
        });
    }
    @endif
</script>
</html>
