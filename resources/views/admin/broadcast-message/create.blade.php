@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css?var='.rand())}}" rel="stylesheet"/>
<style type="text/css">
    .datetimepicker { padding: 4px !important; }
    .datetimepicker .table-condensed th,.datetimepicker .table-condensed td { padding: 4px 5px; }
</style>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.broadcast-message.breadcrumb', [
    'appPageTitle' => trans('broadcast.title.add_form_title'),
    'breadcrumb' => 'broadcast.create',
    'create' => false,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <section class="col-lg-12">
                <div class="card form-card">
                    {{ Form::open(['route' => 'admin.broadcast-message.store', 'class' => 'form-horizontal zevo_form_submit', 'method' => 'POST', 'role' => 'form', 'id'=>'broadcastMessageAdd']) }}
                    <div class="card-body">
                        <div class="row justify-content-center justify-content-md-start">
                            @include('admin.broadcast-message.form', ['edit' => false])
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="save-cancel-wrap">
                            <a class="btn btn-outline-primary" href="{{ route('admin.broadcast-message.index') }}">
                                {{ trans('buttons.general.cancel') }}
                            </a>
                            <button class="btn btn-primary" id="zevo_submit_btn" type="submit">
                                {{ trans('buttons.general.save') }}
                            </button>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </section>
        </div>
    </div>
</section>
@endsection

@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\CreateBoradcastMessageRequest', '#broadcastMessageAdd') !!}
<script src="{{ asset('assets/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('js/external/jquery.form.min.js?var='.rand()) }}">
</script>
<script type="text/javascript">
var url = {
    dataTable: `{{ route('admin.broadcast-message.get-groups') }}`,
    redirect: `{{ route('admin.broadcast-message.index') }}`,
},
message = {
    something_wrong_try_again: `{{ trans('broadcast.message.something_wrong_try_again') }}`,
};
</script>
<script src="{{ asset('js/broadcast/create.js') }}" type="text/javascript">
</script>
@endsection
