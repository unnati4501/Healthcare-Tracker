var bar = $('#mainProgrssbar'),
    percent = $('#mainProgrssbar .progpercent');
$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    //Validate the notes 
    $(document).on('click', '#update_notes_btn', function(event) {
        var domEditableElement = document.querySelector( '.ck-editor__editable' );
            editorInstance = domEditableElement.ckeditorInstance;
            notes = editorInstance.getData();
            notes = $(notes).text().trim();

        if(notes.length > 2500) {
            event.preventDefault();
            $('#notes-error').html(messages.notes_length).addClass('is-invalid').show();
        } else {
            $('#notes-error').removeClass('is-invalid').hide();
        }
    });


    // show session cancel popup on cancel button click
    $(document).on('click', '#cancelSessionModel', function(e) {
        var bid = $(this).data('id');
        $('#cancelSessionForm').attr('action', url.cancelSession.replace(':bid', bid));
        $('#cancel-session-model-box').data("bid", bid).modal('show');
    });

    // reset cancel session reason from once popup is closed
    $(document).on('hidden.bs.modal', '#cancel-session-model-box', function(e) {
        $('#cancelled_reason').val('').removeClass('is-invalid is-valid');
        $('#cancelled_reason-error').remove();
        $('#cancelSessionForm').attr('action', '');
    });

    // cancel session on yes button of cancel modal
    $(document).on('click', '#session-cancel-model-box-confirm', function(e) {
        $('#cancelled_reason-error').remove();
        var res = [];
        var cancelledReason = $('#cancelled_reason').val();
        if(cancelledReason == "") {
            $('textarea#cancelled_reason').after('<div id="cancelled_reason-error" class="error text-danger">'+messages.cancel_reason_required+'</div>');
            res.push("name");
        }else{
            removeFrmArr(res, 'name');
        }
        if(res.length <= 0){
            toastr.clear();
            var bid = $('#cancel-session-model-box').data("bid");
            $('.page-loader-wrapper').show();
            $.ajax({
                type: 'POST',
                url: url.cancelSession.replace(":bid", bid),
                data: {
                    cancelled_reason: $('#cancelled_reason').val()
                },
                success: function (data) {
                    var sessionCancelModalBox = '#cancel-session-model-box';
                    $(sessionCancelModalBox).modal('hide');
                    $('.page-loader-wrapper').hide();
                    
                    if(data.status == 1){
                        toastr.success(messages.cancelled_success);
                    }else{
                        toastr.error(messages.cancelled_error);
                    }
                    
                    setTimeout(function () {
                        window.location.reload()
                        }, 1000);
                },
            })
        }
    });
    $('#sessionAttachments').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            type: 'GET',
            url: url.getAttachmentsDatatableUrl,
            data: {
                getQueryString: window.location.search,
                from : window.location.pathname
            },
        },
        columns: [{
            data: 'file_name',
            name: 'file_name',
            sortable: false,
            className: 'no-sort',
        }, {
            data: 'created_at',
            name: 'created_at',
        }
        , {
            data: 'actions',
            name: 'actions',
            className: 'no-sort',
            searchable: false,
            sortable: false,
        }
        ],
        "drawCallback": function( settings ) {
            var api = this.api();
            if(api.rows().count() >= 3){
                $(".upload-attachments").hide();
            }else {
                $(".upload-attachments").show();
            }
        },
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        paging: false,
        lengthChange: false,
        searching: false,
        ordering: true,
        order: [],
        info: false,
        autoWidth: false,
        columnDefs: [{
            targets: 'no-sort',
            orderable: false,
        }],
    });

    $(document).on('change', '#attachments', function(e) {
        var canProceed = true;
        if (e.target.files.length > 0) {
            var fileName = e.target.files[0].name,
                allowedMimeTypes = ['image/png', 'image/jpeg', 'image/jpg', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];
            if (!allowedMimeTypes.includes(e.target.files[0].type)) {
                toastr.error(messages.image_valid_error);
                $(e.currentTarget).empty().val('');
                $('#bulk-upload-confirm').attr('disabled', true);
                canProceed = false;
            } else if (e.target.files[0].size > 5242880) {
                toastr.error(messages.image_size_5M_error);
                $('#bulk-upload-confirm').attr('disabled', true);
                $(e.currentTarget).empty().val('');
                canProceed = false;
                $(this).parent('div').find('.custom-file-label').val('');
            } else {
                $('#bulk-upload-confirm').attr('disabled', false);
                $(this).parent('div').find('.custom-file-label').html(fileName);
            }
        }
    });

    $('#bulkUploadAttachmentFrm').ajaxForm({
        beforeSend: function() {
            $('.progress-loader-wrapper .status-text').html('Uploading images....');
            $('.progress-loader-wrapper').show();
            var percentVal = '0%';
            bar.width(percentVal)
            percent.html(percentVal);
        },
        uploadProgress: function(event, position, total, percentComplete) {
            var percentVal = percentComplete + '%';
            bar.width(percentVal)
            percent.html(percentVal);
            if (percentComplete == 100) {
                $('.progress-loader-wrapper .status-text').html('Processing on images...');
            }
        },
        success: function(data) {
            $('.progress-loader-wrapper').hide();
            var percentVal = '100%';
            bar.width(percentVal)
            percent.html(percentVal);
            if (data.status && data.status == 1) {
                window.location.reload();
            } else {
                if (data.message && data.message != '') {
                    toastr.error(data.message);
                }
            }
        },
        error: function(data) {
            $('.progress-loader-wrapper').hide();
            if (data.responseJSON && data.responseJSON.message && data.responseJSON.message != '') {
                toastr.error(data.responseJSON.message || message.somethingWentWrong);
            } else {
                toastr.error(message.somethingWentWrong);
            }
            var percentVal = '0%';
            bar.width(percentVal)
            percent.html(percentVal);
        },
        complete: function(xhr) {
            $('.progress-loader-wrapper').hide();
        }
    });

    $(document).on('click', '.attachment-delete', function (t) {
        $('#delete-model-box').attr("data-id", $(this).data('id'));
        $('#delete-model-box').modal('show');
    });

    $(document).on('click', '#delete-model-box-confirm', function (e) {
        $('.page-loader-wrapper').show();
        var objectId = $('#delete-model-box').data("id");
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            type: 'DELETE',
            url: url.deleteAttachment + '/' + objectId,
            data: null,
            crossDomain: true,
            cache: false,
            contentType: 'json',
            success: function (data) {
                if (data['deleted'] == 'true') {
                    toastr.success(messages.deleted);
                    $('#sessionAttachments').DataTable().ajax.reload(null, false);
                } else {
                    toastr.error(messages.somethingWentWrong);
                }
                $('#delete-model-box').modal('hide');
                $('.page-loader-wrapper').hide();
            },
            error: function (data) {
                if (data == 'Forbidden') {
                    toastr.error(messages.somethingWentWrong);
                }
                $('#delete-model-box').modal('hide');
                $('.page-loader-wrapper').hide();
            }
        });
    });
});

function removeFrmArr(array, element) {
    return array.filter(e => e !== element);
}