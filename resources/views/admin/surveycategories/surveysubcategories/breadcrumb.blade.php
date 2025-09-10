<div class="content-header">
    <div class="container-fluid">
        <div class="d-md-flex justify-content-between">
            <div class="align-self-center">
                <h1>
                    {{ $mainTitle }}
                </h1>
                @if(!empty($breadcrumb))
                    {!! $breadcrumb !!}
                @endif
            </div>
            <div class="align-self-center">
                @if(isset($create) && $create == true)
                @permission('create-survey-sub-category')
                <a class="btn btn-primary" href="{{ route('admin.surveysubcategories.create', (!empty(request()->surveycategory) ? request()->surveycategory->id : 0)) }}">
                    <i class="far fa-plus me-3 align-middle">
                    </i>
                    <span class="align-middle">
                        {{ trans('surveysubcategories.buttons.add') }}
                    </span>
                </a>
                @endauth
                @endif
                @if(isset($backToCategories) && $backToCategories == true)
                <a class="btn btn-outline-primary" href="{{ route('admin.surveycategories.index') }}">
                    <i class="far fa-arrow-left me-3 align-middle">
                    </i>
                    <span class="align-middle">
                        {{ trans('labels.buttons.back') }}
                    </span>
                </a>
                @endif
                @if(isset($back) && $back == true)
                <a class="back-link" href="{{ route('admin.surveysubcategories.index', (!empty(request()->surveycategory) ? request()->surveycategory->id : 0)) }}">
                    {{ trans('buttons.general.back') }}
                </a>
                @endif
            </div>
        </div>
    </div>
</div>