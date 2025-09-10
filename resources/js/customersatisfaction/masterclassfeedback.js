function loadGraph() {
    $('#graph-loader').show();
    $('#graph-area').empty();
    $.ajax({
        url: url.datatable,
        type: 'POST',
        dataType: 'json',
        data: {
            type: 'graph',
            category: $("#category").val(),
            course: $("#course").val(),
            company: $("#company").val(),
            author: $("#author").val(),
            feedback: $("#feedback").val(),
        },
    })
    .done(function(data) {
        var graphTemplate  = $('#graphTemplate').text().trim(),
            graphBarTemplate = $('#graphBarTemplate').text().trim(),
            graphLegendTemplate = $('#graphLegendTemplate').text().trim(),
            bars = legends = "";

        if(data.data && data.data.length > 0) {
            var length = (data.data.length - 1);
            $(data.data).each(function(index, bar) {
                bars += graphBarTemplate
                    .replace(/\#feedbackClass#/g, bar.class + ((length == index && bar.percentage <= 5) ? " small-bar-size" : ""))
                    .replace(/\#percentage#/g, bar.percentage.toFixed(2))
                    .replace(/\#tooltip#/g, `${bar.name}: ${bar.percentage.toFixed(2)}%`);
                legends += graphLegendTemplate
                    .replace(/\#feedbackClass#/g, bar.class)
                    .replace(/\#feedbackName#/g, bar.name);
            });
            graphTemplate = graphTemplate.replace('#bar#', bars).replace('#legend#', legends);
            $('#graph-area').html(graphTemplate);
        }
    })
    .fail(function(error) {
        toastr.error(message.failed_load_graph);
    })
    .always(function() {
        $('#graph-loader').hide();
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
}

$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // to load graph
    loadGraph();

    $('#mcFeedbackManagment').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        ajax: {
            type: 'POST',
            url: url.datatable,
            data: {
                type: 'listing',
                category: $("#category").val(),
                course: $("#course").val(),
                company: $("#company").val(),
                author: $("#author").val(),
                feedback: $("#feedback").val(),
                getQueryString: window.location.search
            },
        },
        columns: [{
            data: 'category_name',
            name: 'category_name',
        }, {
            data: 'course_title',
            name: 'course_title',
        }, {
            data: 'company_name',
            name: 'company_name',
        }, {
            data: 'author_name',
            name: 'author_name',
        }, {
            data: 'emoji',
            name: 'emoji',
            className: 'text-center',
            searchable: false,
            sortable: false,
            render: function(data, type, row) {
                return `<img class="tbl-user-img img-circle" src="${data}" width="70" />`;
            }
        }, {
            data: 'feedback_type',
            name: 'feedback_type',
        }, {
            data: 'feedback',
            name: 'feedback',
        }, {
            data: 'created_at',
            name: 'created_at',
            render: function(data, type, row) {
                return moment.utc(data).tz(timezone).format(date_format);
            }
        }],
        "drawCallback": function( settings ) {
            var api = this.api();
            if(api.rows().count()==0){
                $("#masterclassFeedbackExport").hide();
            }else {
                $("#masterclassFeedbackExport").show();
            }
        },
        pageLength: pagination.value,
        lengthChange: true,
        lengthMenu: [[25, 50, 100], [25, 50, 100]],
        searching: false,
        order: [],
        autoWidth: false,
        columnDefs: [{
            targets: 'no-sort',
            orderable: false,
        }],
        language: {
            "paginate": {
                "previous": pagination.previous,
                "next": pagination.next
            },
            "lengthMenu": pagination.entry_per_page + " _MENU_",
        },
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        buttons: []
    });

    $(document).on('change', '#category', function(e) {
        var category = ($(this).val() || 0);
        $.ajax({
            url: url.masterclassurl.replace(':id', category),
            type: 'POST',
            dataType: 'html',
            data: {
                category: category
            },
        })
        .done(function(data) {
            $('#course').html(data).val('').select2('destroy').select2();
        })
        .fail(function(error) {
            $('#course').html('').select2('destroy').select2();
            toastr.error(message.failed_load_category);
        });
    });

    $('.daterangesFromExportModel').datepicker({
        format: "yyyy-mm-dd",
        todayHighlight: false,
        autoclose: true,
        endDate: new Date(),
        clearBtn: true,
    });

    $(document).on('click', '#masterclassFeedbackExport', function(t) {
        var exportConfirmModalBox = '#export-model-box';
        var __startDate = $(this).attr('data-start');
        var __endDate = $(this).attr('data-end');
        $('#email').val(loginemail).removeClass('error');
        $('#queryString').val(JSON.stringify(get_query()));
        $('.error').remove();
        $("#model-title").html($(this).data('title'));
        $("#exportNpsReport").attr('action', url.masterclassFeedbackExportUrl);
        $('.loadingMsg').remove();
        $('#export-model-box-confirm').prop('disabled', false);
        $('#exportNpsReportMsg').hide();
        $('#exportNps').show();
        $(exportConfirmModalBox).attr("data-id", $(this).data('id'));
        $(exportConfirmModalBox).modal('show');
    });

    $('#exportNpsReport').validate({
        errorClass: 'error text-danger',
        errorElement: 'span',
        highlight: function(element, errorClass, validClass) {
            $('span#email-error').addClass(errorClass).removeClass(validClass);
        },
        unhighlight: function(element, errorClass, validClass) {
            $('span#email-error').removeClass(errorClass).addClass(validClass);
        },
        rules: {
            email: {
                email: true,
                required: true
            }
        }
    });
    $('#exportNpsReport').ajaxForm({
        beforeSend: function() {
            $(".page-loader-wrapper").fadeIn();
            $('#exportNpsReport .card-footer button, #exportIntercompanychallenge .card-footer a').attr('disabled', 'disabled');
        },
        success: function(data) {
            $('#exportNpsReport .card-footer button, #exportIntercompanychallenge .card-footer a').removeAttr('disabled');
            $('#export-model-box').modal('hide');
            if (data.status == 1) {
                toastr.success(data.data);
            } else {
                toastr.error(data.data);
            }
        },
        error: function(data) {
            $('#exportNpsReport .card-footer button, #exportIntercompanychallenge .card-footer a').removeAttr('disabled');
            if (data.responseJSON && data.responseJSON.message && data.responseJSON.message != '') {
                toastr.error(data.responseJSON.message);
            } else {
                toastr.error(message.somethingWentWrong);
            }
        },
        complete: function(xhr) {
            $(".page-loader-wrapper").fadeOut();
            $('#exportNpsReport .card-footer button, #exportIntercompanychallenge .card-footer a').removeAttr('disabled');
        }
    });
});

function get_query(){
    $('.daterangesFromExportModel').show();
    var url = document.location.href;
    var qs = url.substring(url.indexOf('?') + 1).split('&');
    for(var i = 0, result = {}; i < qs.length; i++){
        qs[i] = qs[i].split('=');
        if(qs[i][1] != undefined ){
            $('.daterangesFromExportModel').hide();
        }
        result[qs[i][0]] = decodeURIComponent(qs[i][1]);
    }
    return result;
}