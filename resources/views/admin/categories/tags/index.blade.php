@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.categories.tags.breadcrumb', [
  'mainTitle' => trans('categories.tags.title.index'),
  'breadcrumb' => Breadcrumbs::render('categoryTags.index'),
  'create' => true
])
<!-- /.content-header -->
@endsection

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="card-table-outer">
                <div class="table-responsive">
                    <table class="table custom-table" id="categoryTagsManagment">
                        <thead>
                            <tr>
                                <th>
                                    {{ trans('categories.tags.table.name') }}
                                </th>
                                <th>
                                    {{ trans('categories.tags.table.total_tags') }}
                                </th>
                                <th class="th-btn-4 no-sort">
                                    {{ trans('categories.tags.table.action') }}
                                </th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('after-scripts')
<script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    let url = {
            tags: `{{ route('admin.categoryTags.getTags') }}`,
        },
        pagination = {
            value: {{ $pagination }},
            previous: `{!! trans('buttons.pagination.previous') !!}`,
            next: `{!! trans('buttons.pagination.next') !!}`,
        };
</script>
<script src="{{ mix('js/categories/tags/index.js') }}">
</script>
@endsection
