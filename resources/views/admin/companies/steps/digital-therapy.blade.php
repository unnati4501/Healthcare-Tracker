{{-- <button class="btn btn-primary" type="button" data-toggle="canvas" data-target="#bs-canvas-right" aria-expanded="false" aria-controls="bs-canvas-right">&#9776;</button> --}}
<div class="bg-light border p-3 mb-4">
    <div class="row">
    <div class="col-lg-6 col-xl-2">
        <div class="form-group">
            {{ Form::label('service_mode', 'Service Mode') }}
            <div class="">
                <label class="custom-checkbox">
                    {{trans('labels.company.online')}}
                    {{ Form::checkbox('dt_servicemode[]', 'online', old('dt_is_online', (!empty($dtData) && $dtData->dt_is_online ==  1)), ['class' => 'form-control', 'id' => 'dt_is_online', 'disabled'=>$dt_servicemode]) }}
                    <span class="checkmark">
                    </span>
                    <span class="box-line">
                    </span>
                </label>
                <label class="custom-checkbox">
                    {{trans('labels.company.offline')}}
                    {{ Form::checkbox('dt_servicemode[]', 'onsite', old('dt_is_onsite', (!empty($dtData) && $dtData->dt_is_onsite ==  1)), ['class' => 'form-control', 'id' => 'dt_is_onsite', 'disabled'=>$dt_servicemode]) }}
                    <span class="checkmark">
                    </span>
                    <span class="box-line">
                    </span>
                </label>
                <span id="reseller_loader" style="display: none;">
                    <i class="fas fa-spinner fa-lg fa-spin">
                    </i>
                    <span class="ms-1">
                        Loading data...
                    </span>
                </span>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-xl-6">
        <div class="form-group">
            {{trans('labels.company.staff')}}
            @if($edit)
            {{ Form::select('dt_wellbeing_sp_ids[]', $wellbeingSp, old('dt_wellbeing_sp_ids[]', !empty($dtWsIds) ? explode(',', $dtWsIds) : null), ['class' => 'form-control select2', 'id' => 'dt_wellbeing_sp_ids', 'data-placeholder' => 'Select Staff', 'multiple' => true, 'data-close-on-select' => 'false','disabled' => $dt_servicemode,  'data-allow-clear'=>'false']) }}
            @else
            {{ Form::select('dt_wellbeing_sp_ids[]', $wellbeingSp, null , ['class' => 'form-control select2', 'id' => 'dt_wellbeing_sp_ids', 'data-placeholder' => 'Select Staff', 'multiple' => true, 'disabled' => $dt_servicemode,  'data-allow-clear'=>'false']) }}
            @endif
        </div>
    </div>
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('service_mode', 'Set Hours By') }}
            <div class="">
                {{ Form::select('set_hours_by', $setHoursBy, old('set_hours_by', ($companyDT->set_hours_by ?? 1)), ['class' => 'form-control select2', 'id' => 'set_hours_by', 'placeholder' => 'Select Set Hours By', 'data-placeholder' => 'Select Set Hours By', 'data-allow-clear' => 'true', 'disabled' => $dt_servicemode] ) }}
            </div>
        </div>
        <div class="form-group">
            {{ Form::label('service_mode', 'Set Availability By') }}
            <div class="">
                {{ Form::select('set_availability_by', $setAvailabilityBy, old('set_availability_by', ($companyDT->set_availability_by ?? 1)), ['class' => 'form-control select2', 'id' => 'set_availability_by', 'placeholder' => 'Select Set Hours By', 'data-placeholder' => 'Select Set Hours By', 'data-allow-clear' => 'true', 'disabled' => $dt_servicemode] ) }}
            </div>
        </div>
    </div>
    <div class="col-xl-12" id="staffService">
        <div class="form-group">
            <h3 class="card-inner-title">
                Services provided by Staff
            </h3>
            <table class="table custom-table" id="staffServiceManagment">
                <thead>
                    <tr>
                        <th>
                            Staff
                        </th>
                        <th>
                            Services
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {{-- @if(isset($staffServicesData['staffServices']) && !empty($staffServicesData['staffServices']))
                        @foreach($staffServicesData['staffServices'] as $staffKey => $staffVal)
                            <tr id="staff-row-{{$staffVal['user_id']}}">
                                <td>{{$staffVal['staffName']}}</td>
                                <td>{{$staffVal['services']}}</td>
                            </tr>
                        @endforeach
                    @endif --}}
                    @if(isset($staffServicesData['staffServices']) && !empty($staffServicesData['staffServices']))
                        @foreach($staffServicesData['staffServices'] as $staffKey => $staffVal)
                            <tr id="staff-row-{{$staffKey}}">
                                <td>{{$staffVal['staffName']}}</td>
                                <td id="staff-service-{{$staffKey}}">
                                    {{-- @foreach($staffVal['services'] as $k => $v)
                                        <span class="service-badge" id="service_{{$staffKey}}_{{$k}}">{{$v}}<i class="fal fa-times" data-sid="{{$k}}" data-wsid="{{$staffKey}}"></i>
                                        <input type="hidden" name="service[{{$staffKey}}][]" value="{{$k}}">
                                        </span>
                                        
                                    @endforeach --}}

                                    @foreach($staffVal['services'] as $sId => $v)
                                    <span class="service-badge" id="service_{{$staffKey}}_{{$sId}}">{{$v['name']}}<i class="fal fa-times" data-sid="{{$sId}}" data-wsid="{{$staffKey}}"></i>
                                    <input type="hidden" name="service[{{$staffKey}}][{{$v['id']}}]" value="{{$sId}}">
                                    </span>
                                    
                                @endforeach
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div></div>
<div class="row">
    @include('admin.companies.steps.digitaltherapy.set-business-hours', ['edit' => $edit])
    @include('admin.companies.steps.digitaltherapy.set-location-hours', ['edit' => $edit])
    @include('admin.companies.steps.digitaltherapy.set-wellbeing-hours', ['edit' => $edit])
    @include('admin.companies.steps.digitaltherapy.specific-hours', ['edit' => $edit])
    @include('admin.companies.steps.digitaltherapy.session-rules')
</div>
@include('admin.companies.steps.digitaltherapy.remove-specific-slot-model-box')
@include('admin.companies.steps.digitaltherapy.location-specific-slots-model-box')
@include('admin.companies.steps.digitaltherapy.company-specific-slots-model-box')
<script id="add-specific-new-slot-template" type="text/html">
@include('admin.companies.steps.digitaltherapy.add-new-specific-slot')
</script>
<script id="specific-preview-slot-template" type="text/html">
@include('admin.companies.steps.digitaltherapy.specific-slot-preview', [
    'start_time' => '#start_time#',
    'end_time' => '#end_time#',
    'datestamp' => '#date_stamp#',
    'previous_slot_block' => '#previous_slot_block#',
    'time' => '#time#',
    'key' => '#key#',
    'id' => '#id#',
])
</script>
<script id="specific-preview-slot-input-template" type="text/html">
@include('admin.companies.steps.digitaltherapy.specific-slot-input', [
    'start_time' => '#start_time#',
    'end_time' => '#end_time#',
    'datestamp' => '#date_stamp#',
    'date_input' => '#date_input#',
    'key' => '#key#',
    'id' => '#id#',
])
</script>
<script id="location-specific-preview-slot-input-template" type="text/html">
@include('admin.companies.steps.digitaltherapy.location-specific-slot-input', [
    'start_time' => '#start_time#',
    'end_time' => '#end_time#',
    'datestamp' => '#date_stamp#',
    'date_input' => '#date_input#',
    'location_id' => '#location_id#',
    'key' => '#key#',
    'id' => '#id#',
])
</script>
<script id="specific-edit-slot-template" type="text/html">
@include('admin.companies.steps.digitaltherapy.specific-slot-edit-preview', [
    'start_time' => '#start_time#',
    'end_time' => '#end_time#',
    'id' => '#id#'
])
</script>