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
    'appPageTitle' => trans('broadcast.title.edit_form_title'),
    'breadcrumb' => 'broadcast.edit',
    'create' => false,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card form-card">
            {{ Form::open(['route' => ['admin.broadcast-message.update', $broadcast->id], 'class' => 'form-horizontal zevo_form_submit', 'method' => 'PATCH', 'role' => 'form', 'id'=>'broadcastMessageEdit']) }}
            <div class="card-body">
                <div class="row justify-content-center justify-content-md-start">
                    @include('admin.broadcast-message.form', ['edit' => true])
                </div>
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{{ route('admin.broadcast-message.index') }}">
                        {{ trans('buttons.general.cancel') }}
                    </a>
                    <button class="btn btn-primary" id="zevo_submit_btn" type="submit">
                        {{ trans('buttons.general.update') }}
                    </button>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</section>
@endsection
@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\EditBoradcastMessageRequest', '#broadcastMessageEdit') !!}
<script src="{{ asset('assets/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('js/external/jquery.form.min.js?var='.rand()) }}">
</script>
<script type="text/javascript">
var url = {
    redirect: `{{ route('admin.broadcast-message.index') }}`,
},
message = {
    something_wrong_try_again: `{{ trans('broadcast.message.something_wrong_try_again') }}`,
};
</script>
<script src="{{ asset('js/broadcast/edit.js') }}" type="text/javascript">
</script>
@endsection
