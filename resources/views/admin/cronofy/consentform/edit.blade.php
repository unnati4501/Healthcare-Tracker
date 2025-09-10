@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.cronofy.consentform.breadcrumb', [
  'mainTitle' => trans('page_title.consentform.edit_consent_form'),
  'breadcrumb' => 'cronofy.consentform.edit'
])
<!-- /.content-header -->
@endsection

@section('content')
    <div class="container-fluid">
        {{ Form::open(['route' => ['admin.cronofy.consent-form.update', $record], 'class' => 'form-horizontal zevo_form_submit', 'method'=>'post','role' => 'form', 'id'=>'updateconsentform']) }}
        <div class="card form-card">
            <div class="card-body">
                @include('admin.cronofy.consentform.form')
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{!! route('admin.cronofy.consent-form.index') !!}">
                        {{ trans('buttons.general.cancel') }}
                    </a>
                    <button class="btn btn-primary" id="zevo_submit_btn" type="submit">
                        {{ trans('buttons.general.save') }}
                    </button>
                </div>
            </div>
        </div>
        {{ Form::close() }}
    </div>
</section>
@include('admin.cronofy.consentform.addeditQuestion-model')
@include('admin.cronofy.consentform.delete-model')
@endsection

<!-- include datatable css -->
@section('after-scripts')
{!! JsValidator::formRequest('App\Http\Requests\Admin\UpdateConsentformRequest','#updateconsentform') !!}
<!-- DataTables -->
<script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/ckeditor5/ckeditor.js?var='.rand()) }}">
</script>
<script src="{{ asset('js/external/external-ckeditor.js?var='.rand()) }}">
</script>
<script id="consent_form_question_data_template" type="text/html">
@include('admin.cronofy.consentform.questions', [
    "key" => ":key", 
    "title" => ":title", 
    "description" => ":description", 
    "id" => ":id"
])
</script>
<script type="text/javascript">
    var url = {
        datatable: `{{ route('admin.cronofy.sessions.get-sessions') }}`,
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    },
    message = {
        question_deleted: `{{ trans('Cronofy.consent_form.message.question_deleted') }}`,
        desc_required: `{{ trans('Cronofy.consent_form.validation.description') }}`,
        desc_length: `{{ trans('Cronofy.consent_form.validation.desc_lengh') }}`,
        title_required: `{{ trans('Cronofy.consent_form.validation.title') }}`,
        title_length: `{{ trans('Cronofy.consent_form.validation.title_lengh') }}`,
    };
</script>
<script src="{{ mix('js/cronofy/consentform/edit.js') }}">
</script>
@endsection