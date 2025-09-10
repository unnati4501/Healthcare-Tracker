@if($edit)
{{ Form::hidden('company_id', $recordData->id , ['id'=>'company_id']) }}
@endif
@if($user_role->group == 'zevo')
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-lg-6 col-xl-4">
                @if(!$edit)
                <div class="form-group mb-0">
                    {{ Form::label('is_reseller', 'Is Reseller?') }}
                    <div class="">
                        <label class="custom-radio" for="is_reseller_yes">
                            Yes
                            {{ Form::radio('is_reseller', 'yes', ('yes' == request()->get('is_reseller')), ['class' => 'custom-control-input', 'id' => 'is_reseller_yes', (($companyType=='zevo') ? "disabled" : "")]) }}
                            <span class="checkmark">
                            </span>
                            <span class="box-line">
                            </span>
                        </label>
                        <label class="custom-radio" for="is_reseller_no">
                            No
                            {{ Form::radio('is_reseller', 'no', (('no' == request()->get('is_reseller')) || is_null(request()->get('is_reseller'))), ['class' => 'custom-control-input', 'id' => 'is_reseller_no']) }}
                            <span class="checkmark">
                            </span>
                            <span class="box-line">
                            </span>
                        </label>
                        <span id="reseller_loader" style="display: none;">
                            <i class="fas fa-spinner fa-lg fa-spin">
                            </i>
                            <span class="ms-1">
                                Loading data...
                            </span>
                        </span>
                    </div>
                </div>
                @else
                <div class="callout">
                    <div class="m-0">
                        {{ __('Is Reseller? ') }}
                        <div class="fw-bold">
                            {{ ((($recordData->is_reseller) && is_null($recordData->parent_id)) ? "Yes" : "No") }}
                            {{ Form::hidden('is_reseller',((($recordData->is_reseller) && is_null($recordData->parent_id)) ? "yes" : "no")) }}
                        </div>
                    </div>
                </div>
                @endif
            </div>
            <div class="col-lg-6 col-xl-4">
                @if(!$edit)
                    <div class="form-group mb-0" data-parent-co-wrapper="" style="display: block;">
                        {{ Form::label('parent_company', 'Parent Company') }}
                        @if($companyType=='reseller')
                        {{ Form::select('parent_company', $parent_comapnies, ($recordData->parent_id ?? request()->get('parent_company')), ['class' => 'form-control select2','placeholder' => 'Select parent company','data-placeholder' => 'Select parent company','id' => 'parent_company', 'data-allow-clear' => 'false',(($companyType=='zevo') ? "disabled" : "")] ) }}
                        @endif
                        @if($companyType=='zevo')
                        {{ Form::select('parent_company', $parent_comapnies, ($recordData->parent_id ?? request()->get('parent_company')), ['class' => 'form-control select2', 'id' => 'parent_company', 'data-allow-clear' => 'false',(($companyType=='zevo') ? "disabled" : "")] ) }}
                        {{ Form::hidden('parent_company', 'zevo') }}
                        @endif
                        <div class="mt-1" id="parent_co_loader" style="display: none;">
                            <i class="fas fa-spinner fa-lg fa-spin">
                            </i>
                            <span class="ms-1">
                                Loading data...
                            </span>
                        </div>
                    </div>
                @elseif($edit && !empty($parent_comapnies))
                    <div class="callout">
                        <div class="m-0">
                            {{ __('Parent Company') }}
                            <div class="fw-bold">
                                {{ $parent_comapnies }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="col-lg-6 col-xl-4">
                @if($edit)
                <div class="callout">
                    <div class="m-0">
                        {{ trans('labels.company.code') }} :
                        <div class="fw-bold">
                            {{ $recordData->code }}
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@else
{{ Form::hidden('is_reseller', 'no') }}
{{ Form::hidden('parent_company', $parent_comapnies) }}
@endif
<div class="card form-card">
    <div class="card-body">
        @php
        $brandingClass = "";
        if($companyType == 'zevo'){
            $brandingClass = "d-none";
        }

        $digitalTherapyTabClass = "d-none";
        if((!empty($selectedPlan) && ( $selectedPlan == 1 || $selectedPlan == 2)) || (!empty($recordData) && ($recordData->eap_tab==1)) ){
            $digitalTherapyTabClass = "";
        }
        @endphp
        <div class="stepwizard-wrapper vertical-stepwizard">
            <div class="stepwizard align-self-center">
                <div class="stepwizard-panel">
                    <div class="wizard flex-grow-1">
                        <div class="wizard-inner flex-grow-1">
                            <ul class="nav nav-tabs mw-100" role="tablist">
                                <li class="companyDetails-content comanytabs disabled active">
                                    <a href="#step-1" data-bs-toggle="tab" role="tab"
                                        aria-expanded="true" class="active show" aria-selected="true"><span
                                            class="round-tab"></span> <i>Company Details</i></a>
                                </li>
                                <li class="moderatorsDetails-content comanytabs disabled">
                                    <a href="#step-2" data-bs-toggle="tab" role="tab"
                                        aria-expanded="false" aria-selected="false"><span
                                            class="round-tab"></span> <i>Moderator Details</i></a>
                                </li>
                                <li class="domainBranding-content comanytabs disabled {{$brandingClass}}">
                                    <a href="#sub-domain-branding" data-bs-toggle="tab" role="tab"
                                        aria-expanded="false" aria-selected="false"><span
                                            class="round-tab"></span> <i>Domain Branding</i></a>
                                </li>
                                <li class="portalBranding-content comanytabs disabled {{$brandingClass}}">
                                    <a href="#sub-portal-branding" data-bs-toggle="tab" role="tab"
                                        aria-expanded="false" aria-selected="false"><span
                                            class="round-tab"></span> <i>Portal Branding</i></a>
                                </li>
                                <li class="enableSurvey-content comanytabs disabled {{$brandingClass}}">
                                    <a href="#sub-step-survey" data-bs-toggle="tab" role="tab"
                                        aria-expanded="false" aria-selected="false"><span
                                            class="round-tab"></span> <i>Enable Survey</i></a>
                                </li>
                                <li class="enableLocation-content comanytabs disabled">
                                    <a href="#step-3" data-bs-toggle="tab" role="tab"
                                        aria-expanded="false" aria-selected="false"><span
                                            class="round-tab"></span> <i>Location Details</i></a>
                                </li>
                                <li class="enableDigitalTherapy-content comanytabs disabled {{$digitalTherapyTabClass}}">
                                    <a href="#step-4" data-bs-toggle="tab" role="tab"
                                        aria-expanded="false" aria-selected="false"><span
                                            class="round-tab"></span> <i>Digital Therapy</i></a>
                                </li>
                                <li class="enableManageContent-content comanytabs disabled ">
                                    <a href="#step-5" data-bs-toggle="tab" role="tab"
                                        aria-expanded="false" aria-selected="false"><span
                                            class="round-tab"></span> <i>Manage Content</i></a>
                                </li>
                            </ul>

                        </div>
                    </div>
                </div>
                <div class="stepwizard-content-wrapper">
                    <div class="tab-content"  id="companyAddStep">
                        <h3 style="display: none">Company details</h3> 
                        <div class="tab-pane active companySteps" role="tabpanel" id="step-1" class="step-1" data-step="0">
                            @include('admin.companies.steps.company-details')
                        </div>
                        <h3 style="display: none">Moderators</h3>
                        <div class="tab-pane companySteps" role="tabpanel" id="step-2" class="step-2"  data-step="1">
                            @include('admin.companies.steps.moderators', ['edit' => $edit])
                        </div>
                        <h3 style="display: none" class="d-none">Domain branding</h3>
                        <div class="tab-pane domainBranding-content companySteps" role="tabpanel" id="sub-domain-branding" data-step="2">
                            @include('admin.companies.steps.domain-branding')
                        </div><h3 style="display: none">Portal branding</h3>
                        <div class="tab-pane portalBranding-content companySteps" role="tabpanel" id="sub-portal-branding" data-step="3">
                            @include('admin.companies.steps.portal-branding')
                        </div>
                        <h3 style="display: none">Survey</h3>
                        <div class="tab-pane enableSurvey-content companySteps" role="tabpanel" id="sub-step-survey" data-step="4">
                            @include('admin.companies.steps.enable-survey')
                        </div>
                        <h3 style="display: none">Location Content</h3>
                        <div class="tab-pane enableLocation-content companySteps" role="tabpanel" id="step-3" data-step="6">
                            @include('admin.companies.steps.location-details')
                        </div>
                        <h3 style="display:none">Digital Therapy</h3>
                        <div class="tab-pane enableDigitalTherapy-content companySteps" role="tabpanel" id="step-4" data-step="7">
                            @include('admin.companies.steps.digital-therapy', ['edit' => $edit])
                        </div>   
                         @if($isShowContentType)
                        <h3 style="display: none">Manage Content</h3>
                        <div class="tab-pane enableManageContent-content companySteps" role="tabpanel" id="step-5" data-step="8">
                            @include('admin.companies.steps.manage-content')   
                        </div>
                        @endif

                    </div>
                    {{-- <div class="save-cancel-wrap">

                        <button type="button" class="btn prev-step prev-step-sm" disabled="disabled"><span
                                class="button-arrow"><i class="far fa-arrow-left"></i></span></button>
                        <div>
                            <!-- <button type="button" class="btn btn-outline-primary me-2" onclick="window.location.replace('manage-survey-list.html');">Cancel</button> -->
                            <button type="button" class="btn btn-primary next-step right-arrow-btn"><span
                                    class="nextstep-text">Next</span> <span class="button-arrow"><i
                                        class="far fa-arrow-right"></i></span></button>
                        </div>

                    </div> --}}
                </div>

            </div>
        </div>
    </div>
</div>
<script id="add-new-slot-template" type="text/html">
    @include('admin.companies.add-new-slot')
</script>
<script id="edit-slot-template" type="text/html">
    @include('admin.companies.edit-preview', [
        'start_time' => '#start_time#',
        'end_time' => '#end_time#',
        'id' => ''
    ])
</script>
<script id="preview-slot-template" type="text/html">
    @include('admin.companies.slot-preview', [
        'start_time' => '#start_time#',
        'end_time' => '#end_time#',
        'time' => '#time#',
        'key' => '#key#',
        'id' => '#id#',
        'ws_selected' => '#ws_selected#',
        'ws_hidden_field' => '#ws_hidden_fields#'
    ])
</script>
<script id="preview-ws-slot-template" type="text/html">
    @include('admin.companies.slot-ws-preview', [
        'ws_name' => '#ws_name#',
        'value' => '#value#',
        'key' => '#key#',
        'id' => '#id#'
    ])
</script>
<script id="preview-ws-hidden-template" type="text/html">
    @include('admin.companies.slot-ws-hidden', [
        'ws_name' => '#ws_name#',
        'value' => '#value#',
        'key' => '#key#',
        'id' => '#id#'
    ])
</script>
<script id="add-new-slot-location-template" type="text/html">
    @include('admin.companies.steps.digitaltherapy.location-general-slots.add-new-slot')
</script>
<script id="edit-slot-location-template" type="text/html">
    @include('admin.companies.steps.digitaltherapy.location-general-slots.edit-preview', [
        'start_time' => '#start_time#',
        'end_time' => '#end_time#',
        'id' => '',
        'from' => '#from#'
    ])
</script>
<script id="preview-slot-location-template" type="text/html">
    @include('admin.companies.steps.digitaltherapy.location-general-slots.slot-preview', [
        'start_time' => '#start_time#',
        'end_time' => '#end_time#',
        'time' => '#time#',
        'key' => '#key#',
        'id' => '#id#',
        'ws_selected' => '#ws_selected#',
        'ws_hidden_field' => '#ws_hidden_fields#',
        'from' => '#from#'
    ])
</script>
<script id="preview-ws-slot-template-location" type="text/html">
    @include('admin.companies.steps.digitaltherapy.location-general-slots.slot-ws-preview', [
        'ws_name' => '#ws_name#',
        'value' => '#value#',
        'key' => '#key#',
        'id' => '#id#'
    ])
</script>
<script id="preview-ws-location-hidden-template" type="text/html">
    @include('admin.companies.steps.digitaltherapy.location-general-slots.slot-ws-hidden', [
        'ws_name' => '#ws_name#',
        'value' => '#value#',
        'key' => '#key#',
        'id' => '#id#'
    ])
</script>
@include('admin.companies.remove-slot-model-box')
@include('admin.companies.steps.digitaltherapy.location-general-slots.remove-slot-model-box')