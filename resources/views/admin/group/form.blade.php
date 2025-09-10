<div class="card-inner">
<div class="row justify-content-center justify-content-md-start">
<div class="col-auto basic-file-upload order-lg-2">
    <label>
        {{ trans('labels.group.logo') }}
        <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="{{ getHelpTooltipText('group.logo') }}">
            <i aria-hidden="true" class="far fa-info-circle text-primary">
            </i>
        </span>
    </label>
    <div class="edit-profile-wrapper edit-profile-small">
        <img id="previewImg" class="profile-image user-img edit-photo" src="{{ ( $groupData->logo ?? asset('assets/dist/img/placeholder-img.png') )}}" width="200" height="200" />
        <div class="edit-profile-avtar">
        @if(!$edit || (isset($groupData) && !in_array($groupData->subcategory->short_name, ['masterclass', 'challenge'])))
        <u>{{ trans('group.form.labels.change') }}</u>
        {{ Form::file('logo', ['class' => 'edit-avatar', 'id' => 'profileImage', 'data-width' => config('zevolifesettings.imageConversions.group.logo.width'), 'data-height' => config('zevolifesettings.imageConversions.group.logo.height'), 'data-ratio' => config('zevolifesettings.imageAspectRatio.group.logo'), 'autocomplete' => 'off'])}}
        @endif
        </div>
    </div>
</div>

<div class="col-xxl-10 col-lg-9 col-12 order-1">
    <div class="row">
        @if($edit)
        <div class="col-md-6">
            <div class="form-group">
                <div class="callout">
                    <div class="m-0">
                        {{ trans('group.form.labels.group_creator') }}:
                        <div class="fw-bold">
                            {{ $creatorData->full_name }} / {{ $creatorData->email  }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        <div class="col-md-6">
            <div class="form-group">
            <label for="">{{trans('group.form.labels.title')}}</label>
            @if(!empty($groupData->title))
                {{ Form::text('name', old('name',$groupData->title), ['class' => 'form-control', 'placeholder' => trans('group.form.placeholder.enter_group_name'), 'id' => 'name', 'autocomplete' => 'off']) }}
            @else
                {{ Form::text('name', old('name'), ['class' => 'form-control', 'placeholder' => trans('group.form.placeholder.enter_group_name'), 'id' => 'name', 'autocomplete' => 'off']) }}
            @endif
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
            <label for="">{{ trans('group.form.labels.sub_category') }}</label>
            @if(!empty($groupData->sub_category_id))
                {{ Form::select('category', $categories, $groupData->sub_category_id, ['class' => 'form-control select2','id'=>'category_id',"style"=>"width: 100%;", 'placeholder' => trans('group.form.placeholder.select_category'), 'data-placeholder' => trans('group.form.placeholder.select_category'), 'autocomplete' => 'off','disabled'=>'true'] ) }}
                <input type="hidden" name="category" value="{{$groupData->sub_category_id}}">
            @else
                {{ Form::select('category', $categories, null, ['class' => 'form-control select2','id'=>'category_id',"style"=>"width: 100%;", 'placeholder' => trans('group.form.placeholder.select_category'), 'data-placeholder' => trans('group.form.placeholder.select_category'), 'autocomplete' => 'off'] ) }}
            @endif
            </div>
        </div>

        @if(!$edit || (isset($groupData) && !in_array($groupData->subcategory->short_name, ['masterclass', 'challenge'])))
        <div class="col-xl-6">
            <div class="form-group">
                {{ Form::label('group_type', trans('group.form.labels.group_type')) }}
                <div class="">
                    @if(!empty($groupData->type))
                    <label class="custom-radio">
                        {{ trans('group.form.labels.private') }}
                        {{ Form::radio('type', 'private', $groupData->type == 'private', ['class' => 'custom-control-input', 'id' => 'private', 'autocomplete' => 'off']) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                    <label class="custom-radio">
                        {{ trans('group.form.labels.public') }}
                        {{ Form::radio('type', 'public', $groupData->type == 'public', ['class' => 'custom-control-input', 'id' => 'public', 'autocomplete' => 'off']) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                    @else
                    <label class="custom-radio">
                        {{ trans('group.form.labels.private') }}
                        {{ Form::radio('type', 'private', true, ['class' => 'custom-control-input', 'id' => 'private', 'autocomplete' => 'off']) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                    <label class="custom-radio">
                        {{ trans('group.form.labels.public') }}
                        {{ Form::radio('type', 'public', false, ['class' => 'custom-control-input', 'id' => 'public', 'autocomplete' => 'off']) }}
                        <span class="checkmark">
                        </span>
                        <span class="box-line">
                        </span>
                    </label>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('description', trans('group.form.labels.introduction')) }}
                @if(!empty($description))
                    {!! Form::textarea('introduction', old('introduction',$description), ['id' => 'introduction', 'rows' => 5, 'class' => 'form-control','placeholder'=>'','spellcheck'=>'false']) !!}
                @else
                    {!! Form::textarea('introduction', old('introduction'), ['id' => 'introduction', 'rows' => 5, 'class' => 'form-control','placeholder'=>'','spellcheck'=>'false']) !!}
                @endif
            </div>
        </div>
    </div>
</div>

</div>
</div>

@if(!$edit || (isset($groupData) && !in_array($groupData->subcategory->short_name, ['masterclass', 'challenge'])))
<div class="card-inner">
    <h3 class="card-inner-title">{{ trans('group.form.labels.participating_user') }} </h3>
    <div>
        <div id="setPermissionList" class="tree-multiselect-box">
        @if(isset($departmentData))
        <select id="group_member" name="group_member" multiple="multiple" class="form-control" >
            @foreach($departmentData as $deptGroup => $deptData)
                @foreach($deptData['teams'] as $teamGroup => $teamData)
                    @foreach($teamData['members'] as $memberGroup => $memberData)
                        @if($edit && !empty($groupUserData))
                            @if($groupData->creator_id != $memberData['id'])
                                <option value="{{ $memberData['id'] }}" data-section="{{ $deptData['name'] }}/{{ $teamData['name'] }}"  {{ ((!empty($groupUserData) && in_array($memberData['id'], $groupUserData)))? 'selected' : ''   }} >{{ $memberData['name'] }}</option>
                            @endif
                        @else
                            @if($userId != $memberData['id'])
                                <option value="{{ $memberData['id'] }}" data-section="{{ $deptData['name'] }}/{{ $teamData['name'] }}"  {{ (!empty(old('members_selected')) && in_array($memberData['id'], old('members_selected')))? 'selected' : ''   }} >{{ $memberData['name'] }}</option>
                            @endif
                        @endif
                    @endforeach
                @endforeach
            @endforeach
        </select>
        @else
        <select id="group_member" name="group_member" multiple="multiple" class="form-control" >
            @foreach($companyData as $compGroup => $compData)
                @foreach($compData['departments'] as $deptGroup => $deptData)
                    @foreach($deptData['teams'] as $teamGroup => $teamData)
                        @foreach($teamData['members'] as $memberGroup => $memberData)
                            @if($edit && !empty($groupUserData))
                                @if($groupData->creator_id != $memberData['id'])
                                    <option value="{{ $memberData['id'] }}" data-section="{{ $compData['name'] }}/{{ $deptData['name'] }}/{{ $teamData['name'] }}"  {{ ((!empty($groupUserData) && in_array($memberData['id'], $groupUserData)))? 'selected' : ''   }} >{{ $memberData['name'] }}</option>
                                @endif
                            @else
                                @if($userId != $memberData['id'])
                                    <option value="{{ $memberData['id'] }}" data-section="{{ $compData['name'] }}/{{ $deptData['name'] }}/{{ $teamData['name'] }}"  {{ (!empty(old('members_selected')) && in_array($memberData['id'], old('members_selected')))? 'selected' : ''   }} >{{ $memberData['name'] }}</option>
                                @endif
                            @endif
                        @endforeach
                    @endforeach
                @endforeach
            @endforeach
        </select>
        @endif
        </div>
    </div>
</div>
<span id="group_member-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">{{trans('group.validation.group_member_required')}}</span>
    <span id="group_member-min-error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">{{trans('group.validation.group_member_min')}}</span>
@endif
