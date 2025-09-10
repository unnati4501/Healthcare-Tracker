<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8"/>
        <meta content="IE=edge" http-equiv="X-UA-Compatible"/>
        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1" name="viewport"/>
        <!-- CSRF Token -->
        <meta content="{{ csrf_token() }}" name="csrf-token"/>
        <!-- page title -->
        <title>
            {{ (!empty($ga_title))? ucwords($ga_title) : config('app.name', env('APP_NAME')) }}
        </title>
        <!-- icon -->
        <link href="{!! asset('assets/dist/img/logo.png') !!}" rel="icon" sizes="32x32" type="image/gif"/>
        <!-- font style -->
        <link href="https://fonts.gstatic.com?var=<?= rand() ?>" rel="preconnect"/>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600;1,700;1,800&display=swap?var=<?= rand() ?>" rel="stylesheet"/>
        <!-- Styles -->
        <link href="{{asset('assets/plugins/toastr/toastr.min.css?var='.rand())}}" rel="stylesheet"/>
        <link href="{!! asset('assets/plugins/OwlCarousel2/owl.carousel.min.css?var='.rand()) !!}" rel="stylesheet"/>
        <link href="{!! asset('assets/plugins/OwlCarousel2/owl.theme.default.min.css?var='.rand()) !!}" rel="stylesheet"/>
        <!-- Theme style -->
        <link href="{!! asset('assets/dist/css/main.css?var='.rand()) !!}" rel="stylesheet"/>
        <!-- jQuery Custom -->
        <script src="{!! asset('assets/plugins/jquery/jquery.min.js?var='.rand()) !!}">
        </script>
        <script id="ze-snippet" src="https://static.zdassets.com/ekr/snippet.js?key={{ config('zevolifesettings.zendesk_key') }}">
        </script>
        @include('layouts.partials.ga-script')
    @yield('after-scripts')
    </head>
    <body class="hold-transition sidebar-mini">
        <!-- Page Loader -->
        @include('layouts.partials.loader')
        <!-- ./ Page Loader -->
        <div class="wrapper">
            <!-- ./wrapper -->
            <!-- cookie modal -->
            <section class="content">
                <div class="non-auth-area">
                    <!-- Left -->
                    <div class="non-auth-left-area" style="background-image:url('{{ (!empty($branding->branding_logo_background))? $branding->branding_logo_background : asset('assets/dist/img/login-banner.jpg')  }}')">
                        <div class="auth-slid d-flex flex-column h-100">
                            <div class="mt-auto mb-auto">
                                <div class="auth-slid-text-area text-white">
                                    <h1>
                                        {{ $branding->title  }}
                                    </h1>
                                    <p class="sub-header">
                                        {{ $branding->description  }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ./Left -->
                    <!-- Right -->
                    <div class="non-auth-right-area d-flex flex-column">
                        <div class="mb-0 non-auth-right-head">
                            <img alt="Logo" class="non-auth-logo" src="{{ $branding->company_logo }}"/>
                        </div>
                        <div class="non-auth-right-body mt-auto mb-auto">
                            <div class="ps-5 pe-5 p-2 pb-0">
                                @yield('content')
                            </div>
                        </div>
                        <div class="non-auth-right-body">
                            <div class="fw-bold pb-3 text-center">
                                <a class="openwidget" href="javascript:;">
                                    Need Help?
                                </a>
                            </div>
                            @php
                                $appEnvironment           = app()->environment();
                                $domain = config('zevolifesettings.footer_copyright_text_domain.'.$appEnvironment)
                            @endphp
                            @if($branding->url == $domain && !empty($branding->is_reseller) && $branding->is_reseller == 1)
                            <div class="text-center gray-500" style="margin: 20px; text-align: justify;">
                                {{ trans('layout.footer.texts.copyright') }}
                                {{ date('Y') }}
                                Irish Life Wellbeing Limited. A private company limited by shares. Registered in Ireland No.686621. Registered Office: Irish Life Centre, Lower Abbey Street, Dublin 1.
                            </div>
                            @else
                            <div class="text-center gray-500">
                                {{ trans('layout.footer.texts.copyright') }} 
                                {{ date('Y') }}
                                <a href="{{ url('/') }}">
                                    {{config('app.name', env('APP_NAME'))}}
                                </a>
                                {{ trans('layout.footer.texts.rights') }}
                            </div>
                            @endif
                            <p class="sub-footer-links font-12 text-center">
                                <a href="{{ $branding->privacy_policy }}" target="_blank" title="Privacy Policy">
                                    {{ trans('layout.footer.links.privacy_policy') }}
                                </a>
                                <span>
                                    |
                                </span>
                                <a href="{{ $branding->cookie_policy }}" target="_blank" title="Cookie Policy">
                                    {{ trans('layout.footer.links.cookie_policy') }}
                                </a>
                            </p>
                        </div>
                    </div>
                    <!-- ./Right -->
                </div>
            </section>
        </div>
        <!-- Toast Message -->
        <script src="{{asset('assets/plugins/toastr/toastr.min.js?var='.rand())}}">
        </script>
        <!-- /.container-fluid -->
        @include('layouts.partials.tostar')
        <!-- /.content -->
        <!-- /.content-wrapper -->
        <!-- Bootstrap 4 -->
        <script src="{!! asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js?var='.rand()) !!}">
        </script>
        <!-- Laravel Javascript Validation -->
         <script src="{{ asset('assets/plugins/jsvalidation/js/jsvalidation.min.js?var='.rand())}}" type="text/javascript">
        </script>
        <script src="{{ asset('assets/plugins/OwlCarousel2/owl.carousel.min.js?var='.rand())}}" type="text/javascript">
        </script>
        <script src="{{ asset('assets/dist/js/custom-auth.js?var='.rand())}}" type="text/javascript">
        </script>
        <script src="{{asset('assets/dist/js/custom.js?var='.rand())}}">
        </script>
    </body>
</html>