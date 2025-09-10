@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.admin-alerts.breadcrumb', [
  'mainTitle' => trans('page_title.admin-alerts.edit_admin_alert'),
  'breadcrumb' => 'admin-alerts.edit'
])
<!-- /.content-header -->
@endsection

@section('content')
    <div class="container-fluid">
        {{ Form::open(['route' => ['admin.admin-alerts.update', $record], 'class' => 'form-horizontal zevo_form_submit', 'method'=>'post','role' => 'form', 'id'=>'updateadminalertform']) }}
        <div class="card form-card">
            <div class="card-body">
                @include('admin.admin-alerts.form')
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{!! route('admin.admin-alerts.index') !!}">
                        {{ trans('buttons.general.cancel') }}
                    </a>
                    <button class="btn btn-primary" id="zevo_submit_btn" type="submit">
                        {{ trans('buttons.general.save') }}
                    </button>
                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div>
</section>
@include('admin.admin-alerts.addedit-user-model')
@include('admin.admin-alerts.delete-model')
@endsection

<!-- include datatable css -->
@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\UpdateConsentformRequest','#updateconsentform') !!}
<!-- DataTables -->
<script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/ckeditor5/ckeditor.js?var='.rand()) }}">
</script>
<script src="{{ asset('js/external/external-ckeditor.js?var='.rand()) }}">
</script>
<script id="admin_alert_user_data_template" type="text/html">
@include('admin.admin-alerts.users', [
    "key" => ":key", 
    "user_name" => ":user_name", 
    "user_email" => ":user_email", 
    "id" => ":id"
])
</script>
<script type="text/javascript">
    var pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    },
    message = {
        user_deleted: `{{ trans('adminalert.message.user_deleted') }}`,
        email_required: `{{ trans('adminalert.validation.user_email_required') }}`,
        email_valid: `{{ trans('adminalert.validation.user_email_valid') }}`,
        email_exists: `{{ trans('adminalert.validation.user_email_exists') }}`,
        user_name_required: `{{ trans('adminalert.validation.user_name_required') }}`,
        user_name_valid: `{{ trans('adminalert.validation.user_name_valid') }}`,
        desc_required: `{{ trans('adminalert.validation.desc_required') }}`,
        desc_length:  `{{ trans('adminalert.validation.desc_length') }}`
    };
</script>
<script src="{{ mix('js/admin-alerts/edit.js') }}">
</script>
@endsection