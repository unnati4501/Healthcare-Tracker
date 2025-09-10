@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.challenge_map_library.breadcrumb', [
  'mainTitle' => trans('challengeMap.title.manage'),
  'breadcrumb' => 'challengeMapLibrary.index',
  'create' => true
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer" id="maplibrary-wrap">
                    <div class="table-responsive">
                        <table class="table custom-table" id="mapLibrary">
                            <thead>
                                <tr>
                                    <th class="no-sort th-btn-4">
                                        {{ trans('challengeMap.table.image') }}
                                    </th>
                                    <th>
                                        {{ trans('challengeMap.table.name') }}
                                    </th>
                                    <th>
                                        {{ trans('challengeMap.table.total_distance') }}
                                    </th>
                                    <th>
                                        {{ trans('challengeMap.table.locations') }}
                                    </th>
                                    <th>
                                        {{ trans('challengeMap.table.description') }}
                                    </th>
                                    <th>
                                        {{ trans('challengeMap.table.status') }}
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
@include('admin.challenge_map_library.delete_modal')
@include('admin.challenge_map_library.archive_map_modal')
@include('admin.challenge_map_library.view_map_modal')
@include('admin.challenge_map_library.view_description_model')
@include('admin.challenge_map_library.view_map_location_name')
@endsection

@section('after-scripts')
<script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand()) }}">
</script>
<script src="{{ asset('js/external/jquery.form.min.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    var maxImagesLimit = {{ config('zevolifesettings.challenge_image_library_max_images_limit', 20) }},
    url = {
        datatable: `{{ route('admin.challengeMapLibrary.getMapLibrary') }}`,
        delete: `{{ route('admin.challengeMapLibrary.delete','/') }}`,
        getMapLocation: `{{ route('admin.challengeMapLibrary.getMapLocation','/') }}`,
        archive: `{{ route('admin.challengeMapLibrary.archive','/') }}`,
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    },
    message = {
        deleted: `{{ trans('challengeMap.messages.deleted') }}`,
        somethingWentWrong: `{{ trans('challengeMap.messages.something_wrong_try_again') }}`,
        archived: `{{ trans('challengeMap.messages.archived') }}`,
    },
    data = {
        roleGroup: `{{ $roleGroup }}`,
    };
</script>
<script type="text/javascript">
let poly;
let map;
let locationArray = [];
function initMap() {
    const mapOptions = {
        zoom: 1,
        center: new google.maps.LatLng(0, -180),
        mapTypeId: "terrain",
    };
    const map = new google.maps.Map(document.getElementById("map"), mapOptions);
    const flightPlanCoordinates = locationArray;
    poly = new google.maps.Polyline({
        path: flightPlanCoordinates,
        editable: true,
        strokeColor: "#000",
        strokeOpacity: 1.0,
        strokeWeight: 3,
        map: map,
    });
    poly.setMap(map);
}
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('zevolifesettings.googleMapKey') }}&callback=initMap&v=weekly" async></script>
<script src="{{ mix('js/challengeMap/index.js') }}">
</script>
@endsection
