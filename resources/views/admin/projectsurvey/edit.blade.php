@extends('layouts.app')
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.projectsurvey.breadcrumb',[
    'mainTitle'      => trans('customersatisfaction.projectsurvey.title.edit_form_title'),
    'breadcrumb'     => 'projectsurvey.edit',
    'showbackbutton' => false,
])
<!-- /.content-header -->
@endsection
@section('content')
<link href="{{asset('assets/plugins/datepicker/datepicker3.css?var='.rand())}}" rel="stylesheet"/>
<section class="content">
    <div class="container-fluid">
        <div class="card form-card">
          <!-- /.card-header -->
            {{ Form::open(['route' => ['admin.projectsurvey.update',$surveyData->id], 'class' => 'form-horizontal zevo_form_submit', 'method'=>'post','role' => 'form', 'id'=>'projectSurveyEdit']) }}
            <div class="card-body">
                    <div class="row justify-content-center justify-content-md-start">
                        @include('admin.projectsurvey.form')
                    </div>
            </div>
          <!-- /.card-body -->
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{!! route('admin.reports.nps') !!}">{{trans('buttons.general.cancel')}}</a>
                    <button type="submit" class="btn btn-primary" id="zevo_submit_btn">{{trans('buttons.general.update')}}</button>
                </div>
            </div>
          {{ Form::close() }}
          <!-- /.card-footer-->
        </div>

    </div><!-- /.container-fluid -->
</section>
@endsection

@section('after-scripts')
<script src="{{asset('assets/plugins/datepicker/bootstrap-datepicker.js?var='.rand())}}"></script>
{!! JsValidator::formRequest('App\Http\Requests\Admin\EditProjectSurveyRequest','#projectSurveyEdit') !!}
<script src="{{ mix('js/customersatisfaction/add-edit-project.js') }}" type="text/javascript">
</script>
@endsection