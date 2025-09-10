@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.challenge_image_library.breadcrumb', [
  'mainTitle' => trans('challengeLibrary.title.manage'),
  'breadcrumb' => 'challengeImageLibrary.index',
  'create' => true
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card search-card">
            <div class="card-body pb-0">
                {{ Form::open(['route' => 'admin.challengeImageLibrary.index', 'class' => 'form-horizontal', 'method'=>'get','role' => 'form']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::select('target_type', $target_type, request()->get('target_type'), ['class' => 'form-control select2', 'id'=>'target_type', 'autocomplete' => 'off', 'placeholder' => "", 'data-placeholder' => trans('challengeLibrary.filter.target'), 'data-allow-clear' => 'true']) }}
                        </div>
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{ trans('buttons.general.apply') }}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('admin.challengeImageLibrary.index') }}">
                            <i class="far fa-times">
                            </i>
                        </a>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer" id="CILManagment-wrap">
                    <div class="table-responsive">
                        <table class="table custom-table" id="CILManagment">
                            <thead>
                                <tr>
                                    <th class="no-sort th-btn-4">
                                        {{ trans('challengeLibrary.table.image') }}
                                    </th>
                                    <th>
                                        {{ trans('challengeLibrary.table.target') }}
                                    </th>
                                    <th class="no-sort th-btn-4">
                                        {{ trans('challengeLibrary.table.action') }}
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@include('admin.challenge_image_library.delete_modal')
@include('admin.challenge_image_library.upload_modal')
@endsection

@section('after-scripts')
{!! $validator = JsValidator::formRequest('App\Http\Requests\Admin\AddBulkChallengeImageLibRequest','#bulkUploadImagesFrm') !!}
<script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('js/external/jquery.form.min.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    var maxImagesLimit = {{ config('zevolifesettings.challenge_image_library_max_images_limit', 20) }},
    url = {
        datatable: `{{ route('admin.challengeImageLibrary.getImages') }}`,
        delete: `{{ route('admin.challengeImageLibrary.delete','/') }}`,
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    },
    message = {
        deleted: `{{ trans('challengeLibrary.messages.deleted') }}`,
        somethingWentWrong: `{{ trans('challengeLibrary.messages.something_wrong_try_again') }}`,
        image_valid_error: `{{ trans('challengeLibrary.messages.image_valid_error') }}`,
        image_size_2M_error: `{{ trans('challengeLibrary.messages.image_size_2M_error') }}`,
        upload_image_dimension: '{{ trans('challengeLibrary.messages.upload_image_dimension') }}',
    };
</script>
<script src="{{ mix('js/challengeLibrary/index.js') }}">
</script>
@endsection
