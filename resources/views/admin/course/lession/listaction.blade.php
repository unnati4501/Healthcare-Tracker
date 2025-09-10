<a class="action-icon" href="{{ route('admin.masterclass.editLession', $record->id) }}" title="{{ trans('masterclass.lesson.buttons.edit') }}">
    <i class="far fa-edit">
    </i>
</a>
@if($record->status == 0)
<a class="action-icon danger courseLessonDelete" data-id="{{$record->id}}" href="javascript:void(0);" title="{{ trans('masterclass.lesson.buttons.delete')}}">
    <i class="far fa-trash-alt">
    </i>
</a>
@endif
