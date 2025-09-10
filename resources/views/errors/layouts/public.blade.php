<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8"/>
        <meta content="width=device-width, initial-scale=1" name="viewport"/>
        <meta content="ie=edge" http-equiv="x-ua-compatible"/>
        <title>
            {{ config('app.name', env('APP_NAME')) }} - @yield('title')
        </title>
        <!-- icon -->
        @php
            $branding = getBrandingData();
        @endphp
        <link href="{!! asset('assets/dist/img/logo.png') !!}" rel="icon" sizes="32x32" type="image/gif"/>
        @yield('meta')
        <!-- Theme style -->
        <link href="{{asset('assets/dist/css/main.css?var='.rand())}}" rel="stylesheet"/>
        <!-- REQUIRED SCRIPTS -->
        <!-- jQuery -->
        <script src="{{asset('assets/plugins/jquery/jquery.min.js?var='.rand())}}">
        </script>
    </head>
    <body class="@yield('body-class','hold-transition')">
        <section class="page_404">
            <div class="container-fluid">
                <div class="row mm-h-80">
                    <div class="col-12 d-flex align-self-md-center">
                        <div class="error-msg-box-main w-100">
                            <div class="text-center">
                                <div class="paga_404_inner">
                                    <div class="paga-404-logo">
                                        @if(!isset($home_button))
                                        <a class="logo-area" href="{{ url('/') }}">
                                            <img alt="{{ config('app.name', env('APP_NAME')) }}" class="header-logo" src="{{ $branding->company_logo }}" title="{{ config('app.name', env('APP_NAME')) }}" width="107px" height="38px"/>
                                        </a>
                                        @else
                                        <a class="logo-area">
                                            <img alt="{{ config('app.name', env('APP_NAME')) }}" class="header-logo" src="{{ $branding->company_logo }}" style="max-height: 60px;" title="{{ config('app.name', env('APP_NAME')) }}"/>
                                        </a>
                                        @endif
                                        <h1 class="text-center page-404-title">
                                            @yield('code', __('Oh no'))
                                        </h1>
                                    </div>
                                    <br/>
                                    @yield('image')
                                    <br/>
                                    <h4 class="mb-4 gray-500">
                                        @yield('message')
                                    </h2>
                                    @yield('sub_message')
                                    @if(!isset($home_button))
                                    <a class="btn btn-primary" href="{{ url('/') }}" title="Go to Home">
                                        {{ __('Go Home') }}
                                        <i aria-hidden="true" class="fa fa-angle-right ms-2">
                                        </i>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- Bootstrap -->
        <script src="{{asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js?var='.rand())}}">
        </script>
    </body>
</html>