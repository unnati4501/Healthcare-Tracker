@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
@endsection

@section('content')
@include('admin.department.location.breadcrumb', [
    'appPageTitle' => trans('department.location.title.department_location_title'),
    'breadcrumb' => 'location.index'
])
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer">
                    <div class="table-responsive">
                        <table class="table custom-table" id="departmentLocation">
                            <thead>
                                <tr>
                                    <th class="hidden">
                                        {{trans('department.location.table.updated_at')}}
                                    </th>
                                    <th>
                                        {{trans('department.location.table.locationname')}}
                                    </th>
                                    <th>
                                        {{trans('department.location.table.country')}}
                                    </th>
                                    <th>
                                        {{trans('department.location.table.state')}}
                                    </th>
                                    <th>
                                        {{trans('department.location.table.time_zone')}}
                                    </th>
                                    <th>
                                        {{trans('department.location.table.address')}}
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
    </div>
</section>
@endsection

@section('after-scripts')
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script type="text/javascript">
    var url = {
        getLocationList: `{{ route('admin.departments.getLocationList', $department->id) }}`,
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    };
</script>
<script src="{{ asset('js/department/location-index.js') }}" type="text/javascript">
</script>
@endsection
