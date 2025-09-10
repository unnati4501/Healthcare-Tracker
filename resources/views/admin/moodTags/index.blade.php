@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.moodTags.breadcrumb', [
  'mainTitle' => trans('moods.tags.title.manage'),
  'breadcrumb' => 'moodTags.index',
  'create' => $tagCount < 16
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer" id="moodManagement-wrap">
                    <div class="table-responsive">
                        <table class="table custom-table" id="moodManagement">
                            <thead>
                                <tr>
                                    <th>
                                        {{ trans('moods.tags.table.updated_at') }}
                                    </th>
                                    <th>
                                        {{ trans('moods.tags.table.tags') }}
                                    </th>
                                    <th class="text-center th-btn-2 no-sort">
                                        {{ trans('moods.tags.table.action') }}
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
@include('admin.moodTags.delete_modal')
@endsection
<!-- include datatable css -->
@section('after-scripts')
<!-- DataTables -->
<script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    var url = {
        datatable: `{{ route('admin.moodTags.getMoodTags') }}`,
        delete: `{{ route('admin.moodTags.delete','/') }}`,
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    },
    message = {
        deleted: `{{ trans('moods.tags.messages.deleted') }}`,
        unauthorized: `{{ trans('moods.tags.messages.unauthorized_access') }}`,
        somethingWentWrong: `{{ trans('moods.tags.messages.something_wrong_try_again') }}`,
    };
</script>
<script src="{{ mix('js/moodTags/index.js') }}">
</script>
@endsection
