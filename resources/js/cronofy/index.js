$(document).ready(function() {
	$('#cronofyCalender').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: url.datatable,
        },
        columns: [
            {data: 'calendar_id', name: 'calendar_id'},
            {data: 'primary', name: 'primary'},
            {data: 'status', name: 'status'},
            {data: 'action', name: 'action'}
        ],
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        paging: false,
        lengthChange: false,
        searching: false,
        ordering: true,
        order: [[0, 'asc']],
        info: true,
        autoWidth: false,
        stateSave: false
    });
    $(document).on('click', '.unlink-calendar', function(e) {
        $('#unlink-model-box').data("id", $(this).attr('profileId'));
        $('#unlink-model-box').modal('show');
    });
    $(document).on('click', '#unlink-model-box-confirm', function(e) {
        $('.page-loader-wrapper').show();
        var objectId = $('#unlink-model-box').data("id");

        $.ajax({
            type: 'GET',
            url: url.unlink.replace(':id', objectId),
            data: null,
            crossDomain: true,
            cache: false,
            contentType: 'json',
        })
        .done(function(data) {
            if (data.unlink == 'true') {
                toastr.success(message.calendar_unlink);
                location.reload();
            } else {
                toastr.error(message.something_wrong);
            }
        })
        .fail(function(data) {
            toastr.error(message.something_wrong);
        })
        .always(function() {
            $('#unlink-model-box').modal('hide');
            $('.page-loader-wrapper').hide();
        });
    });
});