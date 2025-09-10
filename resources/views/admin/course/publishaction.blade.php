@if($record->status == 1)
@if($record->courseUserLogs()->count() > 0)
<span class="text-success">
    {{ trans('masterclass.buttons.published') }}
</span>
@else
<a class="btn btn-outline-primary badge-btn publishCourse" data-action="unpublish" data-id="{{ $record->id }}" href="javascript:void(0);">
    {{ trans('masterclass.buttons.unpublish') }}
</a>
@endif
@else
<a class="btn btn-primary badge-btn publishCourse" data-action="publish" data-id="{{$record->id}}" href="javascript:void(0);">
    {{ trans('masterclass.buttons.publish') }}
</a>
@endif
