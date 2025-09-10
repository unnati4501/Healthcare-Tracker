@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.cronofy.breadcrumb', [
    'appPageTitle' => trans('Cronofy.title.index_title'),
    'breadcrumb' => 'cronofy.index',
    'addCalendar' => true
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer" id="cronofyCalender-wrap">
                    <div class="table-responsive">
                        <table class="table custom-table" id="cronofyCalender">
                            <thead>
                                <tr>
                                    <th class="text-center" style="display: none">
                                        {{trans('Cronofy.table.updated_at')}}
                                    </th>
                                    <th class="no-sorting-arrow">
                                        {{ trans('Cronofy.table.email') }}
                                    </th>
                                    <th>
                                        {{ trans('Cronofy.table.primary_email') }}
                                        <span class="font-16 qus-sign-tooltip" data-toggle="help-tooltip" title="All your bookings will be added under the Primary calendar.">
                                            <i aria-hidden="true" class="far fa-info-circle text-primary">
                                            </i>
                                        </span>
                                    </th>
                                    <th>
                                        {{ trans('Cronofy.table.status') }}
                                    </th>
                                    <th>
                                        {{ trans('Cronofy.table.action') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Unlink calendar Model Popup -->
@include('admin.cronofy.unlink-model')
<!-- Primary calendar Model Popup -->
@include('admin.cronofy.primary-model')
@endsection
<!-- include datatable css -->
@section('after-scripts')
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script type="text/javascript">
    var url = {
    datatable: `{{ route('admin.cronofy.getCalendar') }}`,
    unlink: `{{ route('admin.cronofy.unlinkCalendar', ':id') }}`,
    primary: `{{ route('admin.cronofy.primary', ':id') }}`,
},
message = {
    calendar_unlink: `{{ trans('Cronofy.message.calendar_unlink') }}`,
    something_wrong: `{{ trans('Cronofy.message.something_wrong') }}`,
    calendar_primary: `{{ trans('Cronofy.message.calendar_primary') }}`,
};
</script>
<script type="text/javascript">
    $(document).ready(function() {
    $(document).on('click', '.primary-calendar', function(e) {
        $('#primary-model-box').data("id", $(this).data('id'));
        $('#primary-model-box').modal('show');
    });
    $(document).on('click', '#primary-model-box-confirm', function(e) {
        $('.page-loader-wrapper').show();
        var objectId = $('#primary-model-box').data("id");
        $.ajax({
            type: 'GET',
            url: url.primary.replace(':id', objectId),
            data: null,
            crossDomain: true,
            cache: false,
            contentType: 'json',
        })
        .done(function(data) {
            if (data.primary == 'true') {
                toastr.success(message.calendar_primary);
                location.reload();
            } else {
                toastr.error(message.something_wrong);
            }
        })
        .fail(function(data) {
            toastr.error(message.something_wrong);
        })
        .always(function() {
            $('#primary-model-box').modal('hide');
            $('.page-loader-wrapper').hide();
        });
    });
});
</script>
<script src="{{ asset('js/cronofy/index.js') }}" type="text/javascript">
</script>
@if((isset($wsDetails) && !$wsDetails->is_cronofy) || (isset($wcDetails) && !$wcDetails->is_cronofy) )
<script type="text/javascript">
    $(document).ready(function() {
        $('body.sidebar-mini').addClass('sidebar-collapse');
    });
</script>
@endif
@endsection
