function loadDT(table, type) {
    if (dtS[table] == undefined) {
        dtS[table] = $(table).DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: url.datatable,
                data: {
                    type: type
                }
            },
            columns: [{
                data: 'type',
                name: 'type'
            }, {
                data: 'value',
                name: 'value'
            }],
            paging: false,
            pageLength: pagination.value,
            lengthChange: false,
            searching: false,
            ordering: true,
            order: [
                [0, 'desc']
            ],
            info: true,
            autoWidth: false,
            columnDefs: [{
                targets: 'no-sort',
                orderable: false,
            }],
            destroy: true,
            language: {
                paginate: {
                    previous: pagination.previous,
                    next: pagination.next,
                }
            },
            dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        });
    } else {
        dtS[table].draw();
    }
}
$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $(document).on('shown.bs.tab', '#limitList a[data-bs-toggle="tab"]', function(e) {
        var target = $(e.target).attr("href");
        window.location.hash = target;
        e.stopImmediatePropagation();
        if (target == "#challengepoints") {
            loadDT('#challengePointManagment', 'challenge');
        } else if (target == "#rewardspoints") {
            loadDT('#rewardPointManagment', 'reward-point');
        } else if (target == "#rewardspointslimit") {
            loadDT('#rewardPointDailyLimitManagment', 'reward-daily-limit');
        }
    });
    $(document).on('click', '.btn-set-default', function(e) {
        var type = $(this).data('type');
        $('#set-default-model-box').data('type', type);
        $('#set-default-model-box').modal('show');
    });
    $(document).on('click', '#set-default-confirm', function(e) {
        var type = $('#set-default-model-box').data('type');
        $.ajax({
            url: url.default,
            type: 'POST',
            dataType: 'json',
            data: {
                type: type
            },
        }).done(function(data) {
            if (data.status == true) {
                $('#set-default-model-box').modal('hide');
                toastr.success(data.data);
                if (type == "challenge") {
                    loadDT('#challengePointManagment', 'challenge');
                } else if (type == "reward-point") {
                    loadDT('#rewardPointManagment', 'reward-point');
                } else if (type == "reward-daily-limit") {
                    loadDT('#rewardPointDailyLimitManagment', 'reward-daily-limit');
                }
            } else {
                toastr.error(data.data);
            }
        }).fail(function(error) {
            toastr.error(error.responseJSON.data || message.something_went_wrong);
        });
    });
});