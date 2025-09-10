$(document).ready(function() {
    var _hash = window.location.hash;
    if ($(`#myTabContent ${_hash}`).length > 0) {
        $(`#myTab a[href="${_hash}"]`).tab('show');
    }
    $('#date_range').daterangepicker({
        showDropdowns: true,
        autoUpdateInput: false,
        locale: {
            format: date_format,
            cancelLabel: messages.clear
        }
    }).on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format(date_format) + ' - ' + picker.endDate.format(date_format));
    }).on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });
    $('#myTab a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        var target = $(e.target).attr("href")
        window.location.hash = target;
        if (target == "#all") {
            $('#allSurveySuggestionManagment').DataTable().ajax.reload(null, false);
        } else if (target == "#favorites") {
            $('#favoSurveySuggestionManagment').DataTable().ajax.reload(null, false);
        }
    });
    $(document).on('click', '.set-favorite', function(e) {
        var _id = $(this).data('id');
        $('.toast').remove();
        $.ajax({
            type: 'GET',
            url: url.favorite.replace(':id', _id),
            contentType: 'json'
        }).done(function(data) {
            var _hash = window.location.hash;
            if (_hash == "" || _hash == "#all") {
                $('#allSurveySuggestionManagment').DataTable().ajax.reload(null, false);
            } else if (_hash == "#favorites") {
                $('#favoSurveySuggestionManagment').DataTable().ajax.reload(null, false);
            }
            if (data.status == true) {
                toastr.success(data.data);
            } else {
                toastr.error((data.data || messages.fail_to_load));
            }
        }).fail(function(data) {
            if (data == 'Forbidden') {
                toastr.error(messages.fail_to_load);
            }
        });
    });
});