@if($role->group == 'zevo' || ($role->group == 'reseller' && $company->parent_id == null))
@permission('update-company')
<a class="action-icon" href="{{ route('admin.companies.edit', [$roleGroupCompany ,$record->id]) }}" title="{{ trans('labels.buttons.edit_record') }}">
    <i class="far fa-edit">
    </i>
</a>
@endauth
{{-- @permission('add-moderator')
<a class="action-icon" href="{{ route('admin.companies.createModerator', [$roleGroupCompany, $record->id, 'referrer' => 'index']) }}" title="Add Moderator">
    <i class="far fa-plus">
    </i>
</a>
@endauth --}}
@endif
@if($role->group == 'zevo' || ($role->group == 'reseller' && $company->parent_id == null))
@permission('delete-company')
<a class="action-icon danger companyDelete" data-id="{{ $record->id }}" data-title="{{ $record->name }}" data-type="{{ $companyType }}" href="javascript:void(0);" title="Delete Record">
    <i class="far fa-trash-alt">
    </i>
</a>
@endauth
@endif
<div class="dropdown d-inline-block">
    <a aria-expanded="false" aria-haspopup="true" class="action-icon me-0" data-bs-toggle="dropdown"href="javascript:void(0);" title="More options">
        <i class="fas fa-ellipsis-v">
        </i>
    </a>
    <div aria-labelledby="dropdownMenuButton" class="dropdown-menu dropdown-menu-right">
        @if($role->group == 'zevo' || ($role->group == 'reseller' && $company->parent_id == null))
        @permission('get-teams')
        <a class="action-icon dropdown-item" href="{{ route('admin.companies.teams', [$roleGroupCompany, $record->id]) }}" title="Get Teams">
            <i class="far fa-users fa-fw me-2">
            </i>
            Get Teams
        </a>
        @endauth
        @endif
        @permission('manage-company-app-settings')
        @if($role->group == 'zevo' && $record->allow_app == true)
        <a class="action-icon dropdown-item" href="{{ route('admin.companies.changeAppSettingIndex', [$roleGroupCompany, $record->id]) }}" title="App Setting">
            <i class="far fa-cog fa-fw nav-icon me-2">
            </i>
            App Setting
        </a>
        @endif
        @endauth
        {{-- @if($role->group == 'zevo' || ($role->group == 'reseller' && $company->parent_id == null))
        @permission('view-moderator')
        @if($record->moderators->count() > 0)
        <a class="action-icon dropdown-item" href="{{ route('admin.companies.moderators', [$roleGroupCompany, $record->id]) }}" title="View Moderator">
            <i class="far fa-user fa-fw me-2">
            </i>
            View Moderator
        </a>
        @endauth
        @endif
        @endif --}}
        @permission('view-limits')
        @if($role->group == 'zevo')
        <a class="action-icon dropdown-item" href="{{ route('admin.companies.getLimits', [$roleGroupCompany, $record->id]) }}" title="View Limits">
            <i class="far fa-microchip fa-fw me-2">
            </i>
            View Limits
        </a>
        @endif
        @endauth
        @permission('export-survey-report')
        @if($zcsReportBtnVisibility)
        <a class="action-icon dropdown-item export-survey-report" data-id="{{ $record->id }}" href="javascript:void(0);" title="Export Survey Report">
            <i class="far fa-download fa-fw me-2">
            </i>
            Export Survey Report
        </a>
        @endif
        @endauth
        @permission('masterclass-survey-report')
        @if($mcsReportBtnVisibility)
        <a class="action-icon dropdown-item masterclass-survey-report" data-id="{{ $record->id }}" href="javascript:void(0);" title="Export Masterclass Survey Report">
            <i class="far fa-file-chart-line fa-fw me-2">
            </i>
            Export Masterclass Survey Report
        </a>
        @endif
        @endauth
        @permission('survey-configuration')
        @if($record->enable_survey)
        <a class="action-icon dropdown-item" href="{{ route('admin.companies.survey-configuration', [$roleGroupCompany, $record->id]) }}" title="Survey Configuration">
            <i class="far fa-users-cog fa-fw me-2">
            </i>
            Survey Configuration
        </a>
        @endif
        @endauth
        @permission('portal-footer')
        @if(($record->is_reseller && is_null($record->parent_id)) || (!$record->is_reseller && !is_null($record->parent_id)))
        <a class="action-icon dropdown-item" href="{{ route('admin.companies.portalFooter', [$roleGroupCompany, $record->id]) }}" title="Portal Footer">
            <i class="far fa-info-circle fa-fw me-2">
            </i>
            Portal Footer
        </a>
        @endif
        @endauth
        @permission('manage-credits')
        <a class="action-icon dropdown-item" href="{{ route('admin.companies.manageCredits', [$roleGroupCompany, $record->id]) }}" title="Manage Credits">
            <i class="far fa-credit-card fa-fw me-2">
            </i>
            Manage Credits
        </a>
        @endauth
        @permission('manage-dt-banners')
        @if($role->slug == 'super_admin' && !empty($companySlug))
        <a class="action-icon dropdown-item" href="{{ route('admin.companies.digitalTherapyBanners', [$roleGroupCompany, $record->id]) }}" title="DT Banners">
            <i class="far fa-image fa-fw me-2">
            </i>
            DT Banners
        </a>
        @endif
        @endauth
    </div> 
</div>
