$(document).ready(function() {
    
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $('#cm_notes_filter').datepicker({
        autoclose: true,
        todayHighlight: true,
        format: 'yyyy-mm-dd',
    }).on('changeDate', function(e) {
        var date = moment(e.date).format('YYYY-MM-DD');
        $('.no-cm-notes, .cm-notes-wrap').hide();
        $('.loading-cm-notes').show();
        $.ajax({
            url: url.notes,
            type: 'POST',
            dataType: 'html',
            data: {
                date: date
            },
        }).done(function(data) {
            if (data != '') {
                $('.no-cm-notes').hide();
                $('.cm-notes-wrap').html(data).show();
            } else {
                $('.cm-notes-wrap').html('');
                $('.no-cm-notes').show();
            }
        }).fail(function() {
            $('.no-cm-notes').show();
            toastr.error(messages.failed_cm_notes);
        }).always(function() {
            $('.loading-cm-notes').hide();
        });
    });
    $('#cm_notes_filter').datepicker('update', new Date());
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
        $('#note-error').remove();
    });

    //Validate the notes 
    $(document).on('click', '#add_notes_btn', function(event) {
        var domEditableElement = document.querySelector( '.ck-editor__editable' );
            editorInstance = domEditableElement.ckeditorInstance;
            notes = editorInstance.getData();
            notes = $(notes).text().trim();
            
        if(notes == ''){
            event.preventDefault();
            $('#addNoteForm').valid();
            $('#notes-error').html('The notes field is required.').addClass('is-invalid').show();
        } else {
            if(notes.length > 1000) {
                event.preventDefault();
                $('#notes-error').html('The notes field may not be greater than 1000 characters.').addClass('is-invalid').show();
            } else {
                console.log("Hide");
                $('#notes-error').removeClass('is-invalid').hide();
            }
        }
    });

    //Get the notes in edit mode 
    $(document).on("click", ".open-editNotes-model", function () {
        var editNoteId = $(this).data('id');
        var noteFrom = $(this).data('notefrom');
        $("#commentId").val($(this).data('id'));
        $("#clientId").val($(this).data('clientid'));
        $("#noteFrom").val($(this).data('notefrom'));
        $.ajax({
            type: 'GET',
            url: url.getClientNote,
            data: {
                id : editNoteId,
                noteFrom : noteFrom
            },
            success: function(response){
            }
        });
    });

    //Validate edit notes from
    $(document).on('click', '#edit_notes_btn', function(event) {
        notes = '';
        if(notes == ''){
            event.preventDefault();
            $('#editNoteForm').valid();
            $('#edit-notes-error').html('The notes field is required.').addClass('is-invalid').show();
        } else {
            if(notes.length > 1000) {
                event.preventDefault();
                $('#edit-notes-error').html('The notes field may not be greater than 1000 characters.').addClass('is-invalid').show();
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
        $(deleteConfirmModalBox).modal('show');
    });

    $(document).on('click', '#delete-model-box-confirm', function (e) {
        $('.page-loader-wrapper').show();
        var deleteConfirmModalBox = '#delete-model-box';
        var objectId = $(deleteConfirmModalBox).attr("data-id");
        var noteFrom = $(deleteConfirmModalBox).attr("data-notefrom");
        if(noteFrom == 'ClientNote'){
            var newUrl = url.deleteClientNote.replace(':id', objectId);
        }else{
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
                    setTimeout(function () {
                        window.location.reload()
                      }, 1000)
                } else {
                    toastr.error(messages.unable_to_delete_note);
                }
            },
            error: function (data) {
                toastr.error(message.unable_to_delete_note);
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
    
});