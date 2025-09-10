$(document).ready(function() {
    $('#detailedContentReport').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: url.dataTable,
            data: {
                title: $('#title').val(),
                company: $('#company').val(),
                type: $('#type').val(),
                category: $('#category').val(),
                fromdate: $('#fromdate').val(),
                todate: $('#todate').val()
            },
        },
        columns: [
            {data: 'title', name: 'title'},
            {data: 'name', name: 'name'},
            {data: 'type', name: 'type'},
            {data: 'like_count', name: 'like_count'},
            {data: 'view_count', name: 'view_count'}
        ],
        paging: true,
        pageLength: pagination.value,
        lengthChange: false,
        searching: false,
        ordering: true,
        order: [],
        info: true,
        autoWidth: false,
        stateSave: false,
        language: {
            "paginate": {
                "previous": pagination.previous,
                "next": pagination.next
            },
        },
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        drawCallback: function(settings) {
            var api = this.api();
            if(api.rows().count()==0){
                $("#detailed-tab-result-block #exportcontentReportbtn").hide();
            }else {
                $("#detailed-tab-result-block #exportcontentReportbtn").show();
            }
        }
    });
    var typeValue = $('#type').val();
    if(typeValue != '' && typeValue != 8){
        $('#categorybox').show();
    } else {
        $('#categorybox').hide();
    }
    $(document).on('change','#type', function() {
        var type = $(this).val();
        if(type == 8){
            $('#categorybox').hide();
        } else {
            getSubcategoryValue(type);
        }
    });
    $(document).on('click', '#exportcontentReportbtn', function (t) {
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
            $("#emailError").html(message.email_required);
        }else if(!pattern.test(userEmail)){
            $("#emailError").show();
            $("#emailError").html(message.valid_email);
        }else{
            $("#emailError").hide();
            $("#emailError").html("");
            exportContentReport();
        }
    });
    $('.dateranges').datepicker({
        format: "mm/dd/yyyy",
        todayHighlight: false,
        autoclose: true,
    });

});
function exportContentReport() {
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
            title: $('#title').val(),
            company: $('#company').val(),
            type: $('#type').val(),
            category: $('#category').val(),
            fromdate: $('#fromdate').val(),
            todate: $('#todate').val(),
            email: $('#email').val()
        },
        crossDomain: true,
        cache: false,
        success: function (data) {
            $('#detailedContentReport').DataTable().ajax.reload(null, false);
            if (data['status'] == 1) {
                toastr.success(data['data']);
            } else {
                toastr.success(data['data']);
            }
            $(exportConfirmModalBox).modal('hide');
            $('.page-loader-wrapper').hide();
        },
        error: function (data) {
            $('#detailedContentReport').DataTable().ajax.reload(null, false);
            toastr.error("This action is unauthorized.");
            $(exportConfirmModalBox).modal('hide');
            $('.page-loader-wrapper').hide();
        }
    });
}
function getSubcategoryValue(type){
    var categoryUrl = url.getCategoryList.replace(':category', type);
    $.get(categoryUrl, function (data, textStatus, jqXHR) {
        if(jqXHR.status == 200 && data && data.status == 1) {
            var options = '';
            $.each(data.data, function(index, element) {
                options += `<option value="${index}">${element}</option>`;
            });
            $('#category').html(options).val('').prop('disabled', false);
            $('#category').select2('destroy').select2();

            $('#categorybox').show();
            if ($(".select2").length > 0 && $(".no-default-select2").length == 0) {
                $('.select2').select2({
                    placeholder: "Select",
                    allowClear: true,
                    width: '100%'
                });
            }
        } else {
            $('#categorybox').hide();
            toastr.clear()
            toastr.error(message.failed_to_load)
        }
    });
}