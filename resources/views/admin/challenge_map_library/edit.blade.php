@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/tree-multiselect/tree-multiselect.css?var='.rand())}}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@if($routeName == 'admin.challengeMapLibrary.step-2')
@include('admin.challenge_image_library.breadcrumb', [
  'mainTitle' => trans('challengeMap.title.add'),
  'breadcrumb' => 'challengeMapLibrary.create'
])
@else
@include('admin.challenge_image_library.breadcrumb', [
  'mainTitle' => trans('challengeMap.title.edit'),
  'breadcrumb' => 'challengeMapLibrary.edit'
])
@endif
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card form-card">
            {{ Form::open(['route' => [$routeUrl, $record->id], 'class' => 'form-horizontal zevo_form_submit', 'method'=>'PATCH', 'role' => 'form', 'id'=>'EditChallengeMap', 'files' => true]) }}
            <div class="card-body">
                @include('admin.challenge_map_library.form', ['edit' => true])
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{!! route('admin.challengeMapLibrary.index') !!}">
                        {{ trans('buttons.general.cancel') }}
                    </a>
                    @if($routeName == 'admin.challengeMapLibrary.step-2')
                    <button class="btn btn-primary" id="zevo_update_btn" type="submit">
                        {{ trans('buttons.general.save') }}
                    </button>
                    @else
                    @if($activeAttechCount <= 0)
                    <button class="btn btn-primary" id="zevo_update_btn" type="submit">
                        {{ trans('buttons.general.update') }}
                    </button>
                    @endif
                    @endif
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</section>
@include('admin.challenge_map_library.delete_latlong_modal')
@endsection

@section('after-scripts')
{!! $validator = JsValidator::formRequest('App\Http\Requests\Admin\UpdateChallengeMapRequest','#EditChallengeMap') !!}
<script src="{{ asset('assets/plugins/tree-multiselect/tree-multiselect.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/moment/moment.min.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/jquery.numeric/jquery.numeric.min.js?var='.rand())}}">
</script>
<script src="{{ asset('js/external/jquery.form.min.js?var='.rand()) }}">
</script>
<script id="map_location_latlong_template" type="text/html">
    @include('admin.challenge_map_library.map_location_latlong', ["id" => ":id", "lat_long" => ":lat_long", "locationId" => ":locationId", "deletedId" => ":deletedId", "mapeditId" => ":mapeditId", "trId" => ":trId", "recordsId" => ":recordsId"])
</script>
<script type="text/javascript">
    var url = {
        mapIndex: `{{ route('admin.challengeMapLibrary.index') }}`,
        deleteLocation: `{{ route('admin.challengeMapLibrary.deletelocation','/') }}`,
        getLocation: `{{ route('admin.challengeMapLibrary.getLocation','/') }}`,
        storeProperty: `{{ route('admin.challengeMapLibrary.store-property') }}`,
        commonImage: `{{ asset('assets/dist/img/boxed-bg.png') }}`,
        storeLatLong: `{{ route('admin.challengeMapLibrary.store-lat-long') }}`,
    },
    message = {
        image_valid_error: `{{trans('challengeMap.messages.image_valid_error')}}`,
        image_size_2M_error: `{{trans('challengeMap.messages.image_size_2M_error')}}`,
        upload_image_dimension: `{{ trans('challengeMap.messages.upload_image_dimension') }}`,
        deleted: `{{ trans('challengeMap.messages.deleted_location') }}`,
        somethingWentWrong: `{{ trans('challengeMap.messages.something_wrong_try_again') }}`,
    },
    validation = {
        location_required: `{{trans('challengeMap.validation.location_required')}}`,
        location_greater_char: `{{trans('challengeMap.validation.location_greater_char')}}`,
        location_type_required: `{{trans('challengeMap.validation.location_type_required')}}`,
        distance_required: `{{trans('challengeMap.validation.distance_required')}}`,
        distance_number: `{{trans('challengeMap.validation.distance_number')}}`,
        property_upload_required: `{{trans('challengeMap.validation.property_upload_required')}}`,
        steps_required: `{{trans('challengeMap.validation.steps_required')}}`,
        steps_number: `{{trans('challengeMap.validation.steps_number')}}`,
        steps_valid_number: `{{trans('challengeMap.validation.steps_valid_number')}}`,
        distance_valid_number: `{{trans('challengeMap.validation.distance_valid_number')}}`,
    },
    data = {
        steps: `{{ config('zevolifesettings.steps') }}`,
        activeAttechCount: `{{ $activeAttechCount }}`,
    };
</script>
<script src="{{ mix('js/challengeMap/edit.js') }}">
</script>
<script type="text/javascript">
let poly;
let map;
let locationArray = [];
function initMap1() {
    const mapOptions = {
        zoom: 1,
        center: new google.maps.LatLng(0, -180),
        mapTypeId: "terrain",
    };
    const map = new google.maps.Map(document.getElementById("map"), mapOptions);
    @foreach($mapProperties as $value)
        locationArray.push(new google.maps.LatLng({{$value['latLong']}}));
    @endforeach
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
    if(data.activeAttechCount <= 0) {
        map.addListener("click", addLatLng);
    }
    function addLatLng(event) {
        $('.page-loader-wrapper').show();
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
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'PATCH',
            url: url.storeLatLong,
            crossDomain: true,
            cache: false,
            data: {
               'lat_long': latLong,
               'map_id': $('#map_id').val(),
           },
        }).done(function(data) {
            if (data.status == 1) {
                let locationId = 'location_'+locationArray.length;
                let trId = 'tr_'+locationArray.length;
                var template = $('#map_location_latlong_template').text().trim().replace(":id", locationArray.length).replace(":locationId", locationId).replace(":lat_long", latLong).replace(":deletedId", 'mapdelete_'+locationArray.length).replace(":mapeditId", 'mapedit_'+locationArray.length).replace(":trId", trId).replaceAll(':recordsId', data.propertiesId);
                $('#location-table').append(template.trim());
                $('#totalLocations').val(locationArray.length);
                toastr.success(data.data);
            } else {
                toastr.error(data.data);
            }
            $('.page-loader-wrapper').hide();
        });
    }
}
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
}
</script>
<script async="" src="https://maps.googleapis.com/maps/api/js?key={{ config('zevolifesettings.googleMapKey') }}&callback=initMap1&v=weekly">
</script>
@endsection
