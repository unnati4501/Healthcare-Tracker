$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $('#webinarManagment').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            type: 'POST',
            url: url.datatable,
            data: {
                webinarname: $('#webinarname').val(),
                author: $('#author').val(),
                subcategory: $('#sub_category').val(),
                type: $('#type').val(),
                tag: $('#tag').val(),
                getQueryString: window.location.search
            },
        },
        columns: [{
            data: 'updated_at',
            name: 'updated_at',
            visible: false
        }, {
            data: 'logo',
            name: 'logo',
            className: "sorting_1",
            searchable: false,
            sortable: false
        }, {
            data: 'webinar_name',
            name: 'webinar_name'
        }, {
            data: 'duration',
            name: 'duration',
            className: 'text-center'
        }, {
            data: 'category',
            name: 'category'
        }, {
            data: 'companiesName',
            name: 'companiesName',
            sortable: false
        }, {
            data: 'category_tag',
            name: 'category_tag',
            visible: (roleGroup == 'zevo')
        }, {
            data: 'author',
            name: 'author'
        }, {
            data: 'created_at',
            name: 'created_at',
            searchable: false,
            render: function (data, type, row) {
                return moment.utc(data).tz(timezone).format(date_format);
            }
        }, {
            data: 'totalLikes',
            name: 'totalLikes',
            searchable: false,
            className: 'text-center'
        },{
            data: 'view_count',
            name: 'view_count',
            searchable: false,
            className: 'text-center'
        }, {
            data: 'actions',
            name: 'actions',
            searchable: false,
            sortable: false
        }],
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
        }, {
            targets: 1,
            className: 'text-center',
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

    $(document).on('click', '.play-meditation-media', function(e) {
        var source = $(this).data('source'),
            type = ($(this).data('type') || 1),
            webinarName = $(this).data('title');
            html = '';

        if (type == 1) {
            html = `<div class="video-wrap"><video class='w-100 o-l-n' controls><source src='${source}' type='video/mp4'></video></div>`;
        } else if (type == 2) {
            html = `<iframe allowfullscreen="" frameborder="0" src="https://www.youtube.com/embed/${source}?playsinline=1&rel=0&showinfo=0&color=white" width="100%" height="350" ></iframe>`;
        } else {
            html = `<iframe title="vimeo-player" src="https://player.vimeo.com/video/${source}" width="100%" height="360" frameborder="0" allowfullscreen></iframe>`;
        }

        $('#media-box .modal-title').html(webinarName);
        $('#media-box .modal-body .video-wrap').html(html);
        $('#media-box').modal('show');
    });
    $(document).on('hidden.bs.modal', '#media-box', function(e) {
        if ($("#media-box video").length > 0) {
            $("#media-box video")[0].pause();
            $('#media-box video source').removeAttr('src');
        } else if ($("#media-box audio").length > 0) {
            $("#media-box audio")[0].pause();
            $('#media-box audio source').removeAttr('src');
        } else if ($("#media-box iframe").length > 0) {
            $("#media-box iframe").remove();
        }
    });
    $(document).on('click', '#webinarDelete', function(t) {
        $('#delete-model-box').data("id", $(this).data('id'));
        $('#delete-model-box').modal('show');
    });
    $(document).on('click', '#delete-model-box-confirm', function(e) {
        $('.page-loader-wrapper').show();
        var objectId = $('#delete-model-box').data("id");

        $.ajax({
            type: 'DELETE',
            url: url.delete.replace(':id',objectId),
            crossDomain: true,
            cache: false,
            contentType: 'json'
        })
        .done(function(data) {
            if (data && data.deleted == true) {
                $('#webinarManagment').DataTable().ajax.reload(null, false);
                toastr.success(message.webinarDeleted);
            } else {
                toastr.error(message.failedDeleteWebinar);
            }
        })
        .fail(function(data) {
            toastr.error(message.failedDeleteWebinar);
        })
        .always(function() {
            $('#delete-model-box').modal('hide');
            $('.page-loader-wrapper').hide();
        });
    });

    $('#visible_to_company_tbl').DataTable({
        lengthChange: false,
        pageLength: 10,
        autoWidth: false,
        columns: [{
            data: 'id',
            name: 'id'
        }, {
            data: 'group_type',
            name: 'group_type'
        }, {
            data: 'company',
            name: 'company'
        }],
        order: [[0, 'asc']],
        paging: true,
        searching: true,
        ordering: true,
        info: true,
        columnDefs: [{
            "targets": 'no-sort',
            "orderable": false,
        }],
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        language: {
            "paginate": {
                "previous": pagination.previous,
                "next": pagination.next
            },
             "sInfo": "Entries _START_ to _END_",
             "infoFiltered": ""
        }
    });

    $(document).on('click', '.preview_companies', function(e) {
        var _data = ($(this).data('rowdata') || ''),
            dataJson = [];
        if(_data != "") {
            _data = $.parseJSON(atob($(this).data('rowdata')));
            $(_data).each(function(index, el) {
                dataJson.push({
                    id: (index + 1),
                    group_type: el.group_type,
                    company: el.name
                });
            });
            $('#visible_to_company_tbl').DataTable()
                .clear()
                .rows
                .add(dataJson)
                .order([0, 'asc'])
                .search('')
                .draw();
            $('#company_visibility_preview').modal('show')
        }
    });
});