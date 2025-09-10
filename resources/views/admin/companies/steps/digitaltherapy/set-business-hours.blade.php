<div class="col-xl-12 set_hours" id="set_business_hours">
    <div class="form-group">
        <h3 class="card-inner-title">
            {{trans('labels.company.set_busines_hours')}}
        </h3>
        <div class="table-responsive set-availability-block">
            @foreach($dt_availability_days as $keyday => $day)
            <div class="d-flex set-availability-box pb-1 mb-1 align-items-center" data-day-key="{{ $keyday }}">
                <div class="set-availability-day">
                    {{ $day }}
                </div>
                <div class="w-100 slots-wrapper">
                    <div class="d-flex align-items-center no-data-block {{ (array_key_exists($keyday, $dtSlots) ? 'd-none' : '') }}">
                        <div class="set-availability-date-time">
                            {{ trans('labels.user.not_available') }}
                        </div>
                        @if($dt_servicemode == false)
                        <div class="d-flex set-availability-btn-area justify-content-end">
                            <a class="add-slot action-icon text-info" href="javascript:void(0);" title="Add Slot">
                                <i class="far fa-plus">
                                </i>
                            </a>
                        </div>
                        @endif
                    </div>
                    @if(array_key_exists($keyday, $dtSlots))
                        @foreach($dtSlots[$keyday] as $slot)
                            @include('admin.companies.slot-preview', [
                                'start_time' => $slot['start_time']->format('H:i'),
                                'end_time' => $slot['end_time']->format('H:i'),
                                'time' => $slot['start_time']->format('h:i A') . ' - ' . $slot['end_time']->format('h:i A'),
                                'key' => $keyday,
                                'id' => $slot['id'],
                                'dt_servicemode' => $dt_servicemode,
                                'ws_selected' => $slot['ws_id'],
                                'ws_hidden_field' => $slot['wsHiddenTemplate']
                            ])
                        @endforeach
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        {{ Form::hidden('slots_exist', ((sizeof($dtSlots) > 0) ? '1' : ''), ['id' => 'slots_exist']) }}
        <div id="slot-error" class="invalid-feedback"></div>
    </div>
</div>
