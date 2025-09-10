@permission('draft-published-event')
<a class="action-icon" href="{{route('admin.reports.booking-report-comapny-wise', $record->company_id)}}" title="{{ trans('labels.buttons.view') }}">
    <i aria-hidden="true" class="far fa-eye">
    </i>
</a>
@endauth
