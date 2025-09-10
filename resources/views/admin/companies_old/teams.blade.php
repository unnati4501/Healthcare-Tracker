@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.companies_old.breadcrumb', [
    'mainTitle' => __('Teams of ' . $company->name),
    'breadcrumb' => Breadcrumbs::render('companiesold.teams'),
    'back' => true,
    'companyType' => $companyType
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <!-- .grid -->
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer">
                    <div class="table-responsive">
                        <table class="table custom-table" id="teamManagment">
                            <thead>
                                <tr>
                                    <th>
                                        Team Code
                                    </th>
                                    <th>
                                        Team Name
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.grid -->
    </div>
</section>
@endsection

@section('after-scripts')
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script type="text/javascript">
    var url = `{{ route("admin.companies.getCompanyTeams", $company->id) }}`,
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    };
</script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#teamManagment').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: url,
                data: {
                    status: 1,
                    getQueryString: window.location.search
                },
            },
            columns: [{
                data: 'code',
                name: 'code'
            }, {
                data: 'name',
                name: 'name'
            }],
            paging: true,
            dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
            pageLength: pagination.value,
            lengthChange: false,
            searching: false,
            ordering: true,
            order: [
                [0, 'desc']
            ],
            info: true,
            autoWidth: false,
            columnDefs: [{
                targets: 'no-sort',
                orderable: false,
            }],
            language: {
                paginate: {
                    previous: pagination.previous,
                    next: pagination.next,
                }
            },
            // dom: '<<tr><"pagination-wrap"ip>',
            // initComplete: (settings, json) => {
            //     $('.pagination-wrap').appendTo(".card-table-outer");
            // },
        });
    });
</script>
@endsection
