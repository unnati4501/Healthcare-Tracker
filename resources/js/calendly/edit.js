$(document).ready(function() {
    //Load ckeditor
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
            
        if(notes == ''){
            event.preventDefault();
            $('#sessionEdit').valid();
            $('#notes-error').html('The notes field is required.').addClass('is-invalid').show();
        } else {
            if(notes.length > 1000) {
                event.preventDefault();
                $('#notes-error').html('The notes field may not be greater than 1000 characters.').addClass('is-invalid').show();
            } else {
                $('#notes-error').removeClass('is-invalid').hide();
            }
        }
    });
});