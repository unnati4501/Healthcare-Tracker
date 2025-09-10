<!-- Content Header (Page header) -->
<div class="content-header">
    <div class="container-fluid">
        <div class="d-md-flex justify-content-between">
            <div class="align-self-center">
                <h1>{{ $appPageTitle }}</h1>
                @if(!empty($breadcrumb))
                    {{ Breadcrumbs::render($breadcrumb) }}
                @endif
            </div>
            <div class="align-self-center">
                @if($create)
                @permission('create-group')
                <a class="btn btn-primary" href="{!! route('admin.groups.create') !!}">
                    <i class="far fa-plus me-3 align-middle"></i>
                    <span class="align-middle">
                        {{trans('group.buttons.add_group')}}
                    </span>
                </a>
                @endauth
                @endif
                @if($edit && $groupData->created_by == 'Admin')
                <a class="btn btn-primary" href="{!! route('admin.groups.edit',$groupData->id) !!}">
                    <i class="far fa-edit me-3 align-middle">
                    </i>
                    <span class="align-middle">
                        {{ trans('buttons.general.edit') }}
                    </span>
                </a>
                @endif
                @if($back)
                <a href="{{ route('admin.groups.index').$string }}" class="btn btn-outline-primary"><i class="far fa-arrow-left me-3 align-middle"></i>  <span class="align-middle">{{trans('buttons.general.back')}}</span></a>
                @endif
            </div>
        </div>
    </div>
</div>