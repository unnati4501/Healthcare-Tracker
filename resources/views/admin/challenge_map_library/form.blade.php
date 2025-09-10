<div class="card-inner">
    <div class="row">
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('image', trans('challengeMap.form.labels.image')) }}
                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('challenge_map.image') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
                <div class="custom-file custom-file-preview">
                    <label class="file-preview-img d-flex" for="image_preview">
                        <img id="image_preview" src="{{ ($record->image ?? asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
                    </label>
                    {{ Form::file('image', ['class' => 'custom-file-input form-control', 'id' => 'image', 'data-width' => config('zevolifesettings.imageConversions.challenge_map.image.width'), 'data-height' => config('zevolifesettings.imageConversions.challenge_map.image.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.challenge_map.image'), 'data-previewelement' => '#image_preview', 'accept' => 'image/*'])}}
                    {{ Form::label('image', ((!empty($record) && !empty($record->getFirstMediaUrl('image'))) ? $record->getFirstMedia('image')->name : trans('webinar.form.placeholder.choose_file')), ["class" => "custom-file-label"]) }}
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('name', trans('challengeMap.form.labels.name')) }}
                {{ Form::text('name', old('name', ($record->name ?? null)), ['class' => 'form-control', 'placeholder' => trans('challengeMap.form.placeholders.name'), 'id' => 'name', 'autocomplete' => 'off', 'data-selectOnClose' => false, 'data-selectonclose' => false]) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('description', trans('challengeMap.form.labels.description')) }}
                {{ Form::textarea('description', old('description', ($record->description ?? null)), ['id' => 'description', 'rows' => 3, 'class' => 'form-control', 'placeholder' => trans('challengeMap.form.placeholders.description'), 'spellcheck' => 'false']) }}
            </div>
        </div>
        @if($edit)
        <div class="col-lg-8" id="mapDiv">
            {{ Form::label('description', trans('challengeMap.form.labels.add_locations')) }}
            <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ trans('challengeMap.messages.add_locations') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
            <div class="map-section" id="map">
            </div>
        </div>
        @else
        <div class="col-lg-12" id="mapDiv">
            {{ Form::label('description', trans('challengeMap.form.labels.add_locations')) }}
            <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ trans('challengeMap.messages.add_locations') }}">
                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                    </i>
                </span>
            <div class="map-section" id="map">
            </div>
        </div>
        @endif
        <div class="col-lg-4">
            <div class="location-scrollbar custom-scrollbar">
                <div class="table-responsive">
                    <table class="table custom-table no-hover location-table gap-adjust no-border" id="location-table">
                        <tbody>
                            @if(!empty($mapProperties))
                            @foreach($mapProperties as $value)
                                @include('admin.challenge_map_library.map_location_latlong', ["id" => $value['key'], "lat_long" => $value['latLong'], "locationId" => 'location_'.$value['key'], "deletedId" => 'mapdelete_'.$value['key'], "mapeditId" => 'mapedit_'.$value['key'], "recordsId" => $value['id'], "trId" => 'tr_'.$value['key']])
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @if($edit)
        <div class="col-lg-12">
            <div class="property-card mt-5">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-12">
                            <h3 class="card-inner-title">{{ trans('challengeMap.form.labels.set_properties') }}</h3>
                            <h6 class="text-primary mb-3">{{ trans('challengeMap.form.labels.location') }} <label id="property-label-id">1</label></h6>
                        </div>
                        <div class="col-lg-6 col-xl-4">
                            {{ Form::hidden('property_id', old('property_id', ($mapProperties[0]['id'] ?? null)), array('id' => 'property_id')) }}
                            {{ Form::hidden('map_id', old('map_id', ($record->id ?? null)), array('id' => 'map_id')) }}
                            {{ Form::hidden('num', old('num', 1), array('id' => 'num')) }}
                            {{ Form::hidden('base64Img', old('base64Img'), array('id' => 'base64Img')) }}
                            <div class="form-group">
                                {{ Form::label('Location Type', trans('challengeMap.form.labels.location_type')) }}
                                {{ Form::select('location_type', $locationsType, old('location_type', 1), ['class' => 'form-control select2', 'id' => 'location_type', 'placeholder' => trans('challengeMap.form.placeholders.location_type'), 'data-placeholder' => trans('challengeMap.form.placeholders.location_type'), 'disabled' => true] ) }}
                            </div>
                        </div>
                        <div class="col-lg-6 col-xl-4 location_name_div">
                            <div class="form-group">
                                {{ Form::label('Location Name', trans('challengeMap.form.labels.location_name')) }}
                                {{ Form::text('location_name', old('location_name', ($mapProperties[0]['locationName'] ?? null)), ['class' => 'form-control', 'placeholder' => trans('challengeMap.form.placeholders.location_name'), 'id' => 'location_name', 'autocomplete' => 'off']) }}
                            </div>
                        </div>
                        <div class="col-lg-6 col-xl-4">
                            <div class="form-group">
                                {{ Form::label('Lat/Long', trans('challengeMap.form.labels.lat_long')) }}
                                {{ Form::text('lat_long', old('lat_long', ($mapProperties[0]['latLong'] ?? null)), ['class' => 'form-control', 'placeholder' => trans('challengeMap.form.placeholders.lat_long'), 'id' => 'lat_long', 'autocomplete' => 'off', 'readonly' => true]) }}
                            </div>
                        </div>
                        <div class="col-lg-6 col-xl-4 location_upload_files_div">
                            <div class="form-group">
                                {{ Form::label('Upload Files', trans('challengeMap.form.labels.upload_files')) }}
                                <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('challenge_map.property') }}">
                                    <i aria-hidden="true" class="far fa-info-circle text-primary">
                                    </i>
                                </span>
                                <div class="custom-file custom-file-preview">
                                    <label class="file-preview-img d-flex" for="propertyimage_preview">
                                        <img id="propertyimage_preview" src="{{ ($mapProperties[0]['propertyImage']->image ?? asset('assets/dist/img/boxed-bg.png')) }}" width="200"/>
                                    </label>
                                    {{ Form::file('propertyimage', ['class' => 'custom-file-input form-control', 'id' => 'propertyimage', 'data-width' => config('zevolifesettings.imageConversions.challenge_map.property.width'), 'data-height' => config('zevolifesettings.imageConversions.challenge_map.property.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.challenge_map.property'), 'data-previewelement' => '#propertyimage_preview', 'accept' => 'image/*'])}}
                                    {{ Form::label('propertyimage', ((!empty($mapProperties[0]['propertyImage']) && !empty($mapProperties[0]['propertyImage']->getFirstMediaUrl('propertyimage'))) ? $mapProperties[0]['propertyImage']->getFirstMedia('propertyimage')->name : trans('webinar.form.placeholder.choose_file')), ["class" => "custom-file-label propertyimage-label"]) }}
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-xl-4" id="distanceDiv" style="display: none">
                            <div class="form-group">
                                {{ Form::label('Distance from previous location', trans('challengeMap.form.labels.distance_previous_location')) }}
                                <div class="input-group">
                                    {{ Form::text('distance_location', old('distance_location'), ['class' => 'form-control', 'placeholder' => trans('challengeMap.form.placeholders.distance'), 'id' => 'distance', 'autocomplete' => 'off']) }}
                                    <span class="input-group-text">{{ trans('challengeMap.form.labels.km') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-xl-4" id="stepsDiv" style="display: none">
                            <div class="form-group">
                                {{ Form::label('Steps', trans('challengeMap.form.labels.steps')) }}
                                {{ Form::text('steps', old('steps'), ['class' => 'form-control', 'placeholder' => trans('challengeMap.form.placeholders.steps'), 'id' => 'steps', 'autocomplete' => 'off', 'readonly' => true]) }}
                            </div>
                        </div>
                        @if($activeAttechCount <= 0)
                        <div class="col-lg-12">
                            <button class="btn btn-primary" id="save-property" type="button">{{ trans('buttons.general.save') }}</button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    <div class="mt-5">
        <div class="row">
            <div class="col-lg-6 col-xl-4">
                <div class="form-group">
                    {{ Form::label('Total Loctaions', trans('challengeMap.form.labels.total_locations')) }}
                    {{ Form::text('total_locations', old('total_locations', ($record->total_location ?? null)), ['class' => 'form-control', 'placeholder' => trans('challengeMap.form.placeholders.total_locations'), 'id' => 'totalLocations', 'autocomplete' => 'off', 'readonly' => true]) }}
                </div>
            </div>
            @if($edit)
            <div class="col-lg-6 col-xl-4">
                <div class="form-group ">
                    {{ Form::label('Total Distance', trans('challengeMap.form.labels.total_distance')) }}
                    <div class="input-group">
                        {{ Form::text('total_distance', old('total_distance', ($record->total_distance ?? null)), ['class' => 'form-control', 'placeholder' => trans('challengeMap.form.placeholders.total_distance'), 'id' => 'total_distance', 'autocomplete' => 'off', "disabled" => true]) }}
                        <span class="input-group-text">{{ trans('challengeMap.form.labels.km') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-xl-4">
                <div class="form-group ">
                    {{ Form::label('Total Steps', trans('challengeMap.form.labels.total_steps')) }}
                    {{ Form::text('total_steps', old('total_steps', ($record->total_steps ?? null)), ['class' => 'form-control', 'placeholder' => trans('challengeMap.form.placeholders.total_steps'), 'id' => 'total_steps', 'autocomplete' => 'off', "disabled" => true]) }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
<div class="card-inner">
    <h3 class="card-inner-title">{{ trans('challengeMap.form.labels.company_visibility') }}</h3>
    <div>
        <div id="setPermissionList" class="tree-multiselect-box">
            <select id="map_companies" name="map_companies[]" multiple="multiple" class="form-control">
                @foreach($companies as $rolekey => $rolevalue)
                    @foreach($rolevalue['companies'] as $key => $value)
                        <option value="{{ $key }}" data-section="{{ $rolevalue['roleType'] }}"  {{ (!empty($mapCompanies) && in_array($key, $mapCompanies))? 'selected' : ''   }} >{{ $value }}</option>
                    @endforeach
                @endforeach
            </select>
        </div>
        <span id="map_companies-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">{{ trans('challengeMap.validation.company_selection') }}</span>
    </div>
</div>