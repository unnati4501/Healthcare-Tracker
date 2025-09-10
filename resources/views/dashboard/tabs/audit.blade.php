<div class="card dashboard-card">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-5 align-self-center">
                <h3 class="card-inner-title border-0 pb-0 mb-0">
                    {{ trans('dashboard.audit.headings.wellbeing_audit') }}
                </h3>
            </div>
            <div class="col-md-7 mt-4 mt-md-0">
                @include('newDashboard.partials.month-range-picker', ['tier' => 1, 'parentId' => 'daterangeAuditCompanyScore', 'fromId' => 'companyScoreFromMonth', 'toId' => 'companyScoreToMonth', 'tooltip' => trans('dashboard.audit.tooltips.company_score_help_text')])
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3 border-lg-right">
                <div class="d-flex justify-content-around h-100 flex-column">
                    <h5>
                        {{ trans('dashboard.audit.headings.company_score') }}
                    </h5>
                    <div class="chart-height donut-chart-height small">
                        <div class="canvas-wrap">
                            <canvas class="canvas" data-companyscoregaugechart="">
                            </canvas>
                            <div class="canvas-center-data" data-companyscoregaugechart-value="">
                                0%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-9 mt-4 mt-lg-0 align-self-center">
                <div class="chart-height">
                    <div class="canvas-wrap">
                        {{-- height="200" width="600" --}}
                        <canvas class="canvas" data-companyscorelinechart="">
                        </canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="card dashboard-card mt-5">
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-5 align-self-center">
                <h3 class="card-inner-title border-0 pb-0 mb-0">
                    {{ trans('dashboard.audit.headings.category_company_score') }}
                </h3>
            </div>
            <div class="col-md-7 mt-4 mt-md-0">
                @include('newDashboard.partials.month-range-picker', ['tier' => 3, 'parentId' => 'daterangeAuditCategoryWiseCompanyScore', 'fromId' => 'categoryWiseCompanyScoreFromMonth', 'toId' => 'categoryWiseCompanyScoreToMonth', 'tooltip' => trans('dashboard.audit.tooltips.category_company_score')])
            </div>
        </div>
        <div class="text-center" id="audit_category_wise_company_score_loader">
            <i class="fas fa-spinner fa-lg fa-spin">
            </i>
            <span class="ms-1">
                {{ trans('dashboard.audit.messages.loading_graphs') }}
            </span>
        </div>
        <div class="text-center" id="audit_category_wise_company_score_no_data" style="display: none;">
            <span class="ms-1">
                {{ trans('dashboard.audit.messages.no_data_category_graphs') }}
            </span>
        </div>
        <div id="audit_category_wise_company_score_tab_wrapper">
            <div class="tabs-wraper">
                <div class="owl-carousel arrow-theme owl-theme p-0" id="audit_category_wise_company_score_tab">
                </div>
            </div>
            <div class="categoryscore-chart-wrapper">
                <div class="chart-height">
                    <div class="canvas-wrap">
                        <canvas class="canvas" data-categorywisecompanylinechart="" height="200" width="600">
                        </canvas>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 border-lg-right">
                    <h5 class="card-inner-title border-0 pb-0 mb-4">
                        {{ trans('dashboard.audit.headings.detailed_category_score') }}
                        <i class="fal fa-info-circle ms-2 text-primary tooltip-icon" data-original-title="tooltip" data-placement="bottom" data-bs-toggle="tooltip" title="{{ trans('dashboard.audit.tooltips.detailed_category_barometer') }}">
                        </i>
                    </h5>
                    <div class="row" style="height: calc(100% - 45px);">
                        <div class="col-xxl-6 col-xl-5 align-self-center">
                            <div class="chart-height donut-chart-height small">
                                <div class="canvas-wrap">
                                    <canvas class="chart-top-servey" data-companycategoryscoregaugechart="">
                                    </canvas>
                                    <div class="canvas-center-data" data-companycategoryscoregaugechart-value="">
                                        0%
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-6 col-xl-7 mt-4 mt-xl-0 align-self-center text-center">
                            <a class="btn btn-outline-primary go-to-question-report" href="javascript:void(0);">
                                {{ trans('dashboard.audit.buttons.question_report') }}
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mt-4 mt-lg-0 align-self-center">
                    <h5 class="card-inner-title border-0 pb-0 mb-4">
                        {{ trans('dashboard.audit.headings.percentage_vs_category_score') }}
                        <i class="fal fa-info-circle ms-2 text-primary tooltip-icon" data-original-title="tooltip" data-placement="bottom" data-bs-toggle="tooltip" title="{{ trans('dashboard.audit.tooltips.users_vs_category_barometer') }}">
                        </i>
                    </h5>
                    <div class="row">
                        <div class="col-xxl-6 col-xl-6 align-self-center">
                            <div class="chart-height donut-chart-height small">
                                <div class="canvas-wrap">
                                    <canvas data-categorywisecompanydoughnutchart="">
                                    </canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-6 col-xl-6 mt-4 mt-xl-0 align-self-center">
                            <div class="chart-legend">
                                <ul class="4-legend chart-legend" style="list-style:none">
                                    <li>
                                        <div class="legend-color" style="border-color:#f44436">
                                        </div>
                                        <span class="legend-text">
                                            Low
                                        </span>
                                        <span class="legend-info">
                                            (0-60%)
                                        </span>
                                    </li>
                                    <li>
                                        <div class="legend-color" style="border-color:#ffab00">
                                        </div>
                                        <span class="legend-text">
                                            Moderate
                                        </span>
                                        <span class="legend-info">
                                            (60-80%)
                                        </span>
                                    </li>
                                    <li>
                                        <div class="legend-color" style="border-color:#21c393">
                                        </div>
                                        <span class="legend-text">
                                            High
                                        </span>
                                        <span class="legend-info">
                                            (80-100%)
                                        </span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr class="mt-5 mb-5"/>
            <div class="row mb-4">
                <div class="col-md-5 align-self-center">
                    <h3 class="card-inner-title border-0 pb-0 mb-0">
                        {{ trans('dashboard.audit.headings.subcategory_score') }}
                    </h3>
                </div>
                <div class="col-md-7 mt-4 mt-md-0">
                    <div class="d-flex justify-content-end align-items-center">
                        <div class="me-3 w-50" data-tier="4" id="subcategoryList">
                            {{ Form::select('audit-subcategory', [], null, ['class' => 'form-control select2 w-100', 'id' => 'audit-subcategory']) }}
                        </div>
                        <div>
                            <a class="tooltip-icon" data-placement="bottom" data-bs-toggle="tooltip" href="javascript:void(0);" title="{{ trans('dashboard.audit.tooltips.subcategory_score') }}">
                                <i class="fal fa-info-circle">
                                </i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center" id="audit_subcategory_wise_company_score_loader" style="display: none;">
                <i class="fas fa-spinner fa-lg fa-spin">
                </i>
                <span class="ms-1">
                    {{ trans('dashboard.audit.messages.loading_subcategories_graphs') }}
                </span>
            </div>
            <div class="text-center" id="audit_subcategory_wise_company_score_no_data" style="display: none;">
                <span class="ms-1">
                    {{ trans('dashboard.audit.messages.no_data_subcategory_graphs') }}
                </span>
            </div>
            <div class="chart-height" id="subCategoryWiseCompanyScoreGraph" style="display: none;">
                <div class="canvas-wrap">
                    <canvas data-subcategorywiselinechart="" height="200" width="600">
                    </canvas>
                </div>
            </div>
        </div>
    </div>
</div>