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
                @permission('create-survey-category')
                <a class="btn btn-primary" href="{{ route('admin.surveycategories.create') }}">
                    <i class="far fa-plus me-3 align-middle">
                    </i>
                    <span class="align-middle">
                        {{ trans('surveycategories.buttons.add') }}
                    </span>
                </a>
                @endauth
                @endif
                @if(isset($back) && $back == true)
                <a class="back-link" href="{{ route('admin.surveycategories.index') }}">
                    {{ trans('buttons.general.back') }}
                </a>
                @endif
            </div>
        </div>
    </div>
</div>