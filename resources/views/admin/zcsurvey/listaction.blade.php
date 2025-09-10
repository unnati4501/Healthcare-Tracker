<a class="action-icon copy-survey" data-id="{{$record->id}}" href="javascript:void(0);" title="Copy">
    <i class="far fa-copy">
    </i>
</a>
<a class="action-icon" href="{{ route('admin.zcsurvey.view-question', $record->id )}}" title="View questions">
    <i class="far fa-question-circle">
    </i>
</a>
@permission('update-survey')
@if($record->status == 'Draft')
<a class="action-icon publish-action" data-action="publish" data-id="{{$record->id}}" href="javascript:void(0);" title="Publish">
    <i class="far fa-save">
    </i>
</a>
@elseif($record->status == "Published" && $record->surveyreponses_count <= 0 && $record->surveylogs_count <= 0)
<a class="action-icon publish-action" data-action="unpublish" data-id="{{$record->id}}" href="javascript:void(0);" title="Unpublish">
    <i class="far fa-save">
    </i>
</a>
@endif
<br/>
@endauth
@permission('update-survey')
@if($record->status == 'Draft')
<a class="action-icon" href="{{ route('admin.zcsurvey.edit', $record->id )}}" title="Edit">
    <i class="far fa-edit">
    </i>
</a>
@endif

@endauth
@permission('delete-survey')
@if($record->status == 'Draft')
<a class="action-icon danger delete-survey" data-id="{{$record->id}}" href="javascript:void(0);" title="Delete">
    <i class="far fa-trash-alt">
    </i>
</a>
@endif
@endauth
