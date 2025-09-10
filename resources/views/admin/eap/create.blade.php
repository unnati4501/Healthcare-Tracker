@extends('layouts.app')

@section('after-styles')
@if($isSA)
<link href="{{asset('assets/plugins/tree-multiselect/tree-multiselect.css?var='.rand())}}" rel="stylesheet"/>
@endif
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.eap.breadcrumb',[
    'appPageTitle' => trans('eap.title.add_form_title'),
    'breadcrumb' => 'eap.create',
    'create'     => false,
    'introduction' => false,
])
<!-- /.content-header -->
@endsection
<!-- /.content-header -->
@section('content')
<section class="content">
    <div class="container-fluid">
        {{ Form::open(['route' => 'admin.support.store', 'class' => 'form-horizontal zevo_form_submit', 'method'=>'post','role' => 'form', 'id'=>'EAPAdd', 'files' => true]) }}
        <div class="card form-card">
            <div class="card-body">
                @include('admin.eap.form', ['edit' => false])
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{!! route('admin.support.list') !!}">
                        {{trans('buttons.general.cancel')}}
                    </a>
                    <button class="btn btn-primary" id="zevo_submit_btn" type="submit">
                        {{trans('buttons.general.save')}}
                    </button>
                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div>
</section>
@endsection

@section('after-scripts')
{!! $validator = JsValidator::formRequest('App\Http\Requests\Admin\CreateEAPRequest','#EAPAdd') !!}
<script src="{{asset('assets/plugins/jquery.numeric/jquery.numeric.min.js?var='.rand())}}">
</script>
<script src="{{ asset('assets/plugins/ckeditor5/ckeditor.js?var='.rand()) }}">
</script>
<script src="{{ asset('js/external/external-ckeditor.js?var='.rand()) }}">
</script>
@if($isSA)
<script src="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.js?var='.rand()) }}">
</script>
@endif
<script type="text/javascript">
    var data = {
        isSA: `{!! $isSA !!}`,
        assets_img: `{{asset('assets/dist/img/boxed-bg.png')}}`,
    },
    getDepartment = '{{ route("admin.support.getDepartments") }}',
    message = {
        image_valid_error: `{{trans('eap.message.image_valid_error')}}`,
        image_size_2M_error: `{{trans('eap.message.image_size_2M_error')}}`,
        select_eap_company: `{{trans('eap.form.placeholder.select_eap_company')}}`,
        upload_image_dimension: `{{trans('eap.message.upload_image_dimension')}}`,
        desc_required: `{{ trans('eap.validation.description') }}`,
        desc_length: `The description field may not be greater than 750 characters.`,
    };
</script>
<script src="{{ asset('js/eap/create-edit.js') }}" type="text/javascript">
</script>
@endsection
