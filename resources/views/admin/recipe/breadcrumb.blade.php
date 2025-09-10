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
                @permission('create-recipe')
                <a class="btn btn-primary" href="{{ route('admin.recipe.create') }}">
                    <i class="far fa-plus me-3 align-middle">
                    </i>
                    <span class="align-middle">
                        {{ trans('recipe.buttons.add') }}
                    </span>
                </a>
                @endauth
                @endif
                @if(isset($back) && $back == true)
                <a class="back-link" href="{{ route('admin.recipe.index') }}">
                    {{ trans('buttons.general.back') }}
                </a>
                @endif
                @if(isset($editRecipe) && $editRecipe == true)
                <a class="btn btn-primary" href="{{ route('admin.recipe.edit', (!empty($recordData->id) ? $recordData->id : 0)) }}">
                    <i class="far fa-edit me-3 align-middle">
                    </i>
                    <span class="align-middle">
                        {{ trans('recipe.buttons.edit') }}
                    </span>
                </a>
                @endif
                @if(isset($backToListing) && $backToListing == true)
                <a class="btn btn-outline-primary" href="{{ route('admin.recipe.index') }}">
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