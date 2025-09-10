@extends('layouts.app')

@section('after-styles')
    <!-- DataTables -->
    <link href="{{asset('assets/plugins/datepicker/datepicker3.css?var='.rand())}}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.locations.breadcrumb',[
    'mainTitle' => trans('location.title.add_form_title'),
    'breadcrumb'  => 'location.create',
    'create'      => false,
])
<!-- /.content-header -->
@endsection
@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card form-card">
                {{ Form::open(['route' => 'admin.locations.store', 'class' => 'form-horizontal zevo_form_submit', 'method'=>'post','role' => 'form', 'id'=>'locationAdd']) }}
                <div class="card-body">
                    <div class="row justify-content-center justify-content-md-start">
                        @include('admin.locations.form',['edit' => false])
                    </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                    <div class="save-cancel-wrap">
                        <a class="btn btn-outline-primary" href="{!! route('admin.locations.index') !!}">
                            {{trans('buttons.general.cancel')}}
                        </a>
                        <button class="btn btn-primary" type="submit" id="zevo_submit_btn">
                            {{trans('buttons.general.save')}}
                        </button>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </section>
@endsection

@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\CreateLocationRequest','#locationAdd') !!}
<script src="{{asset('assets/plugins/datepicker/bootstrap-datepicker.js?var='.rand())}}"></script>
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
