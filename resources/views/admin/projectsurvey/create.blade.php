@extends('layouts.app')
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.projectsurvey.breadcrumb',[
    'mainTitle'      => trans('customersatisfaction.projectsurvey.title.add_form_title'),
    'breadcrumb'     => 'projectsurvey.create',
    'showbackbutton' => false,
])
<!-- /.content-header -->
@endsection
@section('content')
 <link href="{{asset('assets/plugins/datepicker/datepicker3.css?var='.rand())}}" rel="stylesheet"/>
    <section class="content">
        <div class="container-fluid">
            <!-- /.card-header -->
            {{ Form::open(['route' => 'admin.projectsurvey.store', 'class' => 'form-horizontal zevo_form_submit', 'method'=>'post','role' => 'form', 'id'=>'projectSurveyAdd']) }}
            <div class="card form-card">
                    <div class="card-body">
                        <div class="row justify-content-center justify-content-md-start">
                            @include('admin.projectsurvey.form')
                        </div>
                    </div>
                <!-- /.card-body -->
                <div class="card-footer">
                    <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{!! route('admin.reports.nps') !!}" >{{trans('buttons.general.cancel')}}</a>
                    <button type="submit" class="btn btn-primary" id="zevo_submit_btn">{{trans('buttons.general.save')}}</button>
                    </div>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </section>
@endsection

@section('after-scripts')
<script src="{{asset('assets/plugins/datepicker/bootstrap-datepicker.js?var='.rand())}}"></script>
{!! JsValidator::formRequest('App\Http\Requests\Admin\CreateProjectSurveyRequest','#projectSurveyAdd') !!}
<script src="{{ mix('js/customersatisfaction/add-edit-project.js') }}" type="text/javascript">
</script>
@endsection