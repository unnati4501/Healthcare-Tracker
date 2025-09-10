<div class="col-xl-12 set_hours" id="set_wellbeing_hours">
    <div class="form-group">
        <h3 class="card-inner-title">
            {{trans('company.title.set_wellbeing_hours')}}
        </h3>
        <div class="table-responsive set-availability-block">
        	<table class="table custom-table" id="dtwellbeinghours">
                <thead>
                    <tr>
                    	<th>Wellbeing Specialists</th>
                    	<th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                	@foreach($dtWsNames as $key => $ws)
                	<tr id="wshours-{{ $ws['id'] }}">
                		<td>{{ $ws['user_name'] }}</td>
                		<td width="110">
                            @if($ws['slot_count'] > 0)
                            <a class="action-icon text-success">
                                <i class="fa fa-check">
                                </i>
                            </a>
                            @else
                            <a class="action-icon text-danger" title="{{ trans('buttons.general.tooltip.no-avability-set') }}">
                                <i class="far fa-exclamation-circle"></i>
                            </a>
                            @endif
                                @if($dt_servicemode == false)
                                <a class="action-icon bs-calendar-slidebar" id="{{ $ws['id'] }}" href="javascript:;" data-toggle="canvas" data-target="#bs-canvas-right" aria-expanded="false" aria-controls="bs-canvas-right" title="{{ trans('buttons.general.tooltip.edit') }}">
                                    <i class="far fa-edit">
                                    </i>
                                </a>
                                @endif
                            @if($ws['slot_count'] > 0)
    							<a class="action-icon slot-calendar" href="javascript:;" id="{{ $ws['id'] }}" title="{{ trans('buttons.general.tooltip.set-calendar') }}">
    							    <i class="far fa-calendar">
    							    </i>
    							</a>
                            @else 
                                <a class="action-icon" href="javascript:;"> - </a>
                            @endif
						</td>
                	</tr>
                	@endforeach
                </tbody>
            </table>
        </div>
        <div id="slot-wbs-error" class="invalid-feedback"></div>
    </div>
</div>