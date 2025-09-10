@permission('view-sessions')
@if(!$record->is_group)
    <a class="action-icon" href="{{ route('admin.cronofy.sessions.download-attachment', $record) }}" title="{{ trans('Cronofy.session_details.buttons.tooltips.download') }}">
        <i class="far fa-download">
        </i>
    </a>
    @if (strpos($from, 'sessions'))
        <a class="action-icon danger attachment-delete" data-bs-toggle="modal" href="javascript:vodi(0);" data-id="{{ $record->id }}" title="{{ trans('Cronofy.session_details.buttons.tooltips.delete') }}">
            <i class="far fa-trash-alt">
            </i>
        </a>
    @endif  
@endif
@endauth