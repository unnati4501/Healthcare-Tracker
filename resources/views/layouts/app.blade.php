<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8"/>
        <meta content="IE=edge" http-equiv="X-UA-Compatible"/>
        <title>
            {{ (!empty($ga_title))? ucwords($ga_title) : config('app.name', env('APP_NAME')) }}
        </title>
        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1" name="viewport"/>
        <meta content="{{ csrf_token() }}" name="csrf-token"/>
        <!-- icon -->
        <link href="{!! asset('assets/dist/img/logo.png') !!}" rel="icon" sizes="32x32" type="image/gif"/>
        @yield('meta')
        <!-- Styles -->
        @yield('before-styles')
        <!-- font style -->
        <link href="https://fonts.gstatic.com" rel="preconnect"/>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet"/>
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
        <div class="wrapper">
            <!-- Main Sidebar Container -->
            @include('layouts.partials.main_sidebar')
            <!-- ./ Main Sidebar Container -->
            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                <!-- Navbar -->
                @include('layouts.partials.navbar')
                <!-- /.navbar -->
                <!-- Content Header (Page header) -->
                @yield('content-header')
                <!-- /.content-header -->
                <!-- content -->
                @yield('content')
                <!-- /.content -->
            </div>
            <!-- /.content-wrapper -->
            <!-- Main Footer -->
            @include('layouts.partials.footer')
            <!-- /.Main Footer -->

        </div>
        <!-- ./wrapper -->
        @yield('before-scripts')
        <!-- Laravel Javascript Validation -->
        <script src="{{ asset('assets/plugins/jsvalidation/js/jsvalidation.min.js?var='.rand())}}" type="text/javascript">
        </script>
        <!-- Bootstrap 4 -->
        <script src="{!! asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js?var='.rand()) !!}">
        </script>
        <!-- Slimscroll -->
        <script src="{!! asset('assets/plugins/customScrollbar/jquery.mCustomScrollbar.concat.min.js?var='.rand()) !!}">
        </script>
        <!-- select2 -->
        <script src="{!! asset('assets/plugins/select2/select2.full.min.js?var='.rand()) !!}">
        </script>
        <script src="{!! asset('assets/dist/js/custom.js?var='.rand()) !!}">
        </script>
        <script src="{{asset('assets/plugins/toastr/toastr.min.js?var='.rand())}}">
        </script>
        @include('layouts.partials.tostar')
        @yield('after-scripts')
        <script>
            var fullname = '{{Auth::user()->first_name}}' + ' ' + '{{Auth::user()->last_name}}';
            window.intercomSettings = {
              app_id: "{{config('zevoconnect.intercom.app_id')}}",
              name: fullname, // Full name
              email: '{{Auth::user()->email}}', // Email address
              created_at: '{{strtotime(Auth::user()->created_at)}}' // Signup date as a Unix timestamp
            };
        </script>
        <script>
            (function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic('reattach_activator');ic('update',w.intercomSettings);}else{var d=document;var i=function(){i.c(arguments);};i.q=[];i.c=function(args){i.q.push(args);};w.Intercom=i;var l=function(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://widget.intercom.io/widget/wly57b8q';var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);};if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})();
        </script>

    </body>
</html>