<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title d-flex align-items-center">
                <span>
                    {{ trans('labels.dashboard.audit.company_score') }}
                </span>
                <i class="fas fa-info-circle ms-auto text-primary info-icon" data-placement="bottom" data-bs-toggle="tooltip" title="{{ trans('labels.dashboard.audit.company_score_help_text') }}">
                </i>
            </h3>
        </div>
        <div class="card-body">
            <div class="d-sm-flex justify-content-end">
                @include('newDashboard.partials.month-range-picker', ['tier' => 1, 'parentId' => 'daterangeAuditCompanyScore', 'fromId' => 'companyScoreFromMonth', 'toId' => 'companyScoreToMonth'])
            </div>
            <div class="row">
                <div class="col-lg-4">
                    <div class="speed-chart-area p-b-20 p-t-5">
                        <canvas class="chart-top-servey" data-companyscoregaugechart="">
                        </canvas>
                        <div class="speed-chart-text">
                            <span class="score-counter color-green" data-companyscoregaugechart-value="">
                                0%
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="exercise-hours-chart-area">
                        <canvas data-companyscorelinechart="" height="200" width="600">
                        </canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title d-flex align-items-center">
                <span>
                    {{ trans('labels.dashboard.audit.category_company_score') }}
                </span>
                <i class="fas fa-info-circle ms-auto text-primary info-icon" data-placement="bottom" data-bs-toggle="tooltip" title="Wellbeing Audit domain score.">
                </i>
            </h3>
        </div>
        <div class="card-body">
            <div class="text-center" id="audit_category_wise_company_score_loader">
                <i class="fas fa-spinner fa-lg fa-spin">
                </i>
                <span class="ms-1">
                    {{ trans('labels.dashboard.audit.loading_graphs') }}
                </span>
            </div>
            <div class="text-center" id="audit_category_wise_company_score_no_data" style="display: none;">
                <span class="ms-1">
                    {{ trans('labels.dashboard.audit.no_data_category_graphs') }}
                </span>
            </div>
            <div id="audit_category_wise_company_score_tab_wrapper">
                <div id="tabui">
                    <div class="tabs-wraper">
                        <div class="owl-carousel owl-theme" id="audit_category_wise_company_score_tab">
                        </div>
                    </div>
                    <div id="tabcontent">
                        <div class="tabInline-pane">
                            <div>
                                <div class="d-sm-flex justify-content-end">
                                    @include('newDashboard.partials.month-range-picker', ['tier' => 3, 'parentId' => 'daterangeAuditCategoryWiseCompanyScore', 'fromId' => 'categoryWiseCompanyScoreFromMonth', 'toId' => 'categoryWiseCompanyScoreToMonth'])
                                </div>
                                <div class="row">
                                    <div class="col-lg-12 col-xl-12 mb-5">
                                        <div class="exercise-hours-chart-area">
                                            <canvas data-categorywisecompanylinechart="" height="200" width="600">
                                            </canvas>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 mb-4">
                                        <div class="card h-100 mb-0">
                                            <div class="card-header">
                                                <h3 class="card-title d-flex align-items-center">
                                                    <span id="category_score_percentage">
                                                        Category Score %
                                                    </span>
                                                </h3>
                                            </div>
                                            <div class="card-body d-flex align-items-center">
                                                <div class="speed-chart-area p-b-20 p-t-5">
                                                    <canvas class="chart-top-servey" data-companycategoryscoregaugechart="">
                                                    </canvas>
                                                    <div class="speed-chart-text">
                                                        <span class="score-counter color-green" data-companycategoryscoregaugechart-value="">
                                                            0%
                                                        </span>
                                                    </div>
                                                    <a class="btn btn-primary btn-sm m-w-100 mt-2 go-to-question-report" href="javascript:void(0);" title="Go to question report">
                                                        Go to question report
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 mb-4">
                                        <div class="card h-100 mb-0">
                                            <div class="card-header">
                                                <h3 class="card-title d-flex align-items-center">
                                                    <span>
                                                        Percentage of Users vs Category score
                                                    </span>
                                                </h3>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-sm-8">
                                                        <div class="text-center max-w-250">
                                                            <canvas data-categorywisecompanydoughnutchart="" height="60" width="60">
                                                            </canvas>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-4 d-flex align-items-center">
                                                        <table class="table circle-colorfull-list">
                                                            <tbody>
                                                                <tr>
                                                                    <td class="singel-circle">
                                                                        <div class="singel-circle-obese">
                                                                        </div>
                                                                    </td>
                                                                    <td class="circle-text">
                                                                        Low- 0-60%
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="singel-circle">
                                                                        <div class="singel-circle-overweight">
                                                                        </div>
                                                                    </td>
                                                                    <td class="circle-text">
                                                                        Moderate - 60- 80%
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="singel-circle">
                                                                        <div class="singel-circle-normal">
                                                                        </div>
                                                                    </td>
                                                                    <td class="circle-text">
                                                                        High - 80- 100%
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="card h-100 m-0">
                                            <div class="card-header">
                                                <h3 class="card-title d-flex align-items-center">
                                                    <span>
                                                        Subcategory Score %
                                                    </span>
                                                    <i class="fas fa-info-circle ms-auto text-primary info-icon" data-placement="bottom" data-bs-toggle="tooltip" title="Wellbeing Audit sub domain score.">
                                                    </i>
                                                </h3>
                                            </div>
                                            <div class="card-body">
                                                <div class="text-center" id="audit_subcategory_wise_company_score_loader" style="display: none;">
                                                    <i class="fas fa-spinner fa-lg fa-spin">
                                                    </i>
                                                    <span class="ms-1">
                                                        {{ trans('labels.dashboard.audit.loading_subcategories_graphs') }}
                                                    </span>
                                                </div>
                                                <div class="text-center" id="audit_subcategory_wise_company_score_no_data" style="display: none;">
                                                    <span class="ms-1">
                                                        {{ trans('labels.dashboard.audit.no_data_subcategory_graphs') }}
                                                    </span>
                                                </div>
                                                <div class="row" id="subCategoryWiseCompanyScoreGraph" style="display: none;">
                                                    <div class="col-lg-4 offset-lg-8">
                                                        <div class="form-group">
                                                            <div class="input-daterange daterange--in-header" data-tier="4" id="subcategoryList">
                                                                {{ Form::select('audit-subcategory', [], null, ['class' => 'form-control select2', 'id' => 'audit-subcategory', 'style' => 'width:100%;']) }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-12">
                                                        <div class="exercise-hours-chart-area">
                                                            <canvas data-subcategorywiselinechart="" height="200" width="600">
                                                            </canvas>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
