@extends('layouts.app')

@section('after-styles').
<link href="{{asset('assets/plugins/tree-multiselect/tree-multiselect.css?var='.rand())}}" rel="stylesheet"/>
@endsection

@section('content')
@include('admin.course.week.breadcrumb',['mainTitle'=>trans('labels.course.add_module_title'), 'back' => 'course_week','edit' => false])
<section class="content">
    <div class="container-fluid">
        <!-- Main row -->
        <div class="row">
            <!-- Left col -->
            <section class="col-lg-12">
                <!-- DIRECT CHAT -->
                <div class="card">
                    <!-- /.card-header -->
                    {{ Form::open(['route' => ['admin.courses.storeModule', $course->getKey()], 'class' => 'form-horizontal zevo_form_submit', 'method'=>'post','role' => 'form', 'id'=>'courseModuleAdd']) }}
                    <div class="card-body">
                        <div class="row">
                            @include('admin.course.week.form')
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer border-top text-center">
                        <a class="btn btn-effect btn-outline-secondary me-2 mm-w-100" href="{!! route('admin.courses.manageModules', $course->getKey()) !!}">
                            {{trans('labels.buttons.cancel')}}
                        </a>
                        <button id="zevo_submit_btn" class="btn btn-primary btn-effect mm-w-100" onclick="formSubmit();" type="submit">
                            {{trans('labels.buttons.save')}}
                        </button>
                    </div>
                    {{ Form::close() }}
                    <!-- /.card-footer-->
                </div>
                <!--/.direct-chat -->
            </section>
            <!-- /.Left col -->
        </div>
    </div>
    <!-- /.container-fluid -->
</section>
@endsection

@section('after-scripts')

{!! JsValidator::formRequest('App\Http\Requests\Admin\CourseWeekRequest','#courseModuleAdd') !!}
<style type="text/css">
</style>
<script type="text/javascript">

</script>
@endsection
