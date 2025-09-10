$(document).ready(function() {
    //ck-editor__editable
    if ($('.basic-format-ckeditor').length > 0) {
        CKEDITOR.ClassicEditor.create(document.querySelector( '.basic-format-ckeditor' ), {
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
            // The "super-build" contains more premium features that require additional configuration, disable them below.
            // Do not turn them on unless you read the documentation and know how to configure them and setup the editor.
            removePlugins: [
                // These two are commercial, but you can try them out without registering to a trial.
                'CKBox',
                //'CKFinder',
                //'EasyImage',
                // 'Base64UploadAdapter',
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
            window.editor = editor;
            editor.model.document.on( 'change:data', () => {
                if($('#notes-error').length){
                    var notes = editor.getData();
                    notes = $(notes).text().trim();
                    if(notes.length > 2500) {
                        $('#notes-error').html(messages.notes_length).addClass('is-invalid').show();
                    } else {
                        $('#notes-error').removeClass('is-invalid').hide();
                    }
                } else if($('#description-error').length && ($("#eventAdd").length || $("#eventEdit").length)){
                    //Event add / Edit
                    var description = editor.getData();
                    description = $(description).text().trim();
                    if(description == ''){
                        $('#description-error').html('The event description field is required.').addClass('is-invalid').show();
                    } else if(description.length > 2500) {
                        $('#description-error').html('The description field may not be greater than 2500 characters.').addClass('is-invalid').show();
                    } else {
                        $('#description-error').removeClass('is-invalid').hide();
                    }
                } else if($('#description-error').length && ($("#EAPAdd").length || $("#EAPEdit").length)){
                    //Support Add/Edit
                    var description = editor.getData();
                    description = $(description).text().trim();
                    if(description == ''){
                        $('#description-error').html('The description field is required.').addClass('is-invalid').show();
                    } else if(description.length > 750) {
                        $('#description-error').html('The description field may not be greater than 750 characters.').addClass('is-invalid').show();
                    } else {
                        $('#description-error').removeClass('is-invalid').hide();
                    }
                } else if($('#email_message-error').length && $("#sessionEmailEdit").length){
                    //Support Add/Edit
                    var email_message = editor.getData();
                    email_message = $(email_message).text().trim();
                    if(email_message == ''){
                            $('#email_message-error').html(messages.email_body_required).addClass('is-invalid').show();
                    } else {
                        if(email_message.length > 5000) {
                            $('#email_message-error').html('The email body field may not be greater than 5000 characters.').addClass('is-invalid').show();
                        } else {
                            $('#email_message-error').removeClass('is-invalid').hide();
                        }
                    }
                }
            } );
        } )
        .catch( err => {
            console.error( err.stack );
        } );
    }
    
    if ($('.article-ckeditor').length > 0) {
        var imageUrl = document.querySelector('.article-ckeditor').getAttribute('data-upload-path');
        CKEDITOR.ClassicEditor.create(document.querySelector( '.article-ckeditor' ), {
            ckfinder: {
                uploadUrl: imageUrl,
            },
            toolbar: {
                items: [
                    'heading', '|',
                    'bold', 'italic', 'strikethrough', 'underline', 'code', 'subscript', 'superscript', 'removeFormat', '|',
                    'bulletedList', 'numberedList', 'todoList', '|',
                    'outdent', 'indent', '|',
                    'undo', 'redo',
                    '-',
                    'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', '|',
                    'alignment', '|',
                    'link', 'insertImage', 'blockQuote', 'mediaEmbed', '|',
                    'specialCharacters', '|',
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
            fontFamily: {
                options: [
                    'default',
                    'Arial, Helvetica, sans-serif',
                    'Courier New, Courier, monospace',
                    'Georgia, serif',
                    'Lucida Sans Unicode, Lucida Grande, sans-serif',
                    'Tahoma, Geneva, sans-serif',
                    'Times New Roman, Times, serif',
                    'Trebuchet MS, Helvetica, sans-serif',
                    'Verdana, Geneva, sans-serif'
                ],
                supportAllValues: true
            },
            fontSize: {
                options: [ 10, 12, 14, 'default', 18, 20, 22 ],
                supportAllValues: true
            },
            htmlSupport: {
                allow: [
                    {
                        name: /.*/,
                        attributes: true,
                        classes: true,
                        styles: true
                    }
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
            // The "super-build" contains more premium features that require additional configuration, disable them below.
            // Do not turn them on unless you read the documentation and know how to configure them and setup the editor.
            removePlugins: [
                // These two are commercial, but you can try them out without registering to a trial.
                'CKBox',
                //'CKFinder',
               // 'EasyImage',
                // 'Base64UploadAdapter',
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
            window.editor = editor;
            editor.model.document.on( 'change:data', () => {
                if ($('#description-error').length && ($("#feedAdd").length || $("#feedEdit").length)) {
                    var description = editor.getData();
                    description = $(description).text().trim();
                    if(description == ''){
                        $('#description-error').html('The description field is required.').addClass('invalid-feedback').show();
                    } else {
                        $('#description-error').removeClass('invalid-feedback').hide();
                    }
                } if (($('#description-error').length || $('#description-max-error').length) && ($("#courseAdd").length || $("#courseEdit").length)) {
                    var description = editor.getData();
                    description = $(description).text().trim();
                    if (description == ''){
                        $('#description-error').addClass('invalid-feedback').show();
                        $('#description-max-error').removeClass('invalid-feedback').hide();
                    } else if(description.length > 500) {
                        $('#description-max-error').addClass('invalid-feedback').show();
                        $('#description-error').removeClass('invalid-feedback').hide();
                    } else {
                        $('#description-error').removeClass('invalid-feedback').hide();
                        $('#description-max-error').removeClass('invalid-feedback').hide();
                    }
                } if (($('#description-error').length || $('#description-max-error').length) && ($("#shortsAdd").length || $("#shortsEdit").length)) {
                    var description = editor.getData();
                    description = $(description).text().trim();
                    if (description == ''){
                        $('#description-error').addClass('invalid-feedback').show();
                        $('#description-max-error').removeClass('invalid-feedback').hide();
                    } else if(description.length > 500) {
                        $('#description-max-error').addClass('invalid-feedback').show();
                        $('#description-error').removeClass('invalid-feedback').hide();
                    } else {
                        $('#description-error').removeClass('invalid-feedback').hide();
                        $('#description-max-error').removeClass('invalid-feedback').hide();
                    }
                } else if($('#notes-error-cstm').length && ($("#addgroupsession").length || $("#updategroupsession").length)){
                    //From create group session
                    var notes = editor.getData();
                    notes = $(notes).text().trim();
                    if(notes.length > 6000) {
                        $('#notes-error-cstm').html('The note field may not be greater than 6000 characters.').addClass('is-invalid').show();
                     } else {
                        $('#notes-error-cstm').removeClass('is-invalid').hide();
                    }
                }
            } );
        } )
        .catch( err => {
            console.error( err.stack );
        } );
    }
  
});