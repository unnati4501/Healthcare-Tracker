<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8"/>
        <meta content="IE=edge" http-equiv="X-UA-Compatible"/>
        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1" name="viewport"/>
        <meta content="{{ csrf_token() }}" name="csrf-token"/>
        <title>{{ (!empty($ga_title))? ucwords($ga_title) : config('app.name', env('APP_NAME')) }}</title>
        <!-- icon -->
        <link href="{!! asset('assets/dist/img/logo.png') !!}" rel="icon" sizes="32x32" type="image/gif"/>
        @yield('meta')
        <!-- Styles -->
        @yield('before-styles')
        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600;1,700;1,800&display=swap?var=<?= rand() ?>" rel="stylesheet"/>
        <!-- mCustomScrollbar -->
        <link href="{!! asset('assets/plugins/customScrollbar/jquery.mCustomScrollbar.css?var='.rand()) !!}" rel="stylesheet"/>
        <!-- Select2 -->
        <link href="{{asset('assets/plugins/select2/select2.min.css?var='.rand())}}" rel="stylesheet"/>
        <!-- Toastr -->
        <link href="{{asset('assets/plugins/toastr/toastr.min.css?var='.rand())}}" rel="stylesheet"/>
        @yield('after-styles')
        <!-- Theme style -->
        <link href="{!! asset('assets/dist/css/main.css?var='.rand()) !!}" rel="stylesheet"/>
        <script src="{{asset('assets/plugins/jquery/jquery.min.js?var='.rand())}}">
        </script>
        <script type="text/javascript">
            window.Laravel = {!! json_encode([ 'csrfToken' => csrf_token() ]) !!};
            var APPURL = "{{url('/')}}",
                _ZBASEURL = "{{ config('app.url') }}";
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
        </script>
        @include('layouts.partials.ga-script')
    </head>
    <body class="hold-transition sidebar-mini">
        <!-- Page Loader -->
        @include('layouts.partials.loader')
        @include('layouts.partials.progressbar-loader')
        <!-- ./ Page Loader -->
        <!-- Navbar -->
        @include('layouts.partials.survey-navbar')
        <!-- /.navbar -->
        <!-- content -->
        @yield('content')
        <!-- /.content -->
        <!-- ./wrapper -->
        @yield('before-scripts')
        <!-- Laravel Javascript Validation -->
        <script src="{{ asset('assets/plugins/jsvalidation/js/jsvalidation.min.js?var='.rand())}}" type="text/javascript">
        </script>
        <!-- Bootstrap 4 -->
        <script src="{!! asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js?var='.rand()) !!}" type="text/javascript">
        </script>
        <!-- Slimscroll -->
        <script src="{!! asset('assets/plugins/customScrollbar/jquery.mCustomScrollbar.concat.min.js?var='.rand()) !!}" type="text/javascript">
        </script>
        <!-- select2 -->
        <script src="{!! asset('assets/plugins/select2/select2.full.min.js?var='.rand()) !!}" type="text/javascript">
        </script>
        <script src="{!! asset('assets/dist/js/custom.js?var='.rand()) !!}" type="text/javascript">
        </script>
        <script src="{{asset('assets/plugins/toastr/toastr.min.js?var='.rand())}}" type="text/javascript">
        </script>
        @yield('after-scripts')
        @include('layouts.partials.tostar')
    </body>
</html>