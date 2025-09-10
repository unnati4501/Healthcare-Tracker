@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.cronofy.consentform.breadcrumb', [
  'mainTitle' => trans('page_title.consentform.index'),
  'breadcrumb' => 'cronofy.consentform.index'
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer" id="consentFormManagement-wrap">
                    <div class="table-responsive">
                        <table class="table custom-table" id="consentFormManagement">
                            <thead>
                                <tr>
                                    <th>
                                        {{ trans('Cronofy.consent_form.table.order') }}
                                    </th>
                                    <th>
                                        {{ trans('Cronofy.consent_form.table.title') }}
                                    </th>
                                    <th>
                                        {{ trans('Cronofy.consent_form.table.category') }}
                                    </th>
                                    <th class="text-center th-btn-3 no-sort">
                                        {{ trans('Cronofy.consent_form.table.action') }}
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
        datatable: `{{ route('admin.cronofy.consent-form.getConsents') }}`,
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    };
</script>
<script src="{{ mix('js/cronofy/consentform/index.js') }}">
</script>
@endsection
