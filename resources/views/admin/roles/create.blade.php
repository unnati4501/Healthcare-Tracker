@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.roles.breadcrumb', [
  'mainTitle' => trans('roles.title.add'),
  'breadcrumb' => 'role.create',
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card form-card">
            {{ Form::open(['route' => 'admin.roles.store', 'class' => 'form-horizontal zevo_form_submit', 'method' => 'post', 'role' => 'form', 'id' => 'roleAdd']) }}
            <div class="card-body">
                @include('admin.roles.form', ['edit' => false])
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{{ route('admin.roles.index') }}">
                        {{ trans('buttons.general.cancel') }}
                    </a>
                    <button class="btn btn-primary" onclick="formSubmit()" type="submit">
                        {{ trans('buttons.general.save') }}
                    </button>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</section>
@endsection

@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\CreateRoleRequest','#roleAdd') !!}
<script src="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.js?var='.rand()) }}" type="text/javascript">
</script>
<script type="text/javascript">
    var permissionsUrl = `{{ route("admin.ajax.permissions", ":group") }}`;
</script>
<script src="{{ asset('js/roles/create.js') }}" type="text/javascript">
</script>
@endsection
