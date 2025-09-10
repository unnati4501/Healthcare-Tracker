@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.fileimport.breadcrumb',[
    'appPageTitle' => trans('import.title.index_title'),
    'breadcrumb' => 'imports.index',
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        @if($role == 'zevo')
        <div class="nav-tabs-wrap">
            <ul class="nav nav-tabs tabs-line-style" id="myTab" role="tablist">
                <li class="nav-item">
                    <a aria-controls="Users Import" aria-selected="true" class="nav-link active" data-bs-toggle="tab" href="#userImport" id="userImport-tab" role="tab">
                        {{ trans('import.title.users_import') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a aria-controls="Questions Import" aria-selected="false" class="nav-link" data-bs-toggle="tab" href="#questionImport" id="questionImport-tab" role="tab">
                        {{ trans('import.title.questions_import') }}
                    </a>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div aria-labelledby="userImport-tab" class="tab-pane fade show active" id="userImport" role="tabpanel">
                    <div class="text-end mb-4 tab-button">
                        <a class="btn btn-primary" href="{{url('static/user-template.xlsx')}}">
                            <i aria-hidden="true" class="far fa-download me-3 align-middle">
                            </i>
                            {{ trans('import.buttons.simple_file_for_user_import') }}
                        </a>
                    </div>
                    <div class="card">
                        {{ Form::open(['route' => 'admin.imports.store', 'class' => 'form-horizontal', 'method'=>'POST','role' => 'form', 'id'=>'userImportData', 'files' => true]) }}
                        <div class="card-header detailed-header small-gap">
                            <div class="d-flex flex-wrap">
                                {{ Form::hidden('module', 'users') }}
                                <div>
                                    <div class="form-group mb-0">
                                        {{ Form::select('company', $companies, old('company'), ['class' => 'form-control select2','id'=>'company', 'placeholder' => trans('import.filter.select_company'), 'data-placeholder' => trans('import.filter.select_company')]) }}
                                    </div>
                                </div>
                                <div>
                                    <div class="form-group mb-0">
                                        <div class="custom-file">
                                            {{ Form::file('import_file', ['class' => 'custom-file-input form-control', 'id' => 'userImportFile'])}}
                                            <label class="custom-file-label" for="userImportFile">
                                                {{ trans('import.filter.choose_file') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    {{ Form::submit(trans('import.buttons.upload'), ["class" => "btn btn-primary", "style" => "border-radius: 10px;"]) }}
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                        <!-- /.card-header -->
                        <div class="card-body">
                            <div class="card-table-outer" id="userImportManagement-wrap">
                                <div class="table-responsive">
                                    <table class="table custom-table" id="userImportManagement">
                                        <thead>
                                            <tr>
                                                <th class="text-center" style="display: none">
                                                    {{ trans('import.table.updated_at') }}
                                                </th>
                                                <th>
                                                    {{ trans('import.table.company') }}
                                                </th>
                                                <th>
                                                    {{trans('import.table.uploaded_file')}}
                                                </th>
                                                <th>
                                                    {{trans('import.table.validated_file')}}
                                                </th>
                                                <th>
                                                    {{trans('import.table.in_process')}}
                                                </th>
                                                <th>
                                                    {{trans('import.table.processed')}}
                                                </th>
                                                <th>
                                                    {{trans('import.table.impoerted_successfully')}}
                                                </th>
                                                <th>
                                                    {{trans('import.table.file_uploaded_at')}}
                                                </th>
                                                <th class="text-center th-btn-1 no-sort">
                                                    {{trans('import.table.action')}}
                                                </th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!-- /.card-body -->
                    </div>
                </div>
                <div aria-labelledby="questionImport-tab" class="tab-pane fade" id="questionImport" role="tabpanel">
                    <div class="text-end mb-4 tab-button">
                        <a class="btn btn-primary" href="{{url('static/question-template.xlsx')}}">
                            <i aria-hidden="true" class="far fa-download me-3 align-middle">
                            </i>
                            {{ trans('import.buttons.simple_file_for_question_import') }}
                        </a>
                    </div>
                    <div class="card">
                        {{ Form::open(['route' => 'admin.imports.store', 'class' => 'form-horizontal', 'method'=>'POST','role' => 'form', 'id'=>'questionImportData', 'files' => true]) }}
                        <div class="card-header detailed-header small-gap">
                            <div class="d-flex flex-wrap">
                                {{ Form::hidden('module', 'questions') }}
                                <div>
                                    <div class="form-group mb-0">
                                        <div class="custom-file">
                                            {{ Form::file('import_file', ['class' => 'custom-file-input form-control', 'id' => 'questionImportFile', 'autocomplete' => 'off'])}}
                                            <label class="custom-file-label" for="questionImportFile">
                                                {{ trans('import.filter.choose_file') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    {{ Form::submit(trans('import.buttons.upload'),["class"=>"btn btn-primary", "style" => "border-radius: 10px;"]) }}
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                        <!-- /.card-header -->
                        <div class="card-body">
                            <div class="card-table-outer" id="questionImportManagement-wrap">
                                <div class="table-responsive">
                                    <table class="table custom-table" id="questionImportManagement">
                                        <thead>
                                            <tr>
                                                <th style="display: none">
                                                    {{ trans('import.table.updated_at') }}
                                                </th>
                                                <th class="no-sort">
                                                    {{trans('import.table.uploaded_file')}}
                                                </th>
                                                <th class="no-sort">
                                                    {{trans('import.table.validated_file')}}
                                                </th>
                                                <th>
                                                    {{trans('import.table.in_process')}}
                                                </th>
                                                <th>
                                                    {{trans('import.table.processed')}}
                                                </th>
                                                <th>
                                                    {{trans('import.table.impoerted_successfully')}}
                                                </th>
                                                <th>
                                                    {{trans('import.table.file_uploaded_at')}}
                                                </th>
                                                <th class="th-btn-1 no-sort">
                                                    {{trans('import.table.action')}}
                                                </th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!-- /.card-body -->
                    </div>
                </div>
            </div>
            <!-- /.card -->
        </div>
        @else
        <div class="nav-tabs-wrap">
            <ul class="nav nav-tabs tabs-line-style" id="myTab" role="tablist">
                <li class="nav-item">
                    <a aria-controls="Users Import" aria-selected="true" class="nav-link active" data-bs-toggle="tab" id="userImport-tab" role="tab">
                        {{trans('import.title.users_import')}}
                    </a>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div aria-labelledby="userImport-tab" class="tab-pane fade show active" id="userImport" role="tabpanel">
                    <div class="text-end mb-4 tab-button">
                        <a class="btn btn-primary" href="{{url('static/user-template.xlsx')}}" title="Excel Template Format Download">
                            <i aria-hidden="true" class="far fa-download me-3 align-middle">
                            </i>
                            {{trans('import.buttons.simple_file_for_user_import')}}
                        </a>
                    </div>
                </div>
                <div class="card">
                    {{ Form::open(['route' => 'admin.imports.store', 'class' => 'form-horizontal', 'method'=>'POST','role' => 'form', 'id'=>'userImportData', 'files' => true]) }}
                    <div class="card-header detailed-header small-gap">
                        {{ Form::hidden('module', 'users') }}
                        <div class="d-flex flex-wrap">
                            @if($role == 'reseller' && $usercomany->is_reseller)
                            <div>
                                <div class="form-group mb-0">
                                    {{ Form::select('company', $companies, old('company'), ['class' => 'form-control select2','id'=>'company', 'placeholder' => trans('import.filter.select_company'), 'data-placeholder' => trans('import.filter.select_company')]) }}
                                </div>
                            </div>
                            @else
                            <input id="company" name="company" type="hidden" value="{{ $usercomany->id}}"/>
                            @endif
                            <div>
                                <div class="form-group mb-0">
                                    <div class="custom-file">
                                        {{ Form::file('import_file', ['class' => 'pt-1 form-control custom-file-input', 'id' => 'userImportFile', 'autocomplete' => 'off'])}}
                                        <label class="custom-file-label" for="userImportFile">
                                            {{trans('import.filter.choose_file')}}
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div>
                                {{ Form::submit(trans('labels.buttons.upload'),["class"=>"btn btn-primary", "style" => "border-radius: 10px;"]) }}
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                    <div class="card-body">
                        <div class="card-table-outer" id="userImportManagement-wrap">
                            <div class="table-responsive">
                                <table class="table custom-table" id="userImportManagement">
                                    <thead>
                                        <tr>
                                            <th style="display: none">
                                                {{trans('import.table.updated_at')}}
                                            </th>
                                            <th>
                                                {{trans('import.table.company')}}
                                            </th>
                                            <th>
                                                {{trans('import.table.uploaded_file')}}
                                            </th>
                                            <th>
                                                {{trans('import.table.validated_file')}}
                                            </th>
                                            <th>
                                                {{trans('import.table.in_process')}}
                                            </th>
                                            <th>
                                                {{trans('import.table.processed')}}
                                            </th>
                                            <th>
                                                {{trans('import.table.impoerted_successfully')}}
                                            </th>
                                            <th>
                                                {{trans('import.table.file_uploaded_at')}}
                                            </th>
                                            <th class="th-btn-1 no-sort">
                                                {{trans('import.table.action')}}
                                            </th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    <!-- /.row -->
    <!-- /.container-fluid -->
</section>
<!-- Delete Model Popup -->
@include('admin.fileimport.delete-modal')
@endsection
<!-- include datatable css -->
@section('after-scripts')
<!-- DataTables -->
{!! JsValidator::formRequest('App\Http\Requests\Admin\FileImportUserRequest','#userImportData') !!}
{!! JsValidator::formRequest('App\Http\Requests\Admin\FileImportQuestionRequest','#questionImportData') !!}
<script src="{{asset('assets/plugins/moment/moment.min.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/moment/moment-timezone-with-data-10-year-range.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script src="{{ asset('js/external/jquery.form.min.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    var timezone = `{{ $timezone }}`,
    date_format = `{{ $date_format }}`,
    getImportsRoute = `{{ route('admin.imports.getImports') }}`,
    deleteImportRoute = `{{ route('admin.imports.delete','/') }}`,
    visibleCompany = {{ (($role == 'zevo' || ($role == 'reseller' && $usercomany->is_reseller)) ? 'true' : 'false') }};

var pagination = {
    value: {{ $pagination }},
    previous: `{!! trans('buttons.pagination.previous') !!}`,
    next: `{!! trans('buttons.pagination.next') !!}`,
},
message = {
    delete_error: `{{ trans('import.message.delete_error')}} `,
    file_deleted: `{{ trans('import.message.file_deleted')}} `,
    uploading_valid_excel_file: `{{ trans('import.message.uploading_valid_excel_file')}} `,
    something_went_wrong: `{{ trans('import.message.something_went_wrong')}} `,
    processing_on_file: `{{ trans('import.message.processing_on_file')}} `,
    uploading_file: `{{ trans('import.message.uploading_file')}} `,
    choose_file: `{{trans('import.filter.choose_file')}}`,
};
</script>
<script src="{{ mix('js/fileImports.js') }}">
</script>
@endsection
