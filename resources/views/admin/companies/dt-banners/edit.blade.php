@extends('layouts.app')

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.companies.dt-banners.breadcrumb', [
    'mainTitle' => trans('company.dt_banners.title.edit'),
    'breadcrumb' => Breadcrumbs::render('companies.dt-banners.edit', [$companyType, $company->id]),
    'back' => true,
    'backToCompany' => false,
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card form-card">
            {{ Form::open(['route' => ['admin.companies.updateBanner', [$companyType, $record->id]], 'class' => 'form-horizontal zevo_form_submit', 'method' => 'PATCH', 'role' => 'form', 'id' => 'bannerEdit', 'files' => true]) }}
            <div class="card-body">
                @include('admin.companies.dt-banners.form', ['edit' => true])
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{{ route('admin.companies.digitalTherapyBanners', [$companyType, $record->company_id]) }}">
                        {{ trans('labels.buttons.cancel') }}
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
<script src="{{ asset('assets/plugins/tinymce/tinymce.min.js?var='.rand()) }}"></script>
{!! JsValidator::formRequest('App\Http\Requests\Admin\EditDTBannerRequest','#bannerEdit') !!}
<script type="text/javascript">
    var messages = {
        image_valid_error:      `{{trans('company.messages.image_valid_error')}}`,
        image_size_2M_error:    `{{trans('company.messages.image_size_2M_error')}}`,
        upload_image_dimension: `{{ trans('company.messages.upload_image_dimension') }}`,
        choosefile            : `{{ trans('company.dt_banners.form.placeholder.choose_file') }}`,
    };
</script>
<script src="{{ mix('js/company/dtBanner/edit.js') }}"></script>
@endsection