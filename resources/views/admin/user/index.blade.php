@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
<style type="text/css">
.hidden{
    display: none !important;
}
</style>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.user.breadcrumb', [
    'mainTitle' => trans('user.title.index'),
    'breadcrumb' => Breadcrumbs::render('user.index'),
    'create' => true
])
<!-- /.content-header -->
@endsection

@section('content')
<section class="content">
    <div class="container-fluid">
        <!-- search-block -->
        <div class="card search-card {{ (request()->has('referrer') ? 'hidden' : '') }}">
            <div class="card-body pb-0">
                <h4 class="d-md-none">
                    {{ trans('buttons.general.filter') }}
                </h4>
                {{ Form::open(['route' => 'admin.users.index', 'class' => 'form-horizontal', 'method' => 'get', 'role' => 'form', 'id' => 'userSearch']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::text('recordEmail', request()->get('recordEmail'), ['class' => 'form-control', 'placeholder' => trans('user.filter.email'), 'id' => 'recordEmail', 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::text('recordName', request()->get('recordName'), ['class' => 'form-control', 'placeholder' => trans('user.filter.name'), 'id' => 'recordName', 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group">
                            {{ Form::select('role', $companyRoles, request()->get('role'), ['class' => 'form-control select2', 'id' => 'role', 'placeholder' => '', 'data-placeholder' => 'Select Role'] ) }}
                        </div>
                        <div class="form-group status">
                            {{ Form::select('status', $statuses, request()->get('status'), ['class' => 'form-control select2', 'id' => 'status', 'data-allow-clear' => 'false'] ) }}
                        </div>
                        @if($isSA == true)
                        <div class="form-group wbsstatus">
                            {{ Form::select('wbsstatus', $wbsstatus, request()->get('wbsstatus'), ['class' => 'form-control select2', 'id' => 'wbsStatus', 'data-allow-clear' => 'false'] ) }}
                        </div>
                        <div class="form-group responsibility">
                            {{ Form::select('responsibility', $responsibilitiesList, request()->get('responsibility'), ['class' => 'form-control select2', 'id' => 'responsibility', 'data-allow-clear' => 'false'] ) }}
                        </div>
                        @endif
                        @if(($isSA == true || $isRSA == true))
                        <div class="form-group company">
                            {{ Form::select('company', $companies, request()->get('company'), ['class' => 'form-control select2', 'id' => 'company', 'placeholder' => trans('user.filter.company'), 'data-placeholder' => trans('user.filter.company'), 'target-data' => 'team'] ) }}
                        </div>
                        @endif
                        <div class="form-group teamsearch">
                            {{ Form::select('team', ($teams ?? []), request()->get('team'), ['class' => 'form-control select2', 'id'=>'team', 'placeholder' => trans('user.filter.team'), 'data-placeholder' => trans('user.filter.team'), 'disabled' => (isset($teams) ? false : true)] ) }}
                        </div>
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{ trans('buttons.general.apply') }}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('admin.users.index') }}">
                            <i class="far fa-times">
                            </i>
                            <span class="d-md-none ms-2 ms-md-0">
                                {{ trans('buttons.general.reset') }}
                            </span>
                        </a>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
        <a class="btn btn-primary filter-btn" href="javascript:void(0);">
            <i class="far fa-filter me-2 align-middle">
            </i>
            <span class="align-middle">
                {{ trans('buttons.general.filter') }}
            </span>
        </a>
        <!-- /.search-block -->
        <!-- grid strt -->
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer">
                    <div class="table-responsive">
                        @if($isSA == true || $isRSA == true)
                        <table class="table custom-table" id="userManagment">
                            <thead>
                                <tr>
                                    <th class="d-none">
                                        {{ trans('user.table.updated_at') }}
                                    </th>
                                    <th>
                                        {{ trans('user.table.full_name') }}
                                    </th>
                                    <th>
                                        {{ trans('user.table.email') }}
                                    </th>
                                    <th>
                                        {{ trans('user.table.role') }}
                                    </th>
                                    <th>
                                        {{ trans('user.table.company') }}
                                    </th>
                                    <th>
                                        {{ trans('user.table.team_name') }}
                                    </th>
                                    @if($isSA == true)
{{--                                     <th class="text-center">
                                        {{ trans('user.table.is_health_coach') }}
                                    </th> --}}
                                    @endif
                                    <th class="no-sort th-btn-5">
                                        {{ trans('user.table.actions') }}
                                    </th>
                                </tr>
                            </thead>
                        </table>
                        @else
                        <table class="table custom-table" id="userManagmentCompany">
                            <thead>
                                <tr>
                                    <th class="d-none">
                                        {{ trans('user.table.updated_at') }}
                                    </th>
                                    <th>
                                        {{ trans('user.table.full_name') }}
                                    </th>
                                    <th>
                                        {{ trans('user.table.email') }}
                                    </th>
                                    <th>
                                        {{ trans('user.table.role') }}
                                    </th>
                                    <th class="teamsearch">
                                        {{ trans('user.table.team_name') }}
                                    </th>
                                    <th class="no-sort th-btn-5 novis">
                                        {{ trans('user.table.actions') }}
                                    </th>
                                </tr>
                            </thead>
                        </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- .modals -->
@include('admin.user.index-modals')
<!-- /.modals -->
@endsection

@section('after-scripts')
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/jszip.min.js?var='.rand())}}">
</script>
<script type="text/javascript">
    var url = {
        getUsersDt: `{{ route('admin.users.getUsers') }}`,
        delete: `{{ route('admin.users.delete', ':id')}}`,
        archive: `{{ route('admin.users.archive', ':id')}}`,
        disconnect: `{{ route('admin.users.disconnect', ':id') }}`,
        teamsUrl: '{{ route("admin.ajax.companyTeams", ":id") }}',
        findsession: `{{ route('admin.users.find-session', ':id')}}`,
    },
    role = `{{$role}}`,
    selectedRole = `{{$selectedRole}}`,
    isSA = `{{$isSA}}`,
    isRSA = `{{$isRSA}}`,
    button = {
        export: '<i class="far fa-file-excel me-3 align-middle"></i> {{ trans('user.buttons.export_to_excel') }}',
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
        entry_per_page: `{!! trans('buttons.pagination.entry_per_page') !!}`,
    }
    data = {
        teamSection: `{{ ($teamSection) ? 0 : 1 }}`
    };

    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#userManagment').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: url.getUsersDt,
                data: {
                    status: 1,
                    recordName: $('#recordName').val(),
                    recordEmail: $('#recordEmail').val(),
                    recordStatus: $('#status').val(),
                    wbsStatus: $('#wbsStatus').val(),
                    responsibility: $('#responsibility').val(),
                    company: $('#company').val(),
                    team: $('#team').val(),
                    role: $('#role').val(),
                    getQueryString: window.location.search
                },
            },
            columns: [
                { data: 'updated_at', name: 'updated_at' , visible: false },
                { data: 'fullName', name: 'fullName',
                    render:function(data, type, row) {
                        return row.fullName;
                    }
                },
                { data: 'email', name: 'email' },
                { data: 'roleName', name: 'roleName' },
                { data: 'companyName', name: 'companyName',
                    render:function(data, type, row) {
                        return ((data) ? data : "-");
                    }
                },
                { data: 'teamName', name: 'teamName',
                    render:function(data, type, row) {
                        return ((data) ? data : "-");
                    }
                },
                @if($isSA == true)
                // { data: 'is_coach', name: 'is_coach' },
                @endif
                { data: 'actions', name: 'actions', className: 'no-sort', searchable: false, sortable: false }
            ],
            paging: true,
            dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
            pageLength: parseInt(pagination.value),
            @if($role == 'super_admin')
            lengthMenu: [[25, 50, 100, 500], [25, 50, 100, 500]],
            @endif
            lengthChange: (role == 'super_admin' ? true : false),
            searching: false,
            order: [],
            info: true,
            autoWidth: false,
            columnDefs: [{
                targets: 'no-sort',
                orderable: false,
            }],
            language: {
                paginate: {
                    previous: pagination.previous,
                    next: pagination.next,
                },
                @if($role == 'super_admin')
                lengthMenu: pagination.entry_per_page + " _MENU_",
                @endif
            },
        });

        $('#userManagmentCompany').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: url.getUsersDt,
                data: {
                    status: 1,
                    recordName: $('#recordName').val(),
                    recordEmail: $('#recordEmail').val(),
                    // recordCoach: $('#health_coach').val(),
                    recordStatus: $('#status').val(),
                    company: $('#company').val(),
                    team: $('#team').val(),
                    role: $('#role').val(),
                    getQueryString: window.location.search
                },
            },
            columns: [
                { data: 'updated_at', name: 'updated_at' , visible: false },
                { data: 'fullName', name: 'fullName',
                    render:function(data, type, row) {
                        return row.fullName;
                    }
                },
                { data: 'email', name: 'email' },
                { data: 'roleName', name: 'roleName' },
                { data: 'teamName', name: 'teamName', className: 'teamsearch', visible: (data.teamSection == 0) ? true : false },
                { data: 'actions', name: 'actions', searchable: false, sortable: false, className: 'no-sort', visible: {{ (request()->has('referrer') ? 'false' : 'true') }} }
            ],
            paging: true,
            dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
            pageLength: parseInt(pagination.value),
            lengthChange: true,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            searching: false,
            ordering: true,
            order: [],
            info: true,
            autoWidth: false,
            columnDefs: [{
                targets: 'no-sort',
                orderable: false,
            }],
            stateSave: false,
            // dom: 'lBfrtip',
            buttons: [
            {
                extend: 'excel',
                text: button.export,
                className: 'btn btn-primary',
                title: 'user_list_'+Date.now(),
                download: 'open',
                orientation:'landscape',
                exportOptions: {
                    columns: ':visible:not(.novis)',
                    order : 'current'
                }
            }],
            language: {
                paginate: {
                    previous: pagination.previous,
                    next: pagination.next,
                },
                lengthMenu: "Entries per page _MENU_",
            },
            // dom: '<<"pagination-top"lB><tr><"pagination-wrap"<"pagination-left"i>p>',
            // initComplete: (settings, json) => {
            //     $('.pagination-wrap').appendTo(".card-table-outer");
            // },
        });

        $(document).on('click', '.userDelete', function (t) {
            $('#delete-model-box').data("id", $(this).data('id')).modal('show');
        });

        $(document).on('click', '.softDelete', function (t) {
            var username = $(this).parent().siblings(":first").text();
            var objectId = $(this).data('id');
            $.ajax({
                type: 'GET',
                url: url.findsession.replace(':id', objectId),
                data: null,
                crossDomain: true,
                cache: false,
                contentType: 'json',
                success: function (data) {
                    $('#archive-model-box').modal('hide');
                    if (data.status) {
                        $('#normal-message').addClass('d-none');
                        $('#ongoing-message').removeClass('d-none').html(data.name + ' has Upcoming/Ongoing session(s). Are you sure you want to archive ' + data.name +'?'); 
                    } else {
                        $('#normal-message').removeClass('d-none');
                        $('#ongoing-message').addClass('d-none');
                        $('#archive-user-name').text(username);
                    }
                    $('#archive-model-box').data("id", objectId).modal('show');
                },
                error: function (data) {
                    if (data == 'Forbidden') {
                        toastr.error("Failed to archive user, Please try again!");
                    }
                }
            });
        });

        $(document).on('click', '#delete-model-box-confirm', function (e) {
            $('.page-loader-wrapper').show();
            var objectId = $('#delete-model-box').data("id");
            $.ajax({
                type: 'DELETE',
                url: url.delete.replace(':id', objectId),
                data: null,
                crossDomain: true,
                cache: false,
                contentType: 'json',
                success: function (data) {
                    $('#userManagment').DataTable().ajax.reload(null, false);
                    $('#userManagmentCompany').DataTable().ajax.reload(null, false);
                    if (data['deleted'] == 'true') {
                        toastr.success("User has been deleted successfully");
                    } else if(data['deleted'] == 'company_admin') {
                        toastr.error(`You can not delete this user as user is the only admin of company ${data['company']}. You can delete this user after creating another admin of the same company!`);
                    } else if(data['deleted'] == 'associatedPresenter') {
                        toastr.error("You can not delete this user as user is associated with events!");
                    } else {
                        toastr.error("Failed to delete user, Please try again!");
                    }

                    $('#delete-model-box').modal('hide');
                },
                error: function (data) {
                    if (data == 'Forbidden') {
                        toastr.error("Failed to delete user, Please try again!");
                    }
                    $('#delete-model-box').modal('hide');
                }
            })
            .always(function() {
                $('.page-loader-wrapper').hide();
            });
        });

        $(document).on('click', '#archive-model-box-confirm', function (e) {
            $('.page-loader-wrapper').show();
            var objectId = $('#archive-model-box').data("id");
            deleteWellbeingSpecialist(objectId);
            $('.page-loader-wrapper').hide();
        });

        $(document).on('click', '.userDisconnect', function (t) {
            var disconnectConfirmModalBox = '#disconnect-model-box';
            $(disconnectConfirmModalBox).attr("data-id", $(this).data('id'));
            $(disconnectConfirmModalBox).modal('show');
        });

        $(document).on('click', '#disconnect-model-box-confirm', function (e) {
            var disconnectConfirmModalBox = '#disconnect-model-box';
            var objectId = $(disconnectConfirmModalBox).attr("data-id");

            $.ajax({
                type: 'GET',
                url: url.disconnect.replace(':id', objectId),
                data: null,
                crossDomain: true,
                cache: false,
                contentType: 'json',
                success: function (data) {
                    $('#userManagment').DataTable().ajax.reload(null, false);
                    $('#userManagmentCompany').DataTable().ajax.reload(null, false);
                    if (data['status'] == 1) {
                        toastr.success(data['data']);
                    } else {
                        toastr.error("Something went wrong.!");
                    }
                    var disconnectConfirmModalBox = '#disconnect-model-box';
                    $(disconnectConfirmModalBox).modal('hide');
                }
            });
        });

        $('#company').change(function() {
            if ($('#company').val() != '' && $('#company').val() != null) {
                if ($(this).attr("id") == 'company' && $(this).attr('target-data') == 'team') {
                    var select = $(this).attr("id");
                        value = $(this).val(),
                        teamDependent = $(this).attr('target-data'),
                        _token = $('input[name="_token"]').val();
                    $('#' + teamDependent).attr('disabled', true);
                    $.ajax({
                        url: url.teamsUrl.replace(':id', value),
                        method: 'get',
                        data: {
                            _token: _token
                        },
                        success: function(result) {
                            $('#' + teamDependent).empty();
                            $('#' + teamDependent).attr('disabled', false);
                            $('#' + teamDependent).val('').trigger('change').append('<option value="">Select</option>');
                            $('#' + teamDependent).removeClass('is-valid');
                            $.each(result.result, function(key, value) {
                                $('#' + teamDependent).append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                            if (Object.keys(result.result).length == 1) {
                                $.each(result.result, function(key, value) {
                                    $('#' + teamDependent).select2('val', value.id);
                                });
                            }
                        }
                    })
                }
            }
        });

        if (selectedRole != 'wellbeing_specialist') {
            $('.responsibility, .wbsstatus').hide();
            $('.status, .company, .teamsearch').show();
        } else {
            $('.responsibility, .wbsstatus').show();
            $('.status, .company, .teamsearch').hide();
        }
        $(document).on('change', '#role', function (t) {
            $('#responsibility').select2({width: '100%'});
            if ($("#role option:selected").text() == 'Wellbeing Specialist') {
                $('#company').val('').trigger('change');
                $('#team').val('').trigger('change');
                $('.status, .company, .teamsearch').hide();
                $('.responsibility, .wbsstatus').show();
                $('#responsibility').val('3').select2();
            } else {
                $('.company, .teamsearch, .status').show();
                $('.responsibility, .wbsstatus').hide();
                $('#responsibility').val('3').select2();
            }
        });

        if (isSA || isRSA) {
            $("#company").on("select2:unselect", function (e) {
                $('#team').val('').trigger('change');
                $('#team').attr('disabled',true);
            });

            $("#role").on("select2:unselect", function (e) {
                $('#company').val('').trigger('change');
                $('#team').val('').trigger('change');
                $('#team').attr('disabled',true);
            });
        }

        if(data.teamSection != 0) {
            $('.teamsearch').hide();
        }
    });
function deleteWellbeingSpecialist(objectId) {
    $.ajax({
        type: 'DELETE',
        url: url.archive.replace(':id', objectId),
        data: null,
        crossDomain: true,
        cache: false,
        contentType: 'json',
        success: function (data) {
            $('#userManagment').DataTable().ajax.reload(null, false);
            $('#userManagmentCompany').DataTable().ajax.reload(null, false);
            if (data['deleted'] == 'true') {
                toastr.success("User has been archive successfully");
            } else if(data['deleted'] == 'company_admin') {
                toastr.error(`You can not archive this user as user is the only admin of company ${data['company']}. You can archive this user after creating another admin of the same company!`);
            } else if(data['deleted'] == 'associatedPresenter') {
                toastr.error("You can not archive this user as user is associated with events!");
            } else {
                toastr.error("Failed to archive user, Please try again!");
            }

            $('#archive-model-box').modal('hide');
        },
        error: function (data) {
            if (data == 'Forbidden') {
                toastr.error("Failed to archive user, Please try again!");
            }
            $('#archive-model-box').modal('hide');
        }
    })
    .always(function() {
        $('.page-loader-wrapper').hide();
    });
}
</script>
@endsection
