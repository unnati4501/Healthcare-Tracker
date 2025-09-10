$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $('#podcastManagment').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            type: 'POST',
            url: url.datatable,
            data: {
                status: 1,
                podcastName: $('#podcastName').val(),
                coach: $('#coach').val(),
                subcategory: $('#subcategory').val(),
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
            searchable: false,
            sortable: false
        }, {
            data: 'title',
        }, {
            data: 'companiesName',
            name: 'companiesName',
            sortable: false
        }, {
            data: 'duration',
            name: 'duration',
        }, {
            data: 'subcategory_name',
            name: 'subcategory_name'
        }, {
            data: 'category_tag',
            name: 'category_tag',
            visible: (roleGroup == 'zevo')
        }, {
            data: 'coach_name',
            name: 'coach_name'
        }, {
            data: 'created_at',
            name: 'created_at',
            searchable: false,
            render: function(data, type, row) {
                return moment.utc(data).tz(timezone).format(date_format);
            }
        }, {
            data: 'totalLikes',
            name: 'totalLikes',
            searchable: false,
        }, {
            data: 'view_count',
            name: 'view_count',
            searchable: false,
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
        order: [
            [0, 'desc']
        ],
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
        },
    });
    $(document).on('click', '.play-podcast-media', function(e) {
        var source = $(this).data('source'),
            title = ($(this).data('title') || ''),
            html = '';
            html = `<audio class='w-100 o-l-n' controls><source src='${source}' type='audio/mp3'></audio>`;
        $('#media-box .modal-title').html(title);
        $('#media-box .modal-body').html(html);
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
    $(document).on('click', '.podcastDelete', function(t) {
        $('#delete-model-box').data("id", $(this).data('id')).modal('show');
    });
    $(document).on('click', '#delete-model-box-confirm', function(e) {
        $('.page-loader-wrapper').show();
        var objectId = $('#delete-model-box').data("id");
        $.ajax({
            type: 'DELETE',
            url: url.delete.replace(':id', objectId),
            contentType: 'json'
        }).done(function(data) {
            if (data && data.deleted == true) {
                $('#podcastManagment').DataTable().ajax.reload(null, false);
                toastr.success(messages.deleted);
            } else {
                toastr.error(messages.delete_fail);
            }
        }).fail(function(data) {
            toastr.error(messages.delete_fail);
        }).always(function() {
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
        order: [
            [0, 'asc']
        ],
        dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
        language: {
            paginate: {
                previous: pagination.previous,
                next: pagination.next,
            },
            sInfo: "Entries _START_ to _END_",
            infoFiltered: ""
        },
    });
    $(document).on('click', '.preview_companies', function(e) {
        var _data = ($(this).data('rowdata') || ''),
            dataJson = [];
        if (_data != "") {
            _data = $.parseJSON(atob($(this).data('rowdata')));
            $(_data).each(function(index, el) {
                dataJson.push({
                    id: (index + 1),
                    group_type: el.group_type,
                    company: el.name
                });
            });
            $('#visible_to_company_tbl').DataTable().clear().rows.add(dataJson).order([0, 'asc']).search('').draw();
            $('#company_visibility_preview').modal('show')
        }
    });
});