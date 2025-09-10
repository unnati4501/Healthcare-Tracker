<div class="card-inner">
    <div class="row">
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
            <label for="">
                {{trans('notificationsettings.form.labels.title')}}
            </label>
            {{ Form::text('title', old('title'), ['class' => 'form-control', 'placeholder' => trans('notificationsettings.form.placeholder.enter_title'), 'id' => 'title', 'autocomplete' => 'off']) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
            <label>
                {{trans('notificationsettings.form.labels.logo')}}
            </label>
            <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('user_notification.logo') }}">
                <i aria-hidden="true" class="far fa-info-circle text-primary">
                </i>
            </span>
            <div class="custom-file custom-file-preview">
                <label class="file-preview-img" for="logo">
                    @if(!empty($record->logo))
                    <img height="250" id="previewImg" src="{{$record->logo}}" width="200"/>
                    @else
                    <img height="250" id="previewImg" src="{{asset('assets/dist/img/boxed-bg.png')}}" width="200"/>
                    @endif
                </label>
                {{ Form::file('logo', ['class' => 'custom-file-input form-control', 'id' => 'logo', 'data-width' => config('zevolifesettings.imageConversions.user_notification.logo.width'), 'data-height' => config('zevolifesettings.imageConversions.user_notification.logo.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.user_notification.logo'), 'autocomplete' => 'off'])}}
                <label class="custom-file-label" for="logo">
                    {{trans('notificationsettings.form.labels.choose_file')}}
                </label>
            </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                <label for="">
                    {{trans('notificationsettings.form.labels.scheduled_time')}}
                </label>
                <div class="datepicker-wrap">
                    {{ Form::text('schedule_date_time', old('schedule_date_time'), ['class' => 'form-control datepicker', 'placeholder' => trans('notificationsettings.form.placeholder.select_datetime'), 'id' => 'schedule_date_time', 'autocomplete' => 'off']) }}
                    <i class="far fa-calendar"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                <div>
                    <label class="custom-checkbox no-label">{{ trans('notificationsettings.form.labels.push') }}
                        <input type="checkbox" class="form-control" name="push" id="push" />
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-12">
            <div class="form-group">
                <label>
                    {{trans('notificationsettings.form.labels.message')}}
                </label>
                {{ Form::textarea('message', old('message'), ['class' => 'form-control', 'placeholder' => 'Message', 'id' => 'message', 'rows' => 3, 'maxlength' => 200]) }}
                <span id="message-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">
                    {{ trans('notificationsettings.message.message_required') }}
                </span>
                <span id="message-max-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">
                    {{ trans('notificationsettings.message.message_characters') }}
                </span>
            </div>
        </div>
    </div>
</div>
<div class="card-inner">
    <h3 class="card-inner-title">{{ Form::label('members', trans('notificationsettings.form.labels.members')) }}</h3>
    <div>
        <div id="setPermissionList" class="tree-multiselect-box">
        @if(isset($departmentData))
        <select id="members" name="members" multiple="multiple" class="form-control" >
            @foreach($departmentData as $deptGroup => $deptData)
                @foreach($deptData['teams'] as $teamGroup => $teamData)
                    @foreach($teamData['members'] as $memberGroup => $memberData)
                        <option value="{{ $memberData['id'] }}" data-section="{{ $deptData['name'] }}/{{ $teamData['name'] }}"  {{ (!empty(old('members_selected')) && in_array($memberData['id'], old('members_selected')))? 'selected' : ''   }} >{{ $memberData['name'] }}
                        </option>
                    @endforeach
                @endforeach
            @endforeach
        </select>
        @else
        <select id="members" name="members" multiple="multiple" class="form-control" >
            @foreach($companyData as $compGroup => $compData)
                @foreach($compData['departments'] as $deptGroup => $deptData)
                    @foreach($deptData['teams'] as $teamGroup => $teamData)
                        @foreach($teamData['members'] as $memberGroup => $memberData)

                                <option value="{{ $memberData['id'] }}" data-section="{{ $compData['name'] }}/{{ $deptData['name'] }}/{{ $teamData['name'] }}"  {{ (!empty(old('members_selected')) && in_array($memberData['id'], old('members_selected')))? 'selected' : ''   }} >{{ $memberData['name'] }}</option>

                        @endforeach
                    @endforeach
                @endforeach
            @endforeach
        </select>
        @endif
        </div>
        <span id="members-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">{{trans('notificationsettings.message.member_required')}}</span>
    </div>
</div>

