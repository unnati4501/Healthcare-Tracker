<div class="content-header">
    <div class="container-fluid">
        <div class="d-xl-flex align-items-center justify-content-between">
            <div class="">
                <h1>
                    {{ trans('dashboard.title.index') }}
                </h1>
            </div>
            <div class="align-self-center text-xl-end mt-md-3 mt-xl-0">
                <div class="card search-card dashboard-main-filter four-items">
                    <div class="card-body pb-0">
                        <div class="search-outer d-md-flex justify-content-between">
                            <div>
                                @if($role->group == 'reseller' && $company->parent_id == null && $role->slug != 'wellbeing_team_lead')
                                <div class="form-group" id="industry">
                                    {{ Form::select('industry_id', $industry, null, ['class' => "form-control select2", 'id'=>'industry_id', 'style'=>"width: 100%;", 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.industry'), 'data-placeholder' => trans('labels.dashboard.industry'), 'data-allow-clear' => 'true', 'target-data' => 'company_id']) }}
                                </div>
                                @endif
                                @if($role->slug != 'counsellor' && $role->slug != 'wellbeing_specialist' && $role->slug != 'health_coach')
                                    @if($role->group == 'zevo' || ($role->group == 'reseller' && $company->parent_id == null))
                                    <div class="form-group" id="company">
                                        {{ Form::select('company_id', $companies, null, ['class' => "form-control select2", 'id'=>'company_id', 'style'=>"width: 100%;", 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.company'), 'data-placeholder' => trans('labels.dashboard.company'), 'data-allow-clear' => 'true', 'target-data' => 'department_id', 'target-location-data'=>'location_id']) }}
                                    </div>
                                    <div class="form-group" id="dtcompany" style="display: none">
                                        {{ Form::select('company_id', $dtCompanies, null, ['class' => "form-control select2", 'id'=>'dtcompany_id', 'style'=>"width: 100%;", 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.company'), 'data-placeholder' => trans('labels.dashboard.company'), 'data-allow-clear' => 'true', 'target-data' => 'department_id', 'target-location-data'=>'location_id']) }}
                                    </div>
                                    @else
                                    <div style="display: none">
                                        {{ Form::select('company_id', $companies, Auth::user()->company->first()->id, ['class' => "form-control select2", 'id'=>'company_id', 'style'=>"width: 100%;", 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.company'), 'data-placeholder' => trans('labels.dashboard.company'), 'data-allow-clear' => 'true', 'disabled'=>true, 'target-data' => 'department_id', 'target-location-data'=>'location_id']) }}
                                    </div>
                                    <div style="display: none">
                                        {{ Form::select('company_id', $dtCompanies, Auth::user()->company->first()->id, ['class' => "form-control select2", 'id'=>'dtcompany_id', 'style'=>"width: 100%;", 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.company'), 'data-placeholder' => trans('labels.dashboard.company'), 'data-allow-clear' => 'true', 'disabled'=>true, 'target-data' => 'department_id', 'target-location-data'=>'location_id']) }}
                                    </div>
                                    @endif
                                @else
                                    <div style="display: none">
                                        {{ Form::select('company_id', $companies, "", ['class' => "form-control select2", 'id'=>'company_id', 'style'=>"width: 100%;", 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.company'), 'data-placeholder' => trans('labels.dashboard.company'), 'data-allow-clear' => 'true', 'disabled'=>true, 'target-data' => 'department_id', 'target-location-data'=>'location_id']) }}
                                        {{ Form::select('company_id', $dtCompanies, "", ['class' => "form-control select2", 'id'=>'dtcompany_id', 'style'=>"width: 100%;", 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.company'), 'data-placeholder' => trans('labels.dashboard.company'), 'data-allow-clear' => 'true', 'disabled'=>true, 'target-data' => 'department_id', 'target-location-data'=>'location_id']) }}
                                    </div>
                                @endif
                                <!-- Location selection dropdown start !-->
                                @if($role->slug != 'counsellor' && $role->slug != 'wellbeing_specialist' && $role->slug != 'health_coach' && $role->slug != 'wellbeing_team_lead')
                                     <div class="form-group" id="location">
                                        @if($role->group == 'zevo')
                                        {{ Form::select('location_id', $locations, null,['class' => 'form-control select2', 'id'=>'location_id', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.location'), 'data-placeholder' => trans('labels.dashboard.location'), 'data-allow-clear' => 'true', 'disabled' => true]) }}
                                        @else
                                        {{ Form::select('location_id', $locations, null,['class' => 'form-control select2', 'id'=>'location_id', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.location'), 'data-placeholder' => trans('labels.dashboard.location'), 'data-allow-clear' => 'true',  'disabled' => (($role->group == 'company' || ($role->group == 'reseller' && $company->parent_id != null)) ? false : true)]) }}
                                        @endif
                                    </div>
                                @else
                                    <div class="form-group" style="display: none">
                                        {{ Form::select('location_id', $locations, null,['class' => 'form-control select2', 'id'=>'location_id', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.location'), 'data-placeholder' => trans('labels.dashboard.location'), 'data-allow-clear' => 'true']) }}
                                    </div>
                                @endif
                                <!-- Location selection dropdown end !-->
                                @if(($role->group == 'zevo' || $role->group == 'company') && $role->slug != 'wellbeing_specialist' && $role->slug != 'health_coach' && $role->slug != 'wellbeing_team_lead')
                                <div class="form-group" id="age">
                                    {{ Form::select('age', $age, null,['class' => 'form-control select2 age', 'id'=>'age', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.age'), 'data-placeholder' => trans('labels.dashboard.age'), 'data-allow-clear' => 'true']) }}
                                </div>
                                @endif
                                <!-- Department selection dropdown start !-->
                                @if($role->slug != 'counsellor' && $role->slug != 'wellbeing_specialist' && $role->slug != 'health_coach' && $role->slug != 'wellbeing_team_lead')
                                @if($role->group == 'zevo' || $role->group == 'company' || $role->group == 'reseller')
                                <div class="form-group" id="department">
                                    @if($role->group == 'zevo')
                                    {{ Form::select('department_id', [], null,['class' => 'form-control select2', 'id'=>'department_id', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.department'), 'data-placeholder' => trans('labels.dashboard.department'), 'data-allow-clear' => 'true', 'disabled' => true]) }}
                                    @else
                                    {{ Form::select('department_id', [], null,['class' => 'form-control select2', 'id'=>'department_id', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.department'), 'data-placeholder' => trans('labels.dashboard.department'), 'data-allow-clear' => 'true', 'disabled' => true]) }}
                                    @endif
                                </div>
                                @else
                                <div class="form-group" style="display: none">
                                    {{ Form::select('department_id', $departments, null,['class' => 'form-control select2', 'id'=>'department_id', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.department'), 'data-placeholder' => trans('labels.dashboard.department'), 'data-allow-clear' => 'true', 'disabled' => true]) }}
                                </div>
                                @endif
                                @else
                                <div class="form-group" style="display: none">
                                    {{ Form::select('department_id', $departments, null,['class' => 'form-control select2', 'id'=>'department_id', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.department'), 'data-placeholder' => trans('labels.dashboard.department'), 'data-allow-clear' => 'true']) }}
                                </div>
                                @endif
                                <!-- Department selection dropdown ends !-->    
                                @if($role->slug != 'counsellor' && $role->slug != 'wellbeing_specialist' && $role->slug != 'health_coach')
                                <div class="form-group" id="service" style="display: none;">
                                    {{ Form::select('service_id', array('all' => 'All') + $services, null,['class' => 'form-control select2 service', 'id'=>'service_id', 'style'=>'width: 100%;', 'autocomplete' => 'off', 'placeholder' => trans('labels.dashboard.service'), 'data-placeholder' => trans('labels.dashboard.service'), 'data-allow-clear' => 'false']) }}
                                </div>
                                @endif
                                {{ Form::hidden('companiesId', $companiesId, ['id' => 'companiesId']) }}
                                {{ Form::hidden('companiesId', !empty($dtCompaniesId) ? $dtCompaniesId : null, ['id' => 'dtCompaniesId']) }}
                                {{ Form::hidden('serviceIds', !empty($serviceIds) ? $serviceIds : null, ['id' => 'serviceIds']) }}
                                {{ Form::hidden('roleType', $resellerType, ['id' => 'roleType']) }}
                                {{ Form::hidden('roleSlug', $role->slug, ['id' => 'roleSlug']) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
