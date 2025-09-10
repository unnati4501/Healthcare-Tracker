@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/tree-multiselect/tree-multiselect.css?var='.rand())}}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.badge.breadcrumb', [
    'appPageTitle' => trans('badge.title.add_form_title'),
    'breadcrumb' => 'badge.create',
    'create' => false,
    'back' => false,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card form-card">
            {{ Form::open(['route' => 'admin.badges.store', 'class' => 'form-horizontal zevo_form_submit', 'method'=>'post','role' => 'form', 'id'=>'badgeAdd','files' => true]) }}
            <div class="card-body">
                <div class="card-inner">
                    <div class="row">
                        @include('admin.badge.form', ['edit'=>false])
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{!! route('admin.badges.index') !!}" >{{trans('buttons.general.cancel')}}</a>
                    <button id="badgeFormsubmit" type="submit" class="btn btn-primary">{{trans('buttons.general.save')}}</button>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</section>
@endsection
@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\CreateBadgeRequest','#badgeAdd') !!}
<script type="text/javascript">
var data = {
    uom_data: '<?php echo json_encode($uom_data); ?>',
    exerciseType: `<?php echo json_encode($exerciseType); ?>`,
    challenge_targets: `<?php echo json_encode($challenge_targets); ?>`,
    ongoingChallengeTarget: `<?php echo json_encode($ongoingChallengeTarget); ?>`,
},
message = {
    image_valid_error: `{{trans('badge.validation.image_valid_error')}}`,
    image_size_2M_error: `{{trans('badge.validation.image_size_2M_error')}}`,
    badge_type_required: `{{ trans('badge.validation.badge_type_required') }}`,
    unit_of_measurement: `{{ trans('badge.validation.unit_of_measurement') }}`,
    upload_image_dimension: '{{ trans('badge.message.upload_image_dimension') }}',
};
</script>
<script src="{{ asset('js/badge/create.js') }}" type="text/javascript">
</script>
@endsection
