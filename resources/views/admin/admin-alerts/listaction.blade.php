@permission('edit-admin-alert')
<a class="action-icon" href="{{ route('admin.admin-alerts.edit', $record) }}" title="{{ trans('adminalert.buttons.tooltips.edit') }}">
    <i class="far fa-edit">
    </i>
</a>
@endauth