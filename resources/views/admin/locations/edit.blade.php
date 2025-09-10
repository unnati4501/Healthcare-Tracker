@extends('layouts.app')
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.locations.breadcrumb',[
    'mainTitle'  => trans('location.title.edit_form_title'),
    'breadcrumb' => 'location.edit',
    'create'     => false,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card form-card">
            {{ Form::open(['route' => ['admin.locations.update',$id], 'class' => 'form-horizontal zevo_form_submit', 'method'=>'PATCH','role' => 'form', 'id'=>'locationsEdit']) }}
            <div class="card-body">
                <div class="row justify-content-center justify-content-md-start">
                    @include('admin.locations.form',['edit' => true])
                </div>
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{!! route('admin.locations.index') !!}">
                        {{trans('buttons.general.cancel')}}
                    </a>
                    <button class="btn btn-primary" id="zevo_submit_btn" type="submit">
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
{!! JsValidator::formRequest('App\Http\Requests\Admin\EditLocationRequest','#locationsEdit') !!}
<script type="text/javascript">
var stateUrl = '{{ route("admin.ajax.states", ":id") }}';
var tzUrl = '{{ route("admin.ajax.timezones", ":id") }}';
var data = {
    oldCountry: `{{old('country')}}`,
    oldTimezone: `{{old('timezone')}}`,
    oldCountry: `{{old('county')}}`,
    placeholder: {
        select: `{{trans('location.form.placeholder.select')}}`,
    },
};
</script>
<script src="{{ asset('js/location/create-edit.js') }}">
</script>
@endsection
