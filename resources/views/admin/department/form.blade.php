
@if($role->group == 'zevo' || ($role->group == 'reseller' && $company && $company->is_reseller == true))
    <div class="col-xl-4">
        <div class="form-group">
            {{ Form::label('company_id', trans('department.form.labels.company')) }}
            {{ Form::select('company_id', $companies, old('company_id', ($department->company_id ?? null)), ['class' => 'form-control select2', 'id' => 'company_id', 'placeholder' => trans('department.form.placeholder.company'), 'data-placeholder' => trans('department.form.placeholder.company'), 'data-dependent' => 'multilocation', 'disabled' => $edit] ) }}
        </div>
    </div>
    @if($edit)
        {{ Form::hidden('company_id', ($department->company_id ?? $company->id), ['id' => 'company_id']) }}
    @endif
@else
    {{ Form::hidden('company_id', ($department->company_id ?? $company->id), ['id' => 'company_id']) }}
@endif
<div class="col-xl-4">
    <div class="form-group">
        {{ Form::label('name', trans('department.form.labels.department_name')) }}
        {{ Form::text('name', old('name', ($department->name ?? null)), ['class' => 'form-control', 'placeholder' => trans('department.form.placeholder.enter_department_name'), 'id' => 'name', 'autocomplete' => 'off', 'data-selectOnClose' => false, 'data-selectonclose' => false]) }}
    </div>
</div>
<div class="col-xl-4">
    <div class="form-group">
        {{ Form::label('multilocation', trans('department.form.labels.company_location')) }}
        {{ Form::select('location[]', $department_location, old('location[]', ($selected_department_location ?? [])), ['class' => 'form-control select2', 'id' => 'multilocation', 'multiple' => true] ) }}
    </div>
</div>
@if($askForAutoTeamCreation)
<div class="col-xl-12" id="team-block" style="display: {{ $teamBlockVisibility }};">
    <hr/>
    <div class="form-group">
        <div class="col-xl-10">
            <h6 class="text-primary">
                {{ trans('department.form.labels.set_your_team') }}
            </h6>
        </div>
        <div id="location_list">
            @if($edit)
                @foreach($autoTeamCreationMeta as $key => $meta)
                    @include('admin.department.auto-team-creation', [
                        'id' => $key,
                        'locationName' => $department_location[$key],
                        'disabled' => true,
                        'no_of_employee' => ($meta->no_of_employee ?? null),
                        'possible_teams' => ($meta->possible_teams ?? '-'),
                        'name_convention' => ($meta->naming_convention ?? null),
                        'ecClass' => '',
                        'ncClass' => '',
                    ])
                @endforeach
            @endif
        </div>
    </div>
</div>
<script id="location-team-template" type="text/html">
    @include('admin.department.auto-team-creation', [
        'id' => ':id',
        'locationName' => ':location-name',
        'disabled' => false,
        'no_of_employee' => '',
        'possible_teams' => '-',
        'name_convention' => '',
        'ecClass' => 'ecClass',
        'ncClass' => ($edit ? "ncClass" : ""),
    ])
</script>
@endif
