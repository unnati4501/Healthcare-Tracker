$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    $('#creditHistory').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            type: 'POST',
            url: url.datatable,
            data: {
                getQueryString: window.location.search,
                company : $("#company_id").val()
            },
        },
        columns: [{
            data: 'export_date',
            name: 'export_date',
        }, {
            data: 'type',
            name: 'type',
        }, {
            data: 'credits',
            name: 'credits',
        }, {
            data: 'user_name',
            name: 'user_name',
        },
        {
            data: 'available_credits',
            name: 'available_credits',
        },
        {
            data: 'notes',
            name: 'notes',
        }],
        "drawCallback": function( settings ) {
            var api = this.api();
            if(api.rows().count()==0){
                $("#exportCreditHistory").hide();
            }else {
                $("#exportCreditHistory").show();
            }
        },
        paging: true,
        pageLength: pagination.value,
        lengthChange: false,
        searching: false,
        ordering: true,
        order: [],
        info: true,
        autoWidth: false,
        columnDefs: [{
            targets: 'no-sort',
            orderable: false,
        }],
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        language: {
            paginate: {
                previous: pagination.previous,
                next: pagination.next,
            }
        }
    });

    $(document).on('click', '#zevo_submit_btn', function (t) {
        var type                    = $("#update_type").val();
        var credits                 = $("#credits").val();
        var updatedAvailableCredits = 0;
        var totalvailableCredits    = $("#available_credits").val();
        var companyName             = $("#company_name").val();
        var messageType             = "";
        if (type == 'Add') {
            messageType = 'Adding';
            prop        = 'to';
            updatedAvailableCredits = parseInt(totalvailableCredits) + parseInt(credits);
        } else {
            messageType = 'Removing';
            prop        = 'from';
            updatedAvailableCredits = parseInt(totalvailableCredits) - parseInt(credits);
            if(parseInt(credits) > parseInt(totalvailableCredits)){
                toastr.error(messages.creditCountError);
                return false;
            }
        }
        if (credits == 1) {
            creditCountProp = "credit";
        } else {
            creditCountProp = "credits";
        }
        
        if ($("#storeCredits").valid() == true) {
            var message = messageType + " "+ credits +" "+creditCountProp+" "+prop+" the "+companyName+" Company. The updated credits will be "+updatedAvailableCredits+".";
            $("#modal-title").html(type+" Credits");
            $("#modal-message").html(message);
            $('#add-remove-credit-model-box').modal('show');
        }
    });

    $(document).on('click', '#add-remove-credits-confirm', function (t) {
        $('.page-loader-wrapper').show();
        $("#storeCredits").submit();
    });

    $(document).on('click', '#exportCreditHistory', function (t) {
        $("#emailError").hide();
        $("#emailError").html("");
        $('#email').val(loginemail);
        var exportConfirmModalBox = '#export-model-box';
        $(exportConfirmModalBox).attr("data-id", $(this).data('id'));
        $(exportConfirmModalBox).modal('show');
    });

    $(document).on('click', '#export-model-box-confirm', function (e) {
        var userEmail = $('#email').val();
        var pattern = /^\b[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b$/i
        
        if(userEmail.trim() == ''){
            $("#emailError").show();
            $("#emailError").html(messages.emailRequired);
        }else if(!pattern.test(userEmail)){
            $("#emailError").show();
            $("#emailError").html(messages.validEmail);
        }else{
            $("#emailError").hide();
            $("#emailError").html("");
            exportCreditHistory();
        }
    });
 });


function isNumberKey(evt, element) {
    var charCode = (evt.which) ? evt.which : event.keyCode
    if (charCode > 31 && (charCode < 48 || charCode > 57) && !(charCode == 46 || charCode == 8))
    return false;
    else {
    var len = $(element).val().length;
    var index = $(element).val().indexOf('.');
    if (index > 0 && charCode == 46) {
        return false;
    }
    if (index > 0) {
        var CharAfterdot = (len + 1) - index;
        if (CharAfterdot > 3) {
        return false;
        }
    }

    }
    return true;
}

function exportCreditHistory() {
    $('.page-loader-wrapper').show();
    var exportConfirmModalBox = '#export-model-box';

    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });

    $.ajax({
        type: 'POST',
        url: url.exportReport,
        data: {
            company: $('#company_id').val(),
            email: $('#email').val()
        },
        crossDomain: true,
        cache: false,
        success: function (data) {
            $('#creditHistory').DataTable().ajax.reload(null, false);
            if (data['status'] == 1) {
                toastr.success(data['data']);
            } else {
                toastr.success(data['data']);
            }
            $(exportConfirmModalBox).modal('hide');
            $('.page-loader-wrapper').hide();
        },
        error: function (data) {
            $('#creditHistory').DataTable().ajax.reload(null, false);
            toastr.error("This action is unauthorized.");
            $(exportConfirmModalBox).modal('hide');
            $('.page-loader-wrapper').hide();
        }
    });
}