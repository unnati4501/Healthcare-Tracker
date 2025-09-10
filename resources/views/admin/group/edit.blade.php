@extends('layouts.app')

@section('after-styles')
    <link href="{{asset('assets/plugins/tree-multiselect/tree-multiselect.css?var='.rand())}}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.group.breadcrumb',[
    'appPageTitle' => trans('group.title.edit_form_title'),
    'breadcrumb' => 'group.edit',
    'create'     => false,
    'back'       => false,
    'edit'       => false,
])
<!-- /.content-header -->
@endsection
@section('content')
    <section class="content">
        <div class="container-fluid">
            <div class="card form-card">
                {{ Form::open(['route' => ['admin.groups.update',$id], 'class' => 'form-horizontal zevo_form_submit', 'method'=>'PATCH','role' => 'form', 'id'=>'groupEdit','files' => true]) }}
                <div class="card-body">
                    @include('admin.group.form', ['edit'=>true])
                </div>
                {{ Form::hidden('string', $string) }}
                <div class="card-footer">
                    <div class="save-cancel-wrap">
                        <a class="btn btn-outline-primary" href="{!! route('admin.groups.index').$string !!}">{{trans('buttons.general.cancel')}}</a>
                        <button type="submit" class="btn btn-primary" id="zevo_submit_btn">{{trans('buttons.general.update')}}</button>
                    </div>
                </div>
              {{ Form::close() }}
            </div>
        </div>
    </section>
@endsection

@section('after-scripts')
<script src="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.js?var='.rand()) }}"></script>
{!! JsValidator::formRequest('App\Http\Requests\Admin\EditGroupRequest','#groupEdit') !!}
<script type="text/javascript">
    var defaultCourseImg = `{{ asset('assets/dist/img/placeholder-img.png') }}`;
    var message = {
        image_valid_error:  `{{trans('group.message.image_valid_error')}}`,
        image_size_2M_error: `{{trans('group.message.image_size_2M_error')}}`,
        upload_image_dimension: `{{trans('group.message.upload_image_dimension')}}`
    };
</script>
<script src="{{ asset('js/group/create-edit.js') }}" type="text/javascript">
</script>
@endsection