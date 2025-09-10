<div class="card-inner">
    <div class="row">
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('group', trans('companyplans.form.labels.group_type')) }}
                <div>
                    @foreach($groupType as $roleKey => $roleName)
                        <label class="custom-radio" for="role_group_{{ $roleKey }}">
                            {{ ucwords($roleName) }}
                            @if(!$edit)
                            {{ Form::radio('group', $roleKey, ((old('group') == $roleKey) ? true : (($roleKey == 1) ? true : false)), ['class' => 'roleGroup form-control', 'id' => "role_group_{$roleKey}"]) }}
                            @else
                            {{ Form::radio('group', $roleKey, old('group', ($cpPlan->group == $roleKey)), ['class' => 'roleGroup form-control', 'id' => "role_group_{$roleKey}", "disabled" => true]) }}
                            @endif
                            <span class="checkmark">
                            </span>
                            <span class="box-line">
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('companyplan', trans('companyplans.form.labels.companyplan')) }}
                {{ Form::text('companyplan', old('companyplan', ($cpPlan->name ?? null)), ['class' => 'form-control', 'placeholder' => trans('companyplans.form.placeholder.enter_companyplan'), 'id' => 'companyplan', 'autocomplete' => 'off']) }}
            </div>
        </div>
        <div class="col-lg-6 col-xl-4">
            <div class="form-group">
                {{ Form::label('description', trans('companyplans.form.labels.description')) }}
                {{ Form::textarea('description', old('description', ($cpPlan->description ?? null)), ['id' => 'description', 'rows' => 3, 'class' => 'form-control', 'placeholder' => trans('companyplans.form.placeholder.enter_description'), 'spellcheck' => 'false']) }}
            </div>
        </div>
    </div>
</div>
<div class="card-inner">
    <h3 class="card-inner-title">
        {{ trans('companyplans.form.labels.set_privileges') }}
    </h3>
    <label>{{ trans('companyplans.form.labels.set_prvileges_desc') }}</label>
    <div>
        <div class="tree-multiselect-box" id="setPermissionList">
            <select class="form-control" id="set_privileges" multiple="multiple" name="set_privileges">
                @foreach($featuresData as $key => $value)
                    @if(!empty($value['children']))
                        @foreach($value['children'] as $val)
                            <option data-section="{{ $value['name'] }}" data-manage="{{$value['manage']}}" {{ (((!empty($planFeatures) && in_array($val['id'], $planFeatures))) ? 'selected' : '') }} value="{{ $val['id'] }}">
                                {{ $val['name'] }}
                            </option>
                        @endforeach
                    @else
                        <option data-section="{{ $value['name'] }}" {{ (((!empty($planFeatures) && in_array($value['id'], $planFeatures))) ? 'selected' : '') }} data-manage="{{$value['manage']}}" value="{{ $value['id'] }}">
                            {{ $value['name'] }}
                        </option>
                    @endif
                @endforeach
            </select>
        </div>
        <span id="set_privileges_error" style="display: none; width: 100%; margin-top: 0.25rem; font-size: 80%; color: #f44436;">
            {{ trans('companyplans.validation.set_privileges_required') }}
        </span>
    </div>
</div>
