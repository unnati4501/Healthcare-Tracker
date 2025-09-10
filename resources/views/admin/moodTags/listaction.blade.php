@permission('update-mood-tags')
<a class="action-icon" href="{{route('admin.moodTags.edit', $record->id)}}" title="{{ trans('moods.tags.buttons.tooltips.edit') }}">
    <i class="far fa-edit">
    </i>
</a>
@endauth
@permission('delete-mood-tags')
<a class="action-icon danger" data-id="{{$record->id}}" href="javaScript:void(0)" id="deleteModal" title="{{ trans('moods.tags.buttons.tooltips.delete') }}">
    <i class="far fa-trash-alt">
    </i>
</a>
@endauth
