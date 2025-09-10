$(document).ready(function() {
    
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    $('#clientSessionsManagement').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            type: 'POST',
            url: url.datatable,
            data: {
                getQueryString: window.location.search,
                name: $('#session_name').val(),
                status: $('#session_status').val(),
            },
        },
        columns: [{
            data: 'session_name',
            name: 'session_name',
        }, {
            data: 'duration',
            name: 'duration',
        }, {
            data: 'status',
            name: 'status',
        }, {
            data: 'view',
            name: 'view',
            className: 'no-sort',
            searchable: false,
            sortable: false,
        }],
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
    $(document).on('click', '.view-cancel-details', function(e) {
        var data = $(this).data('record'),
            date = ((data.cancelled_at) ? moment.utc(data.cancelled_at).tz(timezone).format(date_format) : ''),
            reason = (data.cancelled_reason || "");
        $('#cancelled_by').html((data.cancelled_by || ""));
        $('#cancelled_at').html(date);
        $('#cancelation_reason').html((reason ? reason.replace(/\\|\//g, '') : ''));
        $('#reason-model-box').modal('show')
    });
    $(document).on('hidden.bs.modal', '#add-note-model', function(e) {
        $('#note').val('').removeClass('is-invalid is-valid');
        $('#notes-error').hide();
    });
    var notesCkeditorAdd;
        CKEDITOR.ClassicEditor.create(document.querySelector( '.notes-add-ckeditor' ), {
            toolbar: {
                items: [
                    'undo', 'redo',
                    'bold', 'italic', 'strikethrough', 'underline', '|',
                    'heading', '|',
                    '-',
                    'fontColor', 'fontBackgroundColor',
                    'alignment', '|',
                    'link', 'codeBlock', '|',
                    'sourceEditing'
                ],
                shouldNotGroupWhenFull: true
            },
            list: {
                properties: {
                    styles: true,
                    startIndex: true,
                    reversed: true
                }
            },
            heading: {
                options: [
                    { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                    { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                    { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                    { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' },
                    { model: 'heading4', view: 'h4', title: 'Heading 4', class: 'ck-heading_heading4' },
                    { model: 'heading5', view: 'h5', title: 'Heading 5', class: 'ck-heading_heading5' },
                    { model: 'heading6', view: 'h6', title: 'Heading 6', class: 'ck-heading_heading6' }
                ]
            },
            link: {
                decorators: {
                    addTargetToExternalLinks: true,
                    defaultProtocol: 'https://',
                    toggleDownloadable: {
                        mode: 'manual',
                        label: 'Downloadable',
                        attributes: {
                            download: 'file'
                        }
                    }
                }
            },
            mention: {
                feeds: [
                    {
                        marker: '@',
                        feed: [
                            '@apple', '@bears', '@brownie', '@cake', '@cake', '@candy', '@canes', '@chocolate', '@cookie', '@cotton', '@cream',
                            '@cupcake', '@danish', '@donut', '@dragée', '@fruitcake', '@gingerbread', '@gummi', '@ice', '@jelly-o',
                            '@liquorice', '@macaroon', '@marzipan', '@oat', '@pie', '@plum', '@pudding', '@sesame', '@snaps', '@soufflé',
                            '@sugar', '@sweet', '@topping', '@wafer'
                        ],
                        minimumCharacters: 1
                    }
                ]
            },
            removePlugins: [
                'CKBox',
                'CKFinder',
                'EasyImage',
                'RealTimeCollaborativeComments',
                'RealTimeCollaborativeTrackChanges',
                'RealTimeCollaborativeRevisionHistory',
                'PresenceList',
                'Comments',
                'TrackChanges',
                'TrackChangesData',
                'RevisionHistory',
                'Pagination',
                'WProofreader',
                'MathType'
            ]
        }).then( editor => {
            notesCkeditorAdd = editor;
            editor.model.document.on( 'change:data', () => {
                 if($('#notes-error').length){
                    //From create group session
                    var notes = editor.getData();
                    notes = $(notes).text().trim();
                    if (notes == '') {
                        $('#notes-error').html('The notes field is required.').addClass('is-invalid').show();
                    } else {
                        if(notes.length > 2500) {
                            $('#notes-error').html(notes_length).addClass('is-invalid').show();
                        } else {
                            $('#notes-error').removeClass('is-invalid').hide();
                        }
                    }
                }
                // if($('#edit-notes-error').length){
                //     //From create group session
                //     var notes = editor.getData();
                //     notes = $(notes).text().trim();
                //     if (notes == '') {
                //         $('#edit-notes-error').html('The notes field is required.').addClass('is-invalid').show();
                //     } else {
                //         if(notes.length > 2500) {
                //             $('#edit-notes-error').html(notes_length).addClass('is-invalid').show();
                //         } else {
                //             $('#edit-notes-error').removeClass('is-invalid').hide();
                //         }
                //     }
                // }
            } );
        } )
        .catch( err => {
            console.error( err.stack );
        } );

    //Validate the notes 
    $(document).on('click', '#add_notes_btn', function(event) {
        notes = notesCkeditorAdd.getData();
        notes = $(notes).text().trim();
            
        if (notes == '') {
            event.preventDefault();
            $('#addNoteForm').valid();
            $('#notes-error').html('The notes field is required.').addClass('is-invalid').show();
        } else {
            if(notes.length > 2500) {
                event.preventDefault();
                $('#notes-error').html(notes_length).addClass('is-invalid').show();
            } else {
                $('#notes-error').removeClass('is-invalid').hide();
            }
        }
    });
    var notesCkeditorEdit;
        CKEDITOR.ClassicEditor.create(document.querySelector( '.notes-ckeditor' ), {
            toolbar: {
                items: [
                    'undo', 'redo',
                    'bold', 'italic', 'strikethrough', 'underline', '|',
                    'heading', '|',
                    '-',
                    'fontColor', 'fontBackgroundColor',
                    'alignment', '|',
                    'link', 'codeBlock', '|',
                    'sourceEditing'
                ],
                shouldNotGroupWhenFull: true
            },
            list: {
                properties: {
                    styles: true,
                    startIndex: true,
                    reversed: true
                }
            },
            heading: {
                options: [
                    { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                    { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                    { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                    { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' },
                    { model: 'heading4', view: 'h4', title: 'Heading 4', class: 'ck-heading_heading4' },
                    { model: 'heading5', view: 'h5', title: 'Heading 5', class: 'ck-heading_heading5' },
                    { model: 'heading6', view: 'h6', title: 'Heading 6', class: 'ck-heading_heading6' }
                ]
            },
            link: {
                decorators: {
                    addTargetToExternalLinks: true,
                    defaultProtocol: 'https://',
                    toggleDownloadable: {
                        mode: 'manual',
                        label: 'Downloadable',
                        attributes: {
                            download: 'file'
                        }
                    }
                }
            },
            mention: {
                feeds: [
                    {
                        marker: '@',
                        feed: [
                            '@apple', '@bears', '@brownie', '@cake', '@cake', '@candy', '@canes', '@chocolate', '@cookie', '@cotton', '@cream',
                            '@cupcake', '@danish', '@donut', '@dragée', '@fruitcake', '@gingerbread', '@gummi', '@ice', '@jelly-o',
                            '@liquorice', '@macaroon', '@marzipan', '@oat', '@pie', '@plum', '@pudding', '@sesame', '@snaps', '@soufflé',
                            '@sugar', '@sweet', '@topping', '@wafer'
                        ],
                        minimumCharacters: 1
                    }
                ]
            },
            removePlugins: [
                'CKBox',
                'CKFinder',
                'EasyImage',
                'RealTimeCollaborativeComments',
                'RealTimeCollaborativeTrackChanges',
                'RealTimeCollaborativeRevisionHistory',
                'PresenceList',
                'Comments',
                'TrackChanges',
                'TrackChangesData',
                'RevisionHistory',
                'Pagination',
                'WProofreader',
                'MathType'
            ]
        }).then( editor => {
            notesCkeditorEdit = editor;
        } )
        .catch( err => {
            console.error( err.stack );
        } );
    //Get the notes in edit mode 
    $(document).on("click", ".open-editNotes-model", function () {
        var editNoteId = $(this).data('id');
        var noteFrom = $(this).data('notefrom');
        $("#commentId").val($(this).data('id'));
        $("#clientId").val($(this).data('clientid'));
        $("#noteFrom").val($(this).data('notefrom'));
        $("#noteFromTable").val($(this).data('notefromtable'));
    
        $.ajax({
            type: 'GET',
            url: url.getClientNote,
            data: {
                id : editNoteId,
                noteFrom : noteFrom,
                noteFromTable : $(this).data('notefromtable')
            },
            success: function(response){
                notesCkeditorEdit.setData(response.note);

            }
        });
    });

    //Validate edit notes from
    $(document).on('click', '#edit_notes_btn', function(event) {
            var notes = notesCkeditorEdit.getData();
            notes = $(notes).text().trim();
        if(notes == ''){
            event.preventDefault();
            $('#editNoteForm').valid();
            $('#edit-notes-error').html('The notes field is required.').addClass('is-invalid').show();
        } else {
            if(notes.length > 2500) {
                event.preventDefault();
                $('#edit-notes-error').html(notes_length).addClass('is-invalid').show();
            } else {
                $('#edit-notes-error').removeClass('is-invalid').hide();
            }
        }
    });

    //Delete notes
    $(document).on('click', '#clientNoteDelete', function (t) {
        var deleteConfirmModalBox = '#delete-model-box';
        $(deleteConfirmModalBox).attr("data-id", $(this).data('id'));
        $(deleteConfirmModalBox).attr("data-notefrom", $(this).data('notefrom'));
        $(deleteConfirmModalBox).attr("data-notefromtable", $(this).data('notefromtable'));
        $(deleteConfirmModalBox).modal('show');
    });

    $(document).on('click', '#delete-model-box-confirm', function (e) {
        $('.page-loader-wrapper').show();
        var deleteConfirmModalBox = '#delete-model-box';
        var objectId = $(deleteConfirmModalBox).attr("data-id");
        var noteFrom = $(deleteConfirmModalBox).attr("data-notefrom");
        var noteFromTable = $(deleteConfirmModalBox).attr("data-notefromtable");
        if (noteFrom == 'ClientNote' && noteFromTable != 'scheduleTable') {
            var newUrl = url.deleteClientNote.replace(':id', objectId);
        } else {
            var newUrl = url.deleteSessionNote.replace(':id', objectId);
        }
        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });

        $.ajax({
            type: 'DELETE',
            url: newUrl,
            data: null,
            crossDomain: true,
            cache: false,
            contentType: 'json',
            success: function (data) {
                var deleteConfirmModalBox = '#delete-model-box';
                $(deleteConfirmModalBox).modal('hide');
                $('.page-loader-wrapper').hide();
                if (data['deleted'] == 'true') {
                    toastr.success(messages.note_deleted);
                    window.location = url.clientDetais;
                } else {
                    toastr.error(messages.unable_to_delete_note);
                }
            },
            error: function (data) {
                toastr.error(messages.unable_to_delete_note);
                var deleteConfirmModalBox = '#delete-model-box';
                $(deleteConfirmModalBox).modal('hide');
                $('.page-loader-wrapper').hide();
            }
        });
    });

    //Display the model popup for view client notes
    $(document).on('click', '.diplay-note-model', function(event) {
        var id = $(this).attr('id');
        var content = $(this).attr('data-content');
        $('#diplay-note-model').attr("data-id", id);
        $('#modal_body').html(content);
        $('#diplay-note-model').show();
    });

    //Display the model popup for view session notes
    $(document).on('click', '.diplay-session-note-model', function(event) {
        var id = $(this).attr('id');
        var content = $(this).attr('data-content');
        $('#diplay-session-note-model').attr("data-id", id);
        $('#modal_session_body').html(content);
        $('#diplay-session-note-model').show();
    });

    //Export the WS and user notes
    $(document).on('click', '.export-notes', function(t) {
        var id = $(this).attr('id');
        var type = $(this).data('type');
        var exportConfirmModalBox = '#export-model-box';
        $('#email').val(loginemail).removeClass('error');
        $('.error').remove();
        $("#model-title").html($(this).data('title'));
        $("#type").val(type);
        $("#exportNotes").attr('action', url.notesExportUrl);
        $('.loadingMsg').remove();
        $('#export-model-box-confirm').prop('disabled', false);
        $('#exportMsg').hide();
        $('#exportNotes').show();
        $(exportConfirmModalBox).attr("data-id", $(this).data('id'));
        $(exportConfirmModalBox).modal('show');
    });

    $('#exportNotes').validate({
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

    $('#exportNotes').ajaxForm({
        beforeSend: function() {
            $(".page-loader-wrapper").fadeIn();
            $('#exportNotes .card-footer button').attr('disabled', 'disabled');
        },
        success: function(data) {
            $('#exportNotes .card-footer button').removeAttr('disabled');
            $('#export-model-box').modal('hide');
            if (data.status == 1) {
                toastr.success(data.data);
            } else {
                toastr.error(data.data);
            }
        },
        error: function(data) {
            $('#exportNotes .card-footer button').removeAttr('disabled');
            if (data.responseJSON && data.responseJSON.message && data.responseJSON.message != '') {
                toastr.error('success'); //data.responseJSON.message
            } else {
                toastr.error('wrong'); //message.somethingWentWrong
            }
        },
        complete: function(xhr) {
            $(".page-loader-wrapper").fadeOut();
            $('#exportNotes .card-footer button').removeAttr('disabled');
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
        paging: true,
        pageLength: pagination.attachmentesPerPage,
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

    $(document).on('click', '#send_kin_access_email', function (t) {
        $("#kin-info-details-modal").modal('show');
        $("#access-kin-info-modal").modal('hide');
    });

    $(document).on("click", "#send_kin_access_email", function () {
        var user_id = $("#user_id").val();
        var ws_id   = $("#wellbeing_specialist_id").val();
        $.ajax({
            type: 'POST',
            url: url.sendEmailForAccessKinInfo,
            data: {
                user_id : $("#user_id").val(),
                ws_id : $("#wellbeing_specialist_id").val()
            },
            success: function(response){
                console.log("success");
            }
        });
    });

    $(document).on('click', '.next-kin-info-btn', function (t) {
        var user_id = $("#user_id").val();
        var ws_id   = $("#wellbeing_specialist_id").val();
        $.ajax({
            type: 'POST',
            url: url.sendEmailForAccessKinInfo,
            data: {
                user_id : $("#user_id").val(),
                ws_id   : $("#wellbeing_specialist_id").val(),
                type    : 'alreadyAccessedKinInfo'
            },
            success: function(response){
                if(response){
                    $("#kin-info-details-modal").modal('show');
                    $("#access-kin-info-modal").modal('hide');
                } else {
                    $("#access-kin-info-modal").modal('show');
                }

            }
        });
    });
    
});
