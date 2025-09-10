$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $('#contentChallengeActivityManagment').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: challengeActivityListUrl,
            data: {
                category: categoryId,
                getQueryString: window.location.search
            },
        },
        columns: [{
            data: 'id',
            name: 'id',
            visible: false
        }, {
            data: 'activity',
            name: 'activity'
        }, {
            data: 'daily_limit',
            name: 'daily_limit',
            searchable: false,
            render: function(data, type, row) {
                return `<span id="span_daily_limit_${row.id}">${row.daily_limit}</span>`
                +`<input type="text" placeholder="Daily Limit" class="form-control daily_limit" maxlength="50" style="display:none;" id="daily_limit_${row.id}" name="daily_limit[${row.id}]" value="${row.daily_limit}">`
                +`<input type="hidden" class="form-control" style="display:none;" id="id_${row.id}" name="id[${row.id}]" value="${row.daily_limit}">`; 
                    
            }
        }, {
            data: 'points_per_action',
            name: 'points_per_action',
            searchable: false,
            render: function(data, type, row) {
                return `<span id="span_points_per_action_${row.id}">${row.points_per_action}</span>`
                +`<input type="text" placeholder="Points per Action" class="form-control points_per_action" maxlength="50" style="display:none;" id="points_per_action_${row.id}" name="points_per_action[${row.id}]" value="${row.points_per_action}">`
                +`<input type="hidden" class="form-control" style="display:none;" id="id_${row.id}" name="id[${row.id}]" value="${row.points_per_action}">`; 
                    
            }
        }, {
            data: 'actions',
            name: 'actions',
            searchable: false,
            sortable: false
        }],
        paging: true,
        pageLength: pagination,
        lengthChange: false,
        searching: false,
        ordering: true,
        order: [
            [0, 'asc']
        ],
        info: true,
        autoWidth: false,
        columnDefs: [{
            targets: 'no-sort',
            orderable: false,
        }, {
            targets: 2,
            className: 'align-top',
        },
        {
            targets: 3,
            className: 'align-top',
        }],
        stateSave: false,
        language: {
            paginate: {
                previous: "<i class='far fa-angle-left page-arrow align-middle me-2'></i><span class='align-middle'>Prev</span>",
                next: "<span class='align-middle'>Next</span><i class='far fa-angle-right page-arrow align-middle ms-2'></i> "
            }
        },
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
    });

    $(document).on('click', '.edit_activity', function(e) {
        e.preventDefault();
        var activityId = ($(this).data('id') || 0);
        console.log(activityId);
        $("#points_per_action_"+activityId).show();
        $("#span_points_per_action_"+activityId).hide();
        $("#daily_limit_"+activityId).show();
        $("#span_daily_limit_"+activityId).hide();
        $("#save_activity_"+activityId).show();
        $("#edit_activity_"+activityId).hide();
    });

    $(document).on('click', '.save_activity', function(e) {
        e.preventDefault();
        var activityId = ($(this).data('id') || 0);
        $('#daily_limit_'+activityId+'-error').remove();
        $('#points_per_action_'+activityId+'-error').remove();
        var points_per_action = $("#points_per_action_"+activityId).val().trim();
        var daily_limit = $("#daily_limit_"+activityId).val().trim();
        var res = [];

        if (daily_limit == '') {
            $('#daily_limit_'+activityId).after('<div id="daily_limit_'+activityId+'-error" class="error error-feedback">'+message.daily_limit_required+'</div>');
            res.push("daily_limit");
        } else if (daily_limit == 0){
            $('#daily_limit_'+activityId).after('<div id="daily_limit_'+activityId+'-error" class="error error-feedback">'+message.greater_then_zero_allowed+'</div>');
            res.push("daily_limit");
        } else {
            removeFrmArr(res, 'daily_limit');
        }

        if (points_per_action == '') {
            $('#points_per_action_'+activityId).after('<div id="points_per_action_'+activityId+'-error" class="error error-feedback">'+message.points_per_action_required+'</div>');
            res.push("points_per_action");
        } else if (points_per_action == 0) {
            $('#points_per_action_'+activityId).after('<div id="points_per_action_'+activityId+'-error" class="error error-feedback">'+message.greater_then_zero_allowed+'</div>');
            res.push("points_per_action");
        }  else {
            removeFrmArr(res, 'points_per_action');
        }

        if (res.length <= 0) {
            $('.page-loader-wrapper').show();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'PATCH',
                url: challengeActivityUpdateUrl,
                crossDomain: true,
                cache: false,
                data: {
                    'daily_limit':daily_limit,
                    'points_per_action': points_per_action,
                    'activity_id': activityId,
                },
            }).done(function(data) {
                if (data.status == 1) {
                    toastr.success(data.data);
                } else {
                    toastr.error(data.data);
                }
                $('.page-loader-wrapper').hide();
            });
            $("#span_daily_limit_"+activityId).html(daily_limit);
            $("#daily_limit_"+activityId).attr('value',daily_limit);
            $("#daily_limit_"+activityId).hide();
            $("#span_daily_limit_"+activityId).show();
            $("#span_points_per_action_"+activityId).html(points_per_action);
            $("#points_per_action_"+activityId).attr('value',points_per_action);
            $("#points_per_action_"+activityId).hide();
            $("#span_points_per_action_"+activityId).show();
            $("#save_activity_"+activityId).hide();
            $("#edit_activity_"+activityId).show();
        }
    });

    $(document).on('keypress', '.points_per_action, .daily_limit', function(event) {
        if (event.shiftKey || (event.keyCode < 48 || event.keyCode > 57)) {
            event.preventDefault();
        }
    });
});

function removeFrmArr(array, element) {
    return array.filter(e => e !== element);
}