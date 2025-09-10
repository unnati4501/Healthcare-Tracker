@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/OwlCarousel2/owl.carousel.min.css?var='.rand()) }}" rel="stylesheet"/>
<link href="{{ asset('assets/plugins/OwlCarousel2/owl.theme.default.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content')
<div class="modal fade has-solid-bg" data-backdrop="static" data-keyboard="false" id="availibility-error-popup" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body position-relative text-center">
                <a class="close" type="button" href="{{route('admin.cronofy.sessions.index')}}">
                    <i class="fal fa-times"></i>
                </a>
                <img class="not-found-img" src="{{ config('zevolifesettings.fallback_image_url.cronofy-availability') }}" alt="image"/>
                <p><br/></p>
                <h3>Oops..!</h3>
                <h6 class="mb-4">We apologise for the inconvenience as we are facing some technical problem, please try again in some time. Please let us know at <a href="mailto:support@zevohealth.zendesk.com">support@zevohealth.zendesk.com</a> if you have any concerns. </h5>
                <a class="btn btn-primary" type="button" href="{{route('admin.cronofy.sessions.index')}}">
                    {{ trans('buttons.general.okay') }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
@section('after-scripts')
<!-- include datatable css -->
<script type="text/javascript">
$(document).ready(function() {
	$("#availibility-error-popup").modal('show');
});
</script>

@endsection