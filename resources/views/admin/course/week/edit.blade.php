@extends('layouts.app')

@section('after-styles')
    <link href="{{asset('assets/plugins/tree-multiselect/tree-multiselect.css?var='.rand())}}" rel="stylesheet"/>
@endsection

@section('content')
@include('admin.course.week.breadcrumb',['mainTitle'=>trans('labels.course.edit_module_title'), 'back' => 'course_week','edit' => true])
    <section class="content">
        <div class="container-fluid">
            <!-- Main row -->
            <div class="row">
            <!-- Left col -->
                <section class="col-lg-12">
                <!-- DIRECT CHAT -->
                    <div class="card">
                      <!-- /.card-header -->
                        {{ Form::open(['route' => ['admin.courses.updateModule',$record->getKey()], 'class' => 'form-horizontal zevo_form_submit', 'method'=>'POST','role' => 'form', 'id'=>'courseModuleEdit']) }}
                        <div class="card-body">
                                <div class="row">
                                    @include('admin.course.week.form')
                                </div>
                        </div>
                      <!-- /.card-body -->
                        <div class="card-footer border-top text-center">
                            <a class="btn btn-effect btn-outline-secondary me-2 mm-w-100" href="{!! route('admin.courses.manageModules', $record->course_id) !!}">{{trans('labels.buttons.cancel')}}</a>
                            <button id="zevo_submit_btn" type="submit" class="btn btn-primary btn-effect mm-w-100" onclick="formSubmit();">{{trans('labels.buttons.update')}}</button>
                        </div>
                      {{ Form::close() }}
                      <!-- /.card-footer-->
                    </div>
                    <!--/.direct-chat -->
                </section>
                <!-- /.Left col -->
            </div>
        </div><!-- /.container-fluid -->
    </section>
@endsection

@section('after-scripts')
<script src="{{ asset('assets/plugins/tinymce/tinymce.min.js?var='.rand()) }}">
</script>
{!! JsValidator::formRequest('App\Http\Requests\Admin\CourseWeekRequest','#courseModuleEdit') !!}
<style type="text/css">
</style>
<script type="text/javascript">

</script>
@endsection