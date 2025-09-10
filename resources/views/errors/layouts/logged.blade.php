@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row mm-h-80">
        <div class="col-12 d-flex align-self-md-center">
            <div class="error-msg-box-main w-100">
                <div class="text-center">
                    <div class="paga-404-logo">
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
@endsection
