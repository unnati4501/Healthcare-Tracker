@if(!empty($record->id))
@permission('update-podcast')
<a class="action-icon" href="{{ route('admin.podcasts.edit', $record->id) }}" title="{{ trans('podcast.buttons.edit') }}">
    <i class="far fa-edit">
    </i>
</a>
@endauth
@permission('delete-podcast')
<a class="action-icon danger podcastDelete" data-id="{{ $record->id }}" href="javascript:void(0);" title="{{ trans('podcast.buttons.delete') }}">
    <i class="far fa-trash-alt">
    </i>
</a>
@endauth
@endif
