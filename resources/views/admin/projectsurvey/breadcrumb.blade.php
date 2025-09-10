<div class="content-header">
    <div class="container-fluid">
        <div class="d-md-flex justify-content-between">
            <div class="align-self-center">
                <h1>{{ $mainTitle }}</h1>
                @if(!empty($breadcrumb))
                    {{ Breadcrumbs::render($breadcrumb) }}
                @endif
            </div>
            @if(isset($showbackbutton) && $showbackbutton === true)
            <div class="align-self-center">
                <a href="{!! route('admin.reports.nps','#projectTab') !!}" class="btn btn-outline-primary"><i class="far fa-arrow-left me-3 align-middle"></i>  <span class="align-middle">{{ trans('buttons.general.back') }}</span></a>
            </div>
            @endif
        </div>
    </div>
</div>