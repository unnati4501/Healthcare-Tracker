@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/tree-multiselect/tree-multiselect.css?var='.rand())}}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.badge.breadcrumb', [
    'appPageTitle' => trans('badge.title.edit_form_title'),
    'breadcrumb' => 'badge.edit',
    'create' => false,
    'back' => false,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card form-card">
            <!-- /.card-header -->
            {{ Form::open(['route' => ['admin.badges.update',$id], 'class' => 'form-horizontal zevo_form_submit', 'method'=>'PATCH','role' => 'form', 'id'=>'badgeEdit','files' => true]) }}
            <div class="card-body">
                <div class="card-inner">
                    <div class="row">
                        @include('admin.badge.form', ['edit'=>true])
                    </div>
                </div>
            </div>
            <!-- /.card-body -->
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{!! route('admin.badges.index') !!}">
                        {{trans('buttons.general.cancel')}}
                    </a>
                    <button class="btn btn-primary" id="badgeFormsubmit" type="submit">
                        {{trans('buttons.general.update')}}
                    </button>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</section>
@endsection

@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\EditBadgeRequest','#badgeEdit') !!}
<script type="text/javascript">
var data = {
    uom_data: '<?php echo json_encode($uom_data); ?>',
    uomBadgeData: `{{ $badgeData->uom }}`,
},
message = {
    image_valid_error: `{{trans('badge.validation.image_valid_error')}}`,
    image_size_2M_error: `{{trans('badge.validation.image_size_2M_error')}}`,
    upload_image_dimension: '{{ trans('badge.message.upload_image_dimension') }}',
};
</script>
<script src="{{ asset('js/badge/edit.js') }}" type="text/javascript">
</script>
@endsection
