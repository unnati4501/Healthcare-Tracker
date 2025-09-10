<div class="content-header">
    <div class="container-fluid">
        <div class="d-md-flex justify-content-between">
            <div class="align-self-center">
                <h1>{{ $mainTitle }}</h1>
                @if(!empty($breadcrumb))
                    {{ Breadcrumbs::render($breadcrumb) }}
                @endif
            </div>
            <div class="align-self-center">
            @if($create)
            @permission('create-company-plan')
            <a class="btn btn-primary" href="{!! route('admin.company-plan.create') !!}">
                <i class="fal fa-plus me-2">
                </i>
                {{trans('companyplans.buttons.add_company_plan')}}
            </a>
            @endauth
            @endif
            </div>
        </div>
    </div>
</div>