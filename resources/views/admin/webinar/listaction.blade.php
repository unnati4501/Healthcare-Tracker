@if(!empty($record->id))
@permission('edit-webinar')
<a class="action-icon" href="{{route('admin.webinar.edit', $record->id)}}" title="{{trans('buttons.general.tooltip.edit')}}">
    <i aria-hidden="true" class="far fa-edit">
    </i>
</a>
@endauth
@permission('delete-webinar')
<a class="action-icon danger" data-id="{{$record->id}}" href="javaScript:void(0)" id="webinarDelete" title="{{trans('buttons.general.tooltip.delete')}}">
    <i aria-hidden="true" class="far fa-trash-alt">
    </i>
</a>
@endauth
@endif
