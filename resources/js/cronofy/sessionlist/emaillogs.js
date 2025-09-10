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
            email_message = editorInstance.getData();
            email_message = $(email_message).text().trim();

        if(email_message == ''){
            event.preventDefault();
            $('#sessionEmailEdit').valid();
            $('#email_message-error').html(messages.email_body_required).addClass('is-invalid').show();
        } else {
            if(email_message.length > 5000) {
                event.preventDefault();
                $('#email_message-error').html(messages.email_body_lengh).addClass('is-invalid').show();
            } else {
                $('#email_message-error').removeClass('is-invalid').hide();
            }
        }
    });

    $('#sessionEmailLogs').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: url.datatable,
            data: {
                getQueryString: window.location.search,
            },
        },
        columns: [{
            data: 'id',
            className: 'text-center',
            sortable: false,
            render: function (data, type, row, meta) {
                return meta.row + meta.settings._iDisplayStart + 1;
            }
        }, {
            data: 'created_at',
            name: 'created_at',
            className: 'text-center'
        }, {
            data: 'reason',
            name: 'reason',
            className: 'text-center',
            sortable: false
        }],
        order: [],
        paging: false,
        lengthChange: false,
        searching: false,
        ordering: true,
        info: true,
        autoWidth: false,
        columnDefs: [{
            targets: 'no-sort',
            orderable: false,
        }],
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        language: {
        },
        stateSave: false
    });
});