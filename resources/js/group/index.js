$(document).ready(function() {
        mainGroupsSearch();

        if(window.location.hash) {
            var hsah = window.location.hash;
            if($('.nav-tabs a[href="' + hsah + '"]').length > 0) {
                $('.nav-tabs a[href="' + hsah + '"]').tab('show');
                mainGroupsSearch();
                otherGroupsSearch();
            }
        }

        $(document).on('show.bs.tab', '#myTab.nav-tabs a', function(e) {
            var target = $(e.target).attr("href");
            if(target) {
                window.location.hash = target;
                mainGroupsSearch();
                otherGroupsSearch();
             }
        });
        $(document).on('click','#mainGroupsSearch', function(){
            mainGroupsSearch();
        });
        $(document).on('click','#otherGroupsSearch',function(){
            otherGroupsSearch();
        });
        $(document).on('click','#resetSearch',function(){
            resetSearch();
        });
    });

    function mainGroupsSearch() {
        $('#mainGroupsManagment').dataTable().fnDestroy();
        $('#mainGroupsManagment').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: url.datatable,
                data: {
                    main: 1,
                    groupName: $('#groupName').val(),
                    sub_category: $('#sub_category').val(),
                    group_type: $('#group_type').val(),
                    getQueryString: window.location.search
                },
            },
            columns: [
                {data: 'updated_at', name: 'updated_at', class: 'hidden', visible: false},
                {data: 'logo', name: 'logo', searchable: false, sortable: false},
                {data: 'category', name: 'category'},
                {data: 'title', name: 'title'},
                {data: 'created_by', name: 'created_by'},
                {data: 'members', name: 'members'},
                {data: 'type', name: 'type'},
                {data: 'actions', name: 'actions', searchable: false, sortable: false}
            ],
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
            }],
            dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
            stateSave: false,
            language: {
                "paginate": {
                    "previous": pagination.previous,
                    "next": pagination.next
                }
            }
        });
    }

    function otherGroupsSearch() {
        $('#otherGroupsManagment').dataTable().fnDestroy();
        $('#otherGroupsManagment').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: url.datatable,
                data: {
                    main: 0,
                    groupName: $('#groupName2').val(),
                    sub_category: $('#sub_category2').val(),
                    is_archived: $('#is_archived').val(),
                    getQueryString: window.location.search
                },
            },
            columns: [
                {data: 'updated_at', name: 'updated_at', class: 'hidden', visible: false},
                {data: 'logo', name: 'logo', searchable: false, sortable: false},
                {data: 'category', name: 'category'},
                {data: 'title', name: 'title'},
                {data: 'members', name: 'members'},
                {data: 'is_archived', name: 'is_archived'},
                {data: 'actions', name: 'actions', searchable: false, sortable: false}
            ],
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
            }],
            stateSave: false,
            dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
            language: {
                "paginate": {
                    "previous": pagination.previous,
                    "next": pagination.next
                }
            }
        });
    }

    $(document).on('click', '#groupDelete', function (t) {
        var deleteConfirmModalBox = '#delete-model-box';
        $(deleteConfirmModalBox).attr("data-id", $(this).data('id'));
        $(deleteConfirmModalBox).modal('show');
    });
    $(document).on('click', '#delete-model-box-confirm', function (e) {
        $('.page-loader-wrapper').show();
        var deleteConfirmModalBox = '#delete-model-box';
        var objectId = $(deleteConfirmModalBox).attr("data-id");

        $.ajaxSetup({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
        });

        $.ajax({
            type: 'DELETE',
            url: url.delete.replace(':id', objectId),
            data: null,
            crossDomain: true,
            cache: false,
            contentType: 'json',
            success: function (data) {
                mainGroupsSearch();
                otherGroupsSearch();
                if (data['deleted'] == 'true') {
                    toastr.success(message.groupDeleted);
                } else if(data['deleted'] == 'use') {
                    toastr.error(message.groupInUse);
                } else {
                    toastr.error(message.unableToDeleteGroup);
                }
                var deleteConfirmModalBox = '#delete-model-box';
                $(deleteConfirmModalBox).modal('hide');
                $('.page-loader-wrapper').hide();
            },
            error: function (data) {
                mainGroupsSearch();
                otherGroupsSearch();
                toastr.error(message.unableToDeleteGroup);
                var deleteConfirmModalBox = '#delete-model-box';
                $(deleteConfirmModalBox).modal('hide');
                $('.page-loader-wrapper').hide();
            }
        });
    });

    $('a[data-bs-toggle="tab"]').on('click', function (e) {
        var id = $(this).attr("href");
        $('.form-group input[type="text"]').val('');
        if(id == '#mainGroups'){
            $('#groupName2').val('');
            $("#sub_category2").select2("val", "");
            $("#is_archived").select2("val", "");
            mainGroupsSearch();
        }
        if(id == '#otherGroups'){
            $('#groupName').val('');
            $("#sub_category").select2("val", "");
            $("#group_type").select2("val","");
            otherGroupsSearch();
        }
    });

    function resetSearch(){
        $('#groupName').val('');
        $('#groupName2').val('');
        $("#sub_category").select2("val", "");
        $("#group_type").select2("val","");
        $("#sub_category2").select2("val", "");
        $("#is_archived").select2("val", "");
        mainGroupsSearch();
        otherGroupsSearch();
    }