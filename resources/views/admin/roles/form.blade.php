<div class="row">
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('group', trans('roles.form.labels.select_role_group')) }}
            @if(!empty($roleData->group))
                <div>
                @foreach($roleGroupData as $key => $value)
                <label class="custom-radio">
                    {{ $value }}
                    {{ Form::radio('group', $key, old('group', ($roleData->group == $key)), ['class' => 'roleGroup form-control', 'id' => "group_{$key}", 'disabled' => true]) }}
                    <span class="checkmark">
                    </span>
                    <span class="box-line">
                    </span>
                </label>
                @endforeach
                </div>
                <input name="group" type="hidden" value="{{ $roleData->group }}"/>
                @else
                <div>
                @foreach($roleGroupData as $key => $value)
                <label class="custom-radio">
                    {{ $value }}
                    {{ Form::radio('group', $key, old('group', ($key == 'zevo')), ['class' => 'roleGroup form-control', 'id' => "group_{$key}"]) }}
                    <span class="checkmark">
                    </span>
                    <span class="box-line">
                    </span>
                </label>
                @endforeach
                </div>
            @endif
        </div>
    </div>
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('name', trans('roles.form.labels.role_name')) }}
            {{ Form::text('name', old('name', ($roleData->name ?? null)), ['class' => 'form-control', 'placeholder' => trans('roles.form.placeholder.role_name'), 'id' => 'name', 'autocomplete' => 'off']) }}
        </div>
    </div>
    <div class="col-lg-6 col-xl-4">
        <div class="form-group">
            {{ Form::label('description', trans('roles.form.labels.role_desc')) }}
            {{ Form::textarea('description', old('description', ($roleData->description ?? null)), ['id' => 'description', 'rows' => 3, 'class' => 'form-control', 'placeholder' => trans('roles.form.placeholder.role_desc'), 'spellcheck' => 'false']) }}
        </div>
    </div>
</div>
<div class="card-inner">
    <h3 class="card-inner-title">
        {{ trans('roles.form.labels.set_privileges') }}
    </h3>
    <div>
        <div class="tree-multiselect-box" id="setPermissionList">
            <select class="form-control" id="set_privileges" multiple="multiple" name="set_privileges">
                @foreach($permissionData as $key => $value)
                @foreach($value['children'] as $val)
                @if(!empty($permissions))
                <option data-section="{{ $value['display_name'] }}" {{ (((!empty($permissions) && in_array($val['id'], $permissions))) ? 'selected' : '') }} value="{{ $val['id'] }}">
                    {{ $val['display_name'] }}
                </option>
                @else
                <option data-section="{{ $value['display_name'] }}" value="{{ $val['id'] }}">
                    {{ $val['display_name'] }}
                </option>
                @endif
                @endforeach
                @endforeach
            </select>
        </div>
        <span id="set_privileges_error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">
            {{ trans('roles.validation.set_privileges_required') }}
        </span>
    </div>
</div>