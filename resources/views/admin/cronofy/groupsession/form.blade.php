<div class="card-inner">
    @if(!$edit)
    <div class="label-steps mb-4 pt-0">
        <div class="active">
            <span>1. {{ trans('Cronofy.title.add_details') }}</span>
        </div>
        <div>
            <span>2. {{ trans('Cronofy.title.book_sessions') }}</span>
        </div>
    </div>
    <hr/>
    @elseif((isset($allowJoin) && $allowJoin == true) || (isset($allowUpdate) && $allowUpdate == true))
    <div class="d-flex justify-content-end">
        @if(isset($allowJoin) && $allowJoin == true)
        <a class="btn btn-primary btn-sm me-3" href="{{ $join_url }}" target="_blank">
            {{ trans('Cronofy.session_details.buttons.join') }}
        </a>
        @endif
        @if(isset($allowUpdate) && $allowUpdate == true)
        <a class="btn btn-primary btn-sm me-3 reschedule-button" href="javascript:;" url="{{ route('admin.cronofy.sessions.reschedule-session', $cronofySchedule->id) }}">
            {{ trans('Cronofy.session_details.buttons.reschedule') }}
        </a>
        <a class="btn btn-outline-primary btn-sm" data-id="{{$cronofySchedule->id}}" href="javaScript:void(0)" id="cancelSessionModel">Cancel</a>
        @endif
    </div>
    <hr/>
    @endif
    <div class="row">
        @if($role->slug == 'wellbeing_specialist')
        <div class="col-lg-6 col-xl-3">
            <div class="form-group">
                {{ Form::label('company', trans('Cronofy.group_session.form.labels.company')) }}
                {{ Form::select('company', $companies, old('company', ($cronofySchedule->company_id ?? null)), ['class' => 'form-control select2', 'id'=>'company', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => "", 'data-placeholder' => trans('Cronofy.group_session.form.placeholder.company'), 'data-allow-clear' => 'true', 'disabled' => $edit]) }}
            </div>
        </div>

        <div class="col-lg-6 col-xl-3" id="locations" style="display: {{ ($edit && $cronofySchedule->location_id == null) ? 'none' : 'show' }}">
            <div class="form-group">
                {{ Form::label('location', trans('Cronofy.group_session.form.labels.location')) }}
                {{ Form::select('location', $location ?? [], old('location', ($cronofySchedule->location_id ?? null)), ['class' => 'form-control select2', 'id'=>'location', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => "", 'data-placeholder' => trans('Cronofy.group_session.form.placeholder.location'), 'data-allow-clear' => 'false', 'disabled' => $edit]) }}
            </div>
        </div>
        @endif
        <div class="col-lg-6 col-xl-3">
            <div class="form-group">
                {{ Form::label('service', trans('Cronofy.group_session.form.labels.service')) }}
                {{ Form::select('service', $service, old('service', ($cronofySchedule->service_id ?? null)), ['class' => 'form-control select2', 'id'=>'service', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => "", 'data-placeholder' => trans('Cronofy.group_session.form.placeholder.service'), 'data-allow-clear' => 'true', 'disabled' => $edit]) }}
                {{ Form::hidden('serviceIsCounselling', false, ['id' => 'serviceIsCounselling']) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-3">
            <div class="form-group">
                {{ Form::label('sub_category', trans('Cronofy.group_session.form.labels.sub_category')) }}
                {{ Form::select('sub_category', $subcategories, old('sub_category', ($cronofySchedule->topic_id ?? null)), ['class' => 'form-control select2', 'id'=>'sub_category', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => "", 'data-placeholder' => trans('Cronofy.group_session.form.placeholder.sub_category'), 'data-allow-clear' => 'true', 'disabled' => $edit]) }}
            </div>
        </div>
        
        @if($role->slug != 'wellbeing_specialist')
        <div class="col-lg-12" id="mainWellbeingSpecialist">
            <div class="form-group">
                {{ Form::label('', trans('Cronofy.group_session.form.labels.ws_display')) }}
                <div data-hint-block="" style="display: {{ ($edit) ? 'none' : 'show' }}">
                    <span>
                        {{ trans('Cronofy.group_session.message.ws_message') }}
                    </span>
                </div>
                <div data-loader-block="" style="display: none;">
                    <span class="fa fa-spinner fa-spin ms-2">
                    </span>
                    {{ trans('Cronofy.group_session.message.loading_ws') }}
                </div>
                <div data-no-slots-block="" style="display: none;">
                    <span>
                        {{ trans('Cronofy.group_session.message.no_result_found') }}
                    </span>
                </div>
                <div class="owl-carousel owl-theme arrow-theme" data-slots-block="" style="display: {{ ($edit) ? 'show' : 'none' }}"  id="ws-owl-carousel">
                    @if($edit && !empty($getWellbeingSpecialist))
                        @foreach($getWellbeingSpecialist as $ws)
                            @include('admin.cronofy.groupsession.ws-block-section', [
                                'wsId'    => $ws->id,
                                'wsName'  => $ws->name,
                                'edit'    => $edit,
                                'wsImage' => $ws->getMediaData('logo', ['w' => 800, 'h' => 800, 'zc' => 3])['url'],
                                'selectedWS' => $cronofySchedule->ws_id,
                            ])
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
        @endif
        @if($edit)
        <div class="col-md-4 mt-3">
            <div class="form-group">
                <div class="callout">
                    <div class="m-0">
                        {{trans('Cronofy.group_session.form.labels.date-time')}}:
                        <div class="fw-bold">
                            {{ $startTime }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        <div class="col-md-12 mb-3 error_location_message d-none" style="color: red">
            <strong>Note:</strong> Please reach out to the admin as currently no locations assigned  for this company to make the booking
        </div>
        <div class="col-lg-12 col-xl-12">
            <div class="form-group">
                {{ Form::label('Notes', trans('Cronofy.group_session.form.labels.notes')) }}
                @if($role->slug == 'wellbeing_specialist')
                <span class="ms-2 font-13">({{ trans('Cronofy.group_session.message.note_warning') }})</span>
                @endif
                {{ Form::textarea('notes', old('notes', (isset($cronofySchedule->notes) ? htmlspecialchars_decode($cronofySchedule->notes) : null)), ['class' => 'form-control article-ckeditor', 'id' => 'notes', 'data-errplaceholder' => '#description-error-cstm', 'data-formid' => (($edit) ? "#addgroupsession" : "#addgroupsession"), 'data-upload-path' => route('admin.ckeditor-upload.session-description', ['_token' => csrf_token() ])]) }}
                <div>
                    <small>
                        {{ trans('Cronofy.group_session.message.fullscreen_mode_for_notes') }}

                        <i class="fas fa-arrows-alt" style="transform: rotate(45deg);">
                        </i>
                        {{ trans('feed.message.from_toolbar') }}
                    </small>
                </div>
                <div id="notes-error-cstm" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">
                </div>
            </div>
        </div>
    </div>
</div>
<div class="card-inner user_sections" style="display: {{ ($company) ? 'block' : 'none' }}">
    <h3 class="card-inner-title">
        {{ trans('Cronofy.group_session.form.labels.add_users') }}
    </h3>
    <div>
        <div class="tree-multiselect-box" id="setPermissionList">
            <select class="form-control" id="add_users" multiple="multiple" name="add_users[]">
                @if(!empty($company))
                    @foreach($company['location'] as $lkey => $lvalue)
                        @foreach($lvalue['department'] as $dkey => $dvalue)
                            @foreach($dvalue['team'] as $tkey => $tvalue)
                                @foreach($tvalue['user'] as $ukey => $uvalue)
                                    <option value="{{$uvalue['id']}}" data-section="{{$lvalue['locationName']}}/{{$dvalue['departmentName']}}/{{$tvalue['name']}}" {{ (!empty($selectedUsers) && in_array($uvalue['id'], $selectedUsers))? 'selected' : ''   }}>{{$uvalue['name'] . ' ' . $uvalue['email']}}</option>
                                @endforeach
                            @endforeach
                        @endforeach
                    @endforeach
                @endif
            </select>
        </div>
        <span id="add_users-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">{{ trans('Cronofy.group_session.message.minimum_user_required') }}</span>
        <span id="add_users-max-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">{{ trans('Cronofy.group_session.message.only_one_participate') }}</span>
    </div>
</div>
@if(!$edit)
<div class="reset-password-block">
    {{ trans('Cronofy.group_session.message.details_page_message') }}
</div>
@endif
<input type="hidden" name="sessionType" id="sessionType" value="{{ $sessionType }}">