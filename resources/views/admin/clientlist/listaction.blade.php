@permission('view-clients')
<a class="action-icon" href="{{ route('admin.clientlist.details', $record->id) }}" title="{{ trans('buttons.general.tooltip.view') }}">
    <i class="far fa-edit">
    </i>
</a>
@endauth
