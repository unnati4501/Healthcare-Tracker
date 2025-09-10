@permission('update-moods')
<a class="action-icon" href="{{route('admin.moods.edit', $record->id)}}" title="{{ trans('moods.buttons.tooltips.edit') }}">
    <i class="far fa-edit">
    </i>
</a>
@endauth
@permission('delete-moods')
<a class="action-icon danger" data-id="{{$record->id}}" href="javaScript:void(0)" id="deleteModal" title="{{ trans('moods.buttons.tooltips.delete') }}">
    <i class="far fa-trash-alt">
    </i>
</a>
@endauth
