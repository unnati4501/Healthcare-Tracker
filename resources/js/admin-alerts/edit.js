$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        timeout: (60000 * 20)
    });
    var subCategories = null;
    $('#usersTbl').DataTable({
        order: [],
        paging: false,
        pageLength: pagination.value,
        lengthChange: false,
        searching: false,
        ordering: true,
        info: false,
        autoWidth: false,
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        columnDefs: [{
            targets: 'no-sort',
            orderable: false,
        }],
        stateSave: false
    });

    $(document).on('click', '#addUser', function() {
        var serviceConfirmModalBox = '#addUser-model-box';
        $('#user_name', '#user_email').val(' ');
        $('#editflag').val(' ');
        $('#user_name-error-cstm, #user_email-error-cstm').html('').hide();
        $(serviceConfirmModalBox).modal('show');
    });
    $(document).on('click', '#editUser', function() {
        var _id = $(this).attr('orderId');
        $('#editflag').val(_id);
        $('#user_name-error-cstm, #user_email-error-cstm').html('').hide();
        var userName = $('#user_name_'+_id).val();
        var userEmail = $('#user_email_'+_id).val();
        $('#user_name').val(userName);
        $('#user_email').val(userEmail);
        var serviceConfirmModalBox = '#addUser-model-box';
        $(serviceConfirmModalBox).modal('show');
    });
    $(document).on('click', '#userSave', function() {
        var lastInsertedId = parseInt($('#last_user_id').val());
        var editFlag = $('#editflag').val();
        var isError = 0;
        var isErrorDes = 0;
        var userName = $('#user_name').val();
        var userEmail = $('#user_email').val();
        $('#user_name-error-cstm, #user_email-error-cstm').html('').hide();

        // Check email exists or not
        var getAllPreviousEmails =  $('.list_user_email').map(function() {
            return this.value;
        }).get();

        // Remove the current element from the array during edit user
        if (editFlag > 0) {
            getAllPreviousEmails.splice( $.inArray($('#user_email_'+editFlag).val(),getAllPreviousEmails) ,1 );
        }
        var arraycontainsEmail = (getAllPreviousEmails.indexOf(userEmail));

        var regExUserName = /^[a-zA-zÀ-ú0-9 |@(),-]+$/i;
        var regexEmail    = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;

        if (userName.length == 0) {
            $('#user_name-error-cstm').html(message.user_name_required).addClass('is-invalid').show();
            isError = 0;
        } else if((regExUserName.test(userName)) == false){
            $('#user_name-error-cstm').html(message.user_name_valid).addClass('is-invalid').show();
            isError = 0;
        } else {
            isError = 1;
            $('#user_name-error-cstm').removeClass('is-invalid').hide();
        }

        if (userEmail.length == 0) {
            $('#user_email-error-cstm').html(message.email_required).addClass('is-invalid').show();
            isErrorDes = 0;
        } else if((regexEmail.test(userEmail)) == false){
            $('#user_email-error-cstm').html(message.email_valid).addClass('is-invalid').show();
            isErrorDes = 0;
        } else if ( arraycontainsEmail != -1) {
            $('#user_email-error-cstm').html(message.email_exists).addClass('is-invalid').show();
            isErrorDes = 0;
        } else {
            isErrorDes = 1;
            $('#user_email-error-cstm').removeClass('is-invalid').hide();
        }
        
        if (isError == 1 && isErrorDes == 1) {
            if (editFlag > 0) {
                $('#user_name_span_'+editFlag).text(userName);
                $('#user_email_span_'+editFlag).text(userEmail);
                $('#user_name_'+editFlag).val(userName);
                $('#user_email_'+editFlag).val(userEmail);
            } else {
                var lastInsertedId = $('.list_user_name').length + parseInt(lastInsertedId) + 1;
                var template = $('#admin_alert_user_data_template').text().trim().replace(/:user_name/g, userName).replace(/:user_email/g, userEmail).replace(/:id/g, lastInsertedId).replace('user-remove hide', 'user-remove');
                $("#usersTbl tbody").append(template);
                if($("#usersTbl tbody tr").length > 1){
                    $('.dataTables_empty').parents("tr").remove();
                }
            }
            var serviceConfirmModalBox = '#addUser-model-box';
            $(serviceConfirmModalBox).modal('hide');
        }
    });

    $(document).on('hidden.bs.modal', '#addUser-model-box', function(e) {
        $('#user_email').val('').removeClass('is-invalid is-valid');
        $('#user_name').val('').removeClass('is-invalid is-valid');
    });
    $(document).on('click', '.user-remove', function() {
        var _id = $(this).attr('orderId');
        var deleteConfirmationBox = '#delete-user-model-box';
        $('#delete-user-model-box-confirm').attr('orderId', _id);
        $(deleteConfirmationBox).modal('show');
    });

    $(document).on('click','#delete-user-model-box-confirm', function() {
        var _id = $(this).attr('orderId');
        $('#row_'+_id).remove();
        var deleteConfirmationBox = '#delete-user-model-box';
        $(deleteConfirmationBox).modal('hide');
        toastr.success(message.user_deleted);
        if($("#usersTbl tbody tr").length <= 0){
            $("#usersTbl tbody").append("<tr><td valign='top' colspan='3' class='dataTables_empty'>No data available in table</td></tr>");
        }
    });
    $(document).on('submit', '#updateadminalertform', function() {
        var isError = 1;
        $('#description-error-cstm').removeClass('is-invalid').hide().html('');

        var domEditableElement = document.querySelector( '.ck-editor__editable' );
            editorInstance = domEditableElement.ckeditorInstance;
           description = editorInstance.getData();
           description = $(description).text().trim();
        if (description == '') {
            $('#zevo_submit_btn').removeAttr('disabled');
            event.preventDefault();
            // if ($('#updateadminalertform').length > 0) {
            //     $('#updateadminalertform').valid();
            // } else {
                
            //     $('#updateadminalertform').valid();
            // }
            $('#description-error-cstm').html(message.desc_required).addClass('is-invalid').show();
            isError = 0;
        } else {
            if (description.length > 500) {
                event.preventDefault();
                isError = 0;
                $('#description-error-cstm').html(message.desc_length).addClass('is-invalid').show();
            } else {
                isError = 1;
                $('#description-error-cstm').removeClass('is-invalid').hide();
            }
            $('#zevo_submit_btn').removeAttr('disabled');
        }
        return true;
    });
});