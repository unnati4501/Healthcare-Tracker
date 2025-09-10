<div class="col-xl-12 set_hours" id="set_location_hours">
    <div class="form-group">
        <h3 class="card-inner-title">
            {{trans('company.title.set_location_hours')}}
        </h3>
        <div class="table-responsive set-availability-block">

        <table class="table custom-table" id="locationHoursManagment">
		{{ Form::hidden("mainTableUpdatedSlotIds", null , ['class' => 'mainTableUpdatedSlotIds', 'id' => 'mainTableUpdatedSlotIds']) }}
		{{ Form::hidden("mainTableRemovedSlotIds", null , ['class' => 'mainTableRemovedSlotIds', 'id' => 'mainTableRemovedSlotIds']) }}
		{{ Form::hidden("tempTableRemovedSlotIds", null , ['class' => 'tempTableRemovedSlotIds', 'id' => 'tempTableRemovedSlotIds']) }}
		{{ Form::hidden("slotExistsForAnyLocation", 0 , ['class' => 'slotExistsForAnyLocation', 'id' => 'slotExistsForAnyLocation']) }}
                <thead>
                    <tr>
                    	<th>Location Name</th>
                    	<th class="location_ws_column">Wellbeing Specialists</th>
                    	<th width="110">Actions</th>
                    </tr>
                </thead>
                <tbody>
                	@foreach($companyLocation as $key => $location)
                	<tr>
                		<td>{{ $location->name }}</td>
                		<td class="location_ws_column" id="location_ws_id_{{$location->id}}">
							@php
								$locationSpecificFromDb = $company->digitalTherapySpecificSlots()
								->leftjoin('users', 'users.id', '=', 'digital_therapy_specific.ws_id')
								->select(
									'users.id',
									DB::raw('concat(users.first_name," ",users.last_name) as user_name'),
									DB::raw('(select count(id) FROM digital_therapy_specific WHERE ws_id = users.id AND company_id = ' . $company->id . ' AND date >= CURDATE()) as slot_count')
								)
								->where('location_id', $location->id)
								->distinct()
								->get()
								->toArray();
							@endphp
							@if(!empty($locationSpecificFromDb))
									@foreach($locationSpecificFromDb as $key => $locationSpecificFromDbValue)
									<span id='ws_{{ $location->id }}_{{ $locationSpecificFromDbValue['id'] }}' title="{{ $locationSpecificFromDbValue['user_name'] }}" class='ws_sl_{{ $location->id }} ws_{{ $locationSpecificFromDbValue['id'] }} badge bg-secondary'>{{ $locationSpecificFromDbValue['user_name'] }}</span>
									@endforeach
									<input type="hidden" class="wsIdsLocationWise" id="wsIdsLocationWise_{{$location->id}}" value="{{ implode(',', array_column($locationSpecificFromDb, 'id')) }}"> 
							@else
								@foreach($dtWsNames as $key => $dtWsNameValue)
									<span id='ws_{{ $location->id }}_{{ $dtWsNameValue['id'] }}' title="{{ $dtWsNameValue['user_name'] }}" class='ws_sl_{{ $location->id }} ws_{{ $dtWsNameValue['id'] }} badge bg-secondary'>{{ $dtWsNameValue['user_name'] }}</span>
								@endforeach
								<input type="hidden" class="wsIdsLocationWise" id="wsIdsLocationWise_{{$location->id}}" value="{{ implode(',', array_column($dtWsNames, 'id')) }}"> 
							@endif
                        </td>
                		<td>
							@if($location->slot_count_general > 0)
							<a class="action-icon text-success slot-general">
                                <i class="fa fa-check">
                                </i>
                            </a>
							@else
							<a class="action-icon text-danger slot-general" title="{{ trans('buttons.general.tooltip.no-avability-set') }}">
								<i class="far fa-exclamation-circle"></i>
                            </a>
							@endif
							@if($location->slot_count_specific > 0)
							<a class="action-icon text-success slot-specific">
                                <i class="fa fa-check">
                                </i>
                            </a>
							@else
							<a class="action-icon text-danger slot-specific" title="{{ trans('buttons.general.tooltip.no-avability-set') }}">
								<i class="far fa-exclamation-circle"></i>
                            </a>
							@endif
								<a href="javascript:void(0)" class="action-icon location_WS_specific_edit" data-companyid = "{{$recordData->id}}" data-id="{{$location->id}}" data-locationname="{{$location->name}}" id="addLocationSlots">
									<i class="far fa-edit"></i>
								</a>
								@if($dt_servicemode == false)
								<a class="action-icon location_ws_column_test edit-location-specific" href="javascript:;" locationId="{{ $location->id }}" title="{{ trans('buttons.general.tooltip.edit') }}">
									<i class="far fa-edit">
									</i>
								</a>
								@endif
							@if($location->slot_count_specific > 0)
							<a class="action-icon slot-calendar-specific slot-calendar" data-locationid="{{$location->id}}" href="javascript:;" title="{{ trans('buttons.general.tooltip.set-calendar') }}">
							    <i class="far fa-calendar">
							    </i>
							</a>
							@endif
							@if($location->slot_count_general > 0)
							<a class="action-icon slot-calendar-general slot-calendar" data-locationid="{{$location->id}}" href="javascript:;" title="{{ trans('buttons.general.tooltip.set-calendar') }}">
							    <i class="far fa-calendar">
							    </i>
							</a>
							@endif
						</td>
                	</tr>
                	@endforeach
                </tbody>
            </table>
        </div>
		{{ Form::hidden('slots_exist', ((sizeof($dtLocationGenralSlots) > 0) ? '1' : ''), ['id' => 'slots_exist_for_location']) }}
        <div id="slot-location-error" class="invalid-feedback"></div>
    </div>
</div>