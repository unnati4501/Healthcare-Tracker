@extends('layouts.app')

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.companies.dt-banners.breadcrumb', [
    'mainTitle' => trans('company.dt_banners.title.add'),
    'breadcrumb' => Breadcrumbs::render('companies.dt-banners.create', [$companyType, $company->id]),
    'backToCompany' => false,
    'back' => true
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        {{ Form::open(['route' => ['admin.companies.storeBanner', [$companyType, $company->id]], 'class' => 'form-horizontal zevo_form_submit', 'method' => 'post', 'role' => 'form', 'id' => 'bannerAdd', 'files' => true]) }}
        <div class="card form-card">
            <div class="card-body">
                @include('admin.companies.dt-banners.form', ['edit' => false])
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{{ route('admin.companies.digitalTherapyBanners', [$companyType, $company->id]) }}">
                        {{ trans('buttons.general.cancel') }}
                    </a>
                    <button class="btn btn-primary" id="zevo_submit_btn" onclick="formSubmit()" type="submit">
                        {{ trans('buttons.general.save') }}
                    </button>
                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div>
</section>
@endsection

@section('after-scripts')
<script src="{{ asset('assets/plugins/tinymce/tinymce.min.js?var='.rand()) }}"></script>
{!! JsValidator::formRequest('App\Http\Requests\Admin\CreateDTBannerRequest','#bannerAdd') !!}
<script type="text/javascript">
    var messages = {
        image_valid_error: `{{trans('company.messages.image_valid_error')}}`,
        image_size_2M_error: `{{trans('company.messages.image_size_2M_error')}}`,
        upload_image_dimension: `{{ trans('company.messages.upload_image_dimension') }}`,
        choosefile            : `{{ trans('company.dt_banners.form.placeholder.choose_file') }}`,
    };
</script>
<script src="{{ mix('js/company/dtBanner/create.js') }}"></script>
@endsection