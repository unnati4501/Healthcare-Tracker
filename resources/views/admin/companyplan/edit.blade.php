@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/tree-multiselect/tree-multiselect.css?var='.rand())}}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.companyplan.breadcrumb',[
    'mainTitle'  => trans('companyplans.title.edit_form_title'),
    'breadcrumb' => 'company-plan.edit',
    'create'     => false,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content no-default-select2">
    <div class="container-fluid">
        <div class="card form-card">
            {{ Form::open(['route' => ['admin.company-plan.update', $id], 'class' => 'form-horizontal zevo_form_submit', 'method'=>'patch','role' => 'form', 'id'=>'companyplanedit']) }}
            <div class="card-body">
                @include('admin.companyplan.form', ['edit' => true])
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{!! route('admin.company-plan.index') !!}">
                        {{ trans('buttons.general.cancel') }}
                    </a>
                    <button class="btn btn-primary" id="companyplanSubmit" type="submit">
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
{!! JsValidator::formRequest('App\Http\Requests\Admin\EditCompanyPlanRequest','#companyplanedit') !!}
<script src="{{asset('assets/plugins/moment/moment.min.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/jquery.numeric/jquery.numeric.min.js?var='.rand())}}">
</script>
<script src="{{ asset('js/external/jquery.form.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.js?var='.rand()) }}" type="text/javascript">
</script>
<script src="{{ asset('js/companyplan/edit.js') }}" type="text/javascript">
</script>
@endsection