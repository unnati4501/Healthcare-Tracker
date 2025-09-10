@extends('layouts.app')
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.app-theme.breadcrumb', [
    'appPageTitle'  => trans('appthemes.title.add_form_title'),
    'breadcrumb'    => 'apptheme.create',
    'create'        => false,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
            {{ Form::open(['route' => 'admin.app-themes.store', 'class' => 'form-horizontal zevo_form_submit', 'method' => 'post', 'role' => 'form', 'id' => 'addTheme', 'files' => true]) }}
        <div class="card form-card">
            <div class="card-body">
                    <div class="row justify-content-center justify-content-md-start">
                        @include('admin.app-theme.form')
                    </div>
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{{ route('admin.app-themes.index') }}">
                        {{ trans('buttons.general.cancel') }}
                    </a>
                    {{ Form::button(trans('buttons.general.save'), ['type' => 'submit', 'id' => 'zevo_submit_btn', 'class' => 'btn btn-primary']) }}
                </div>
            </div>
        </div>
            {{ Form::close() }}
    </div>
</section>
@endsection
@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\CreateAppThemeRequest', '#addTheme') !!}
<script type="text/javascript">
var message = {
    json_valid_error: `{{ trans('appthemes.message.json_valid_error') }}`,
    json_size_2M_error: `{{ trans('appthemes.message.json_size_2M_error') }}`,
};
</script>
<script src="{{ asset('js/appthemes/create-edit.js') }}" type="text/javascript">
</script>
@endsection
