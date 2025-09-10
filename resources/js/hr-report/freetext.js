$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $('.monthranges').datepicker({
        format: "M, yyyy",
        startView: 1,
        minViewMode: 1,
        maxViewMode: 2,
        clearBtn: true,
        autoclose: true,
        endDate: ((moment().isSame(moment().endOf('month'), 'date')) ? moment().endOf('month').add(1, 'd').toDate() : moment().endOf('month').toDate())
    });
    $('#globalFromMonth').datepicker("setDate", ((requestParams.from != undefined) ? moment(requestParams.from).toDate() : ""));
    $('#globalToMonth').datepicker("setDate", ((requestParams.to != undefined) ? moment(requestParams.to).toDate() : ""));
    $(document).on('click', '#hrReportSearchSubmitFrm', function(e) {
        e.preventDefault();
        var action = $('#hrReportSearch').attr('action'),
            now = moment(),
            fromDate = moment($('#globalFromMonth').datepicker("getDate")),
            endDate = moment($('#globalToMonth').datepicker("getDate")).endOf('month'),
            company = ($('#company').val() || 0),
            category = ($('#category').val() || 0),
            data = {
                category: category,
            };
        if ($('#globalFromMonth').val() != "" && $('#globalToMonth').val() != "") {
            if (endDate > now) {
                endDate = now;
            }
            data.from = fromDate.format('YYYY-MM-DD 00:00:00');
            data.to = endDate.format('YYYY-MM-DD 23:59:59');
        } else {
            $('#globalFromMonth, #globalToMonth').val('');
        }
        if (company > 0) {
            data.company = company;
        }
        var qString = $.param(data);
        window.location.href = action + ((qString != '') ? '?' + qString : '');
    });
    if (_category != '' || _dates != '' || _company != '') {
        $('#searchpanel').removeClass('collapsed-card');
    } else {
        $('#searchpanel').addClass('collapsed-card');
    }
    $(document).on('change', '#company', function(e) {
        var _value = ($(this).val() || ''),
            _categoryUrl = categoryUrl.replace(':id', _value);
        $.ajax({
            url: _categoryUrl,
            type: 'GET',
            dataType: 'text',
        }).done(function(data) {
            $('#category').select2('destroy');
            $('#category').html(data).val('').select2();
        }).fail(function(error) {
            $('#category').select2('destroy');
            $('#category').html('').val('').select2();
            alert('Failed to load categoires, please try again');
        });
    });
});