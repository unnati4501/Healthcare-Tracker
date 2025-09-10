@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.categories.tags.breadcrumb', [
    'mainTitle' => trans('categories.tags.title.view', ['category' => $category->name]),
    'breadcrumb' => Breadcrumbs::render('categoryTags.view'),
    'back' => true
])
<!-- /.content-header -->
@endsection

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="card-table-outer">
                <div class="table-responsive">
                    <table class="table custom-table" id="tagsManagment">
                        <thead>
                            <tr>
                                <th>
                                    {{ trans('categories.tags.view.table.tag_name') }}
                                </th>
                                <th>
                                    {{ trans('categories.tags.view.table.mapped_content') }}
                                </th>
                                <th class="th-btn-2 no-sort">
                                    {{ trans('categories.tags.view.table.actions') }}
                                </th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div category="dialog" class="modal fade" data-id="0" id="delete-model-box" tabindex="-1">
    <div category="document" class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    {{ trans('categories.tags.view.modals.delete.title') }}
                </h5>
                <button aria-label="Close" class="close" data-bs-dismiss="modal" type="button">
                    <i class="fal fa-times">
                    </i>
                </button>
            </div>
            <div class="modal-body">
                <p class="m-0">
                    {{ trans('categories.tags.view.modals.delete.message') }}
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-primary" data-bs-dismiss="modal" type="button">
                    {{ trans('buttons.general.cancel') }}
                </button>
                <button class="btn btn-primary" id="delete-model-box-confirm" type="button">
                    {{ trans('buttons.general.delete') }}
                </button>
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
            tags: `{{ route('admin.categoryTags.getCategoryTags', [$category->id]) }}`,
            delete: `{{ route('admin.categoryTags.delete', [':id']) }}`,
        },
        pagination = {
            value: {{ $pagination }},
            previous: `{!! trans('buttons.pagination.previous') !!}`,
            next: `{!! trans('buttons.pagination.next') !!}`,
        },
        messages = {!! json_encode(trans('categories.tags.messages')) !!};
</script>
<script src="{{ mix('js/categories/tags/view.js') }}">
</script>
@endsection
