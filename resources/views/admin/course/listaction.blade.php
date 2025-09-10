@if(!empty($record->id))
@permission('update-course')
<a class="action-icon" href="{{ route('admin.masterclass.edit', $record->id) }}" title="{{ trans('masterclass.buttons.edit') }}">
    <i class="far fa-edit">
    </i>
</a>
@endauth
@permission('delete-course')
<a class="action-icon danger courseDelete" data-id="{{$record->id}}" href="javascript:void(0);" title="{{ trans('masterclass.buttons.delete') }}">
    <i class="far fa-trash-alt">
    </i>
</a>
@endauth
@permission('view-course')
<a class="action-icon" href="{{ route('admin.masterclass.view', $record->id) }}" title="{{ trans('masterclass.buttons.view') }}">
    <i class="far fa-eye">
    </i>
</a>
@endauth
@permission('manage-course-modules')
<a class="action-icon" href="{{ route('admin.masterclass.manageLessions', $record->id) }}" title="{{ trans('masterclass.buttons.lessons') }}">
    <i class="far fa-list">
    </i>
</a>
@endauth
@endif
