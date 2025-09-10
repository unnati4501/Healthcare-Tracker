@permission('view-sessions')
<a class="action-icon" href="{{ route('admin.sessions.show', $record->id) }}" title="{{ trans('calendly.buttons.tooltips.view') }}">
    <i class="far fa-edit">
    </i>
</a>
@endauth
