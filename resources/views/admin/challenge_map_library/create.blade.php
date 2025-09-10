@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/tree-multiselect/tree-multiselect.css?var='.rand())}}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.challenge_image_library.breadcrumb', [
  'mainTitle' => trans('challengeMap.title.add'),
  'breadcrumb' => 'challengeMapLibrary.create'
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card form-card">
        {{ Form::open(['route' => 'admin.challengeMapLibrary.store', 'class' => 'form-horizontal zevo_form_submit', 'method'=>'post','role' => 'form', 'id'=>'AddChallengeMap', 'files' => true]) }}
            <div class="card-body">
                @include('admin.challenge_map_library.form', ['edit' => false])
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{!! route('admin.challengeMapLibrary.index') !!}">
                        {{ trans('buttons.general.cancel') }}
                    </a>
                    <button class="btn btn-primary w-auto" id="zevo_submit_btn" type="submit"><span class="align-middle"> {{ trans('buttons.general.saveandnext') }} </span><i class="far fa-arrow-right ms-2 align-middle d-none d-md-inline-block"></i></button>
                </div>
            </div>
        {{ Form::close() }}
        </div>
    </div>
</section>
@include('admin.challenge_map_library.delete_latlong_modal')
@endsection

@section('after-scripts')
{!! $validator = JsValidator::formRequest('App\Http\Requests\Admin\CreateChallengeMapRequest','#AddChallengeMap') !!}
</script>
<script src="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.js?var='.rand()) }}">
</script>
<script id="map_location_latlong_template" type="text/html">
    @include('admin.challenge_map_library.map_location_latlong', ["id" => ":id", "lat_long" => ":lat_long", "locationId" => ":locationId", "deletedId" => ":deletedId", "mapeditId" => ":mapeditId", "trId" => ":trId", "recordsId" => ":recordsId"])
</script>
<script type="text/javascript">
    var url = {
        mapIndex: `{{ route('admin.challengeMapLibrary.index') }}`,
        deleteLocation: `{{ route('admin.challengeMapLibrary.deletelocation','/') }}`,
    },
    message = {
        image_valid_error: `{{trans('challengeMap.messages.image_valid_error')}}`,
        image_size_2M_error: `{{trans('challengeMap.messages.image_size_2M_error')}}`,
        upload_image_dimension: `{{ trans('challengeMap.messages.upload_image_dimension') }}`,
        deleted: `{{ trans('challengeMap.messages.deleted_location') }}`,
        somethingWentWrong: `{{ trans('challengeMap.messages.something_wrong_try_again') }}`,
    };
</script>
<script src="{{ mix('js/challengeMap/create.js') }}">
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
        strokeColor: "#000000",
        strokeOpacity: 1.0,
        strokeWeight: 3,
        map: map,
    });
    poly.setMap(map);
    map.addListener("click", addLatLng);

    function addLatLng(event) {
        const path = poly.getPath();
        path.push(event.latLng);
        new google.maps.Marker({
            position: event.latLng,
            title: "#" + path.getLength(),
            map: map,
        });
        locationArray.push(event.latLng.toJSON());
        $('#mapDiv').attr('class', 'col-lg-8');
        let latLong = event.latLng.toJSON().lat + ',' + event.latLng.toJSON().lng;
        let locationId = 'location_'+locationArray.length;
        let trId = 'tr_'+locationArray.length;
        var template = $('#map_location_latlong_template').text().trim().replace(":id", locationArray.length).replace(":locationId", locationId).replace(":lat_long", latLong).replace(":deletedId", 'mapdelete_'+locationArray.length).replace(":mapeditId", 'mapedit_'+locationArray.length).replace(":trId", trId).replaceAll(':recordsId', 0);
        $('#location-table').append(template.trim());
        $('#totalLocations').val(locationArray.length);
    }
}
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('zevolifesettings.googleMapKey') }}&callback=initMap&v=weekly" async></script>
@endsection