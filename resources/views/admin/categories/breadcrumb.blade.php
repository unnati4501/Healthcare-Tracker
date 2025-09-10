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
                @permission('create-sub-category')
                <a class="btn btn-primary" href="{{ route('admin.subcategories.create') }}">
                    <i class="far fa-plus me-3 align-middle">
                    </i>
                    {{ trans('categories.buttons.add') }}
                </a>
                @endauth
                @endif
                @if(isset($back) && $back == true)
                <a class="btn btn-primary" href="{{ route('admin.categories.index') }}">
                    <i class="far fa-arrow-left me-3 align-middle">
                    </i>
                    <span class="align-middle">
                        {{ trans('labels.buttons.back') }}
                    </span>
                </a>
                @endif
            </div>
        </div>
    </div>
</div>