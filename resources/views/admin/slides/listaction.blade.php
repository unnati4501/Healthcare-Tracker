@if(!empty($slide->id))
@permission('update-onboarding')
<a class="action-icon" href="{{route('admin.appslides.edit', $slide->id)}}" title="{{ trans('buttons.general.edit') }}">
    <i aria-hidden="true" class="far fa-edit"></i>
</a>
@endauth
@permission('delete-onboarding')
<a href="javaScript:void(0)" class="action-icon danger delete-toast" title="{{ trans('buttons.general.delete') }}" data-id="{{$slide->id}}" id="slideDelete">
    <i class="far fa-trash-alt" aria-hidden="true" ></i>
</a>
@endauth
@endif