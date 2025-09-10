$(document).ready(function() {

    const myModalEl = document.getElementById('export-model-box')
    myModalEl.addEventListener('shown.bs.modal', event => {
        $("#countrySearch").select2({
            dropdownParent: $("#export-model-box")
        });

        $("#timezoneSearch").select2({
            dropdownParent: $("#export-model-box")
        });

        $("#stateSearch").select2({
            dropdownParent: $("#export-model-box")
        });
    })

    //$('#countrySearch').select2('destroy');
   
    $('#locationManagment').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: url.datatable,
            data: {
                status: 1,
                locationName: $('#locationName').val(),
                country: $('#country').val(),
                companyId: $('#companyid').val(),
                timezone: condition.timezone,
                getQueryString: window.location.search
            },
        },
        columns: [
            {data: 'updated_at', name: 'updated_at', visible: false},
            {data: 'company_name', name: 'company_name', visible: condition.companyColVisibility},
            {data: 'name', name: 'name'},
            {data: 'country_name', name: 'country_name'},
            {data: 'state_name', name: 'state_name'},
            {data: 'timezone', name: 'timezone'},
            {data: 'address', name: 'address'},
            {data: 'actions', name: 'actions', searchable: false, sortable: false,  className: 'text-center'}
        ],
        "drawCallback": function( settings ) {
            var api = this.api();
            if(api.rows().count()==0){
                $("#locationExport").hide();
            }else {
                $("#locationExport").show();
            }
        },
        paging: true,
        pageLength: pagination.value,
        lengthChange: false,
        searching: false,
        ordering: true,
        order: [[0, 'desc']],
        info: true,
        autoWidth: false,
        columnDefs: [{
            targets: 'no-sort',
            orderable: false,
        }],
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        stateSave: false,
        language: {
            paginate: {
                "previous": pagination.previous,
                "next": pagination.next
            }
        }
    });

    $(document).on('click', '#locationDelete', function (t) {
        var deleteConfirmModalBox = '#delete-model-box';
        $(deleteConfirmModalBox).attr("data-id", $(this).data('id'));
        $(deleteConfirmModalBox).modal('show');
    });

    $(document).on('click', '#delete-model-box-confirm', function (e) {
        $('.page-loader-wrapper').show();
        var deleteConfirmModalBox = '#delete-model-box';
        var objectId = $(deleteConfirmModalBox).attr("data-id");

        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });

        $.ajax({
            type: 'DELETE',
            url: url.delete.replace(':id',objectId),
            data: null,
            crossDomain: true,
            cache: false,
            contentType: 'json',
            success: function (data) {
                $('#locationManagment').DataTable().ajax.reload(null, false);
                if (data['deleted'] == 'true') {
                    toastr.success(message.locationDeleted);
                } else if(data['deleted'] == 'use') {
                    toastr.error(message.locationInUse);
                } else {
                    toastr.error(message.unableToDeleteLocation);
                }
                var deleteConfirmModalBox = '#delete-model-box';
                $(deleteConfirmModalBox).modal('hide');
                $('.page-loader-wrapper').hide();
            },
            error: function (data) {
                $('#locationManagment').DataTable().ajax.reload(null, false);
                toastr.error(message.unableToDeleteLocation);
                var deleteConfirmModalBox = '#delete-model-box';
                $(deleteConfirmModalBox).modal('hide');
                $('.page-loader-wrapper').hide();
            }
        });
    });

    var tzUrl = url.timezoneUrl;
    var stateUrl = url.stateUrl;

    //to keep county and timezone field enabled when editing locations.
    setTimeout(function() {
        var country = $('#country').val();
        if (country != '' && country != undefined) {
            //  $('#country').trigger('change');
            $('#timezone').removeAttr('disabled');
        }
    }, 10);

    $('#country').change(function() {
        tzUrl = tzUrl;
        if ($('#country').val() != '' && $('#country').val() != null) {
            if ($(this).attr("id") == 'country' && $(this).attr('target-data') == 'timezone') {
                var select = $(this).attr("id");
                var value = $(this).val();
                var tzDependent = $(this).attr('target-data');
                var _token = $('input[name="_token"]').val();

                url = tzUrl.replace(':id', value);

                $.ajax({
                    url: url,
                    method: 'get',
                    data: {
                        _token: _token
                    },
                    success: function(result) {
                        $('#' + tzDependent).empty().attr('disabled', false);
                        $('#' + tzDependent).val('').trigger('change').append('<option value="">Select</option>');
                        $('#' + tzDependent).removeClass('is-valid');
                        $.each(result.result, function(key, value) {
                            $('#' + tzDependent).append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                        if (Object.keys(result.result).length == 1) {
                            $.each(result.result, function(key, value) {
                                $('#' + tzDependent).select2('val', value.id);
                            });
                        }
                        // to populate county and timezone field after redirecting with error.
                        var timezone = condition.timezone;
                        if (timezone != '' && timezone != undefined) {

                            $('#timezone').select2('val', timezone);
                        }
                    }
                })
            }
        } else {
            $('#timezone').html('').select2('destroy').select2();
        }
    });

    $('#countrySearch').change(function() {
        tzUrl = tzUrl;
        if ($('#countrySearch').val() != '' && $('#countrySearch').val() != null) {
            if ($(this).attr("id") == 'countrySearch') {
                var select = $(this).attr("id");
                var value = $(this).val();
                var tzDependent = $(this).attr('target-data');
                var _token = $('input[name="_token"]').val();

                url = tzUrl.replace(':id', value);

                $.ajax({
                    url: url,
                    method: 'get',
                    data: {
                        _token: _token
                    },
                    success: function(result) {
                        $('#timezoneSearch').empty().attr('disabled', false);
                        $('#timezoneSearch').val('').trigger('change').append('<option value="">Select</option>');
                        $('#timezoneSearch').removeClass('is-valid');
                        $.each(result.result, function(key, value) {
                            $('#timezoneSearch').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                        if (Object.keys(result.result).length == 1) {
                            $.each(result.result, function(key, value) {
                                $('#timezoneSearch').select2('val', value.id);
                            });
                        }
                        // to populate county and timezone field after redirecting with error.
                        var timezone = condition.timezone;
                        if (timezone != '' && timezone != undefined) {

                            $('#timezoneSearch').select2('val', timezone);
                        }
                    }
                });


                $.ajax({
                    url: stateUrl.replace(':id', value),
                    method: 'get',
                    data: {
                        _token: _token
                    },
                    success: function(result) {
                        $('#stateSearch').empty();
                        $('#stateSearch').attr('disabled', false);
                        $('#stateSearch').val('').trigger('change').append('<option value="">Select</option>');
                        $('#stateSearch').removeClass('is-valid');
                        $.each(result.result, function(key, value) {
                            $('#stateSearch').append('<option value="' + value.id + '">' + value.name + '</option>');
                        });
                        if (Object.keys(result.result).length == 1) {
                            $.each(result.result, function(key, value) {
                                $('#stateSearch').select2('val', value.id);
                            });
                        }

                        // var county = condition.county;
                        // console.log(county);
                        // if (county != '' && county != undefined) {
                        //     $('#stateSearch').select2('val', county);
                        // }
                    }
                })
            }
        } else {
            $('#timezoneSearch').html('').select2('destroy').select2();
            $('#stateSearch').html('').select2('destroy').select2();

        }
    });

    $('.daterangesFromExportModel').datepicker({
        format: "yyyy-mm-dd",
        todayHighlight: false,
        autoclose: true,
    });

    $(document).on('click', '#locationExport', function(t) {
        var exportConfirmModalBox = '#export-model-box';
        var __startDate = $(this).attr('data-start');
        var __endDate = $(this).attr('data-end');
        $('#email').val(loginemail).removeClass('error');
        $('#queryString').val(JSON.stringify(get_query()));
        $('.error').remove();
        $("#model-title").html($(this).data('title'));
        $("#exportLocationReport").attr('action', url.locationExportUrl);
        $('.loadingMsg').remove();
        $('#export-model-box-confirm').prop('disabled', false);
        $('#exportReportMsg').hide();
        $('#exportLocations').show();
        $(exportConfirmModalBox).attr("data-id", $(this).data('id'));
        $(exportConfirmModalBox).modal('show');
    });

    $('#exportLocationReport').validate({
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
    $('#exportLocationReport').ajaxForm({
        beforeSend: function() {
            $(".page-loader-wrapper").fadeIn();
            $('#exportLocationReport .card-footer button, #exportIntercompanychallenge .card-footer a').attr('disabled', 'disabled');
        },
        success: function(data) {
            $('#exportLocationReport .card-footer button, #exportIntercompanychallenge .card-footer a').removeAttr('disabled');
            $('#export-model-box').modal('hide');
            if (data.status == 1) {
                toastr.success(data.data);
            } else {
                toastr.error(data.data);
            }
        },
        error: function(data) {
            $('#exportLocationReport .card-footer button, #exportIntercompanychallenge .card-footer a').removeAttr('disabled');
            if (data.responseJSON && data.responseJSON.message && data.responseJSON.message != '') {
                toastr.error(data.responseJSON.message);
            } else {
                toastr.error(message.somethingWentWrong);
            }
        },
        complete: function(xhr) {
            $(".page-loader-wrapper").fadeOut();
            $('#exportLocationReport .card-footer button, #exportIntercompanychallenge .card-footer a').removeAttr('disabled');
        }
    });
});

function get_query(){
    var url = document.location.href;
    var qs = url.substring(url.indexOf('?') + 1).split('&');
    for(var i = 0, result = {}; i < qs.length; i++){
        qs[i] = qs[i].split('=');
        result[qs[i][0]] = decodeURIComponent(qs[i][1]);
    }
    return result;
}