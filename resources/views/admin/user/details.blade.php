@extends('layouts.app')

@section('after-styles')
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
<style type="text/css">
    .hidden { display: none; }
</style>
@endsection

@section('content')
@include('admin.user.breadcrumb',['mainTitle' => trans('labels.user.details')])
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-5 col-lg-4">
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <div class="box-profile-imagearea text-center">
                            <a class="btn btn-effect btn-outline-primary btn-left" href="{!! route('admin.users.index') !!}">
                                <i class="far fa-arrow-left">
                                </i>
                            </a>
                            <a class="btn btn-effect btn-outline-primary btn-right" href="{{route('admin.users.edit', $userData->id)}}">
                                <i class="fal fa-pencil-alt">
                                </i>
                            </a>
                            <img alt="User Image" class="profile-user-img" height="200" id="previewImg" src="{{ (!empty($userData->logo))? $userData->logo : asset('assets/dist/img/boxed-bg.png') }}" width="200">
                            </img>
                        </div>
                        <p class="text-muted text-center mb-0 mt-4">
                            User Name
                        </p>
                        <h6 class="profile-username text-center mt-0">
                            {{ $userData->full_name }}
                        </h6>
                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b>
                                    Email
                                </b>
                                <a class="float-end text-break">
                                    {{ $userData->email }}
                                </a>
                            </li>
                            <li class="list-group-item">
                                <b>
                                    Role Group
                                </b>
                                <a class="float-end">
                                    @if(!empty($userCompany) && ($userCompany->is_reseller || (!$userCompany->is_reseller && !is_null($userCompany->parent_id))))
                                        {{ ucwords(config('zevolifesettings.role_group.reseller')) }}
                                    @else
                                        {{ ucwords($roleData->group) }}
                                    @endif
                                </a>
                            </li>
                            @if(!empty($userCompany))
                            <li class="list-group-item">
                                <b>
                                    Company
                                </b>
                                <a class="float-end">
                                    {{ $userCompany->name }}
                                </a>
                            </li>
                            @if(!empty($userTeam))
                            <li class="list-group-item">
                                <b>
                                    Team
                                </b>
                                <a class="float-end">
                                    {{ $userTeam }}
                                </a>
                            </li>
                            @endif
                            @endif
                            @if(!empty($roleData))
                            <li class="list-group-item">
                                <b>
                                    Role
                                </b>
                                <a class="float-end">
                                    {{ $roleData->name }}
                                </a>
                            </li>
                            @endif
                        </ul>
                        <div class="sun-infographic mb-3">
                            <label>
                                Joining Date
                            </label>
                            <div class="mb-3">
                                <i class="fas fa-calendar-alt text-light-orange">
                                </i>
                                {{  Illuminate\Support\Carbon::parse($userData->created_at)->setTimezone($userData->timezone)->format(config('zevolifesettings.date_format.default_date')) }}
                            </div>
                            <div id="sun">
                            </div>
                            <div id="sun-shadow">
                            </div>
                            <div id="cloud3">
                            </div>
                            <div id="cloud4">
                            </div>
                            <div id="cloud5">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-7 col-lg-8">
                <div class="card ">
                    <div class="card-body box-profile">
                        <ul class="nav nav-tabs custom-tabbing flex-md-nowrap flex-column flex-md-row custom-tabbing border-bottom-0" id="myTab" role="tablist">
                            @if(!$userData->is_coach)
                            <li class="nav-item">
                                <a aria-controls="home" aria-selected="true" class="nav-link active h-100" data-bs-toggle="tab" href="#enrolledCourse" id="enrolledCourse-tab" role="tab">
                                    Enrolled Masterclass
                                </a>
                            </li>
                            <li class="nav-item">
                                <a aria-controls="profile" aria-selected="false" class="nav-link h-100" data-bs-toggle="tab" href="#completedCourse" id="completedCourse-tab" role="tab">
                                    Completed Masterclass
                                </a>
                            </li>
                            @endif
                            @if(!empty($userCompany) && ($roleData->group == 'company' || $roleData->group == 'reseller'))
                            @if(!$userCompany->is_reseller && $userCompany->allow_app == true)
                            <li class="nav-item">
                                <a aria-controls="enrolledChallanges" aria-selected="false" class="nav-link h-100" data-bs-toggle="tab" href="#enrolledChallanges" id="contact-tab" role="tab">
                                    Enrolled Challenges
                                </a>
                            </li>
                            <li class="nav-item">
                                <a aria-controls="completedChallanges" aria-selected="false" class="nav-link h-100" data-bs-toggle="tab" href="#completedChallanges" id="contact-tab" role="tab">
                                    Completed Challenges
                                </a>
                            </li>
                            @endif
                            @endif
                            @if($roleData->slug == 'health_coach' || (!empty($userCompany) && ($userCompany->is_reseller == true || !is_null($userCompany->parent_id))))
                            <li class="nav-item">
                                <a aria-controls="enrolledEvents" aria-selected="false" class="nav-link h-100" data-bs-toggle="tab" href="#enrolledEvents" id="contact-tab" role="tab">
                                    Enrolled Event
                                </a>
                            </li>
                            <li class="nav-item">
                                <a aria-controls="completedEvents" aria-selected="false" class="nav-link h-100" data-bs-toggle="tab" href="#completedEvents" id="contact-tab" role="tab">
                                    Completed Event
                                </a>
                            </li>
                            @endif
                        </ul>
                        <div class="tab-content" id="myTabContent">
                            @if(!$userData->is_coach)
                            <div aria-labelledby="enrolledCourse-tab" class="tab-pane fade show active" id="enrolledCourse" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="enrolledCourseUserList">
                                        <thead>
                                            <tr>
                                                <th class="text-center no-sort" style="display: none">
                                                    Joined At
                                                </th>
                                                <th class="text-center no-sort">
                                                    Logo
                                                </th>
                                                <th>
                                                    Course Name
                                                </th>
                                                <th>
                                                    Categories
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div aria-labelledby="completedCourse-tab" class="tab-pane fade" id="completedCourse" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="completedCourseUsersList">
                                        <thead>
                                            <tr>
                                                <th class="text-center no-sort" style="display: none">
                                                    Joined At
                                                </th>
                                                <th class="text-center no-sort">
                                                    Logo
                                                </th>
                                                <th>
                                                    Course Name
                                                </th>
                                                <th>
                                                    Categories
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif

                            @if(!empty($userCompany) && ($roleData->group == 'company' || $roleData->group == 'reseller'))
                            @if(!$userCompany->is_reseller && $userCompany->allow_app == true)
                            <div aria-labelledby="enrolledChallanges-tab" class="tab-pane fade" id="enrolledChallanges" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="enrolledChallangesusersList">
                                        <thead>
                                            <tr>
                                                <th class="text-center no-sort" style="display: none">
                                                    Updated At
                                                </th>
                                                <th class="text-center no-sort">
                                                    Logo
                                                </th>
                                                <th>
                                                    Challenges
                                                </th>
                                                <th>
                                                    Date
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div aria-labelledby="completedChallanges-tab" class="tab-pane fade" id="completedChallanges" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="completedChallangesusersList">
                                        <thead>
                                            <tr>
                                                <th class="text-center no-sort" style="display: none">
                                                    Updated At
                                                </th>
                                                <th class="text-center no-sort">
                                                    Logo
                                                </th>
                                                <th>
                                                    Challenges
                                                </th>
                                                <th>
                                                    Date
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif
                            @endif

                            @if($roleData->slug == 'health_coach' || (!empty($userCompany) && ($userCompany->is_reseller == true || !is_null($userCompany->parent_id))))
                            <div aria-labelledby="enrolledEvents-tab" class="tab-pane fade" id="enrolledEvents" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="enrolledEventsList">
                                        <thead>
                                            <tr>
                                                <th class="text-center no-sort hidden">
                                                    Updated At
                                                </th>
                                                <th class="text-center no-sort">
                                                    Logo
                                                </th>
                                                <th>
                                                    Event Name
                                                </th>
                                                <th>
                                                    Date
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div aria-labelledby="completedEvents-tab" class="tab-pane fade" id="completedEvents" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="completedEventsList">
                                        <thead>
                                            <tr>
                                                <th class="text-center no-sort hidden">
                                                    Updated At
                                                </th>
                                                <th class="text-center no-sort">
                                                    Logo
                                                </th>
                                                <th>
                                                    Event Name
                                                </th>
                                                <th>
                                                    Date
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('after-scripts')
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script type="text/javascript">
    $(document).ready(function() {
        if($('#enrolledCourse').length > 0) {
            enrolledCourseUserList();
        } else if($('#enrolledEvents').length > 0) {
            enrolledEventsList();
            $('.nav-tabs a[href="#enrolledEvents"]').tab('show');
        }

        $('a[data-bs-toggle="tab"]').on('click', function (e) {
            var id = $(this).attr("href");
            $('.form-group input[type="text"]').val('');
            if(id == '#enrolledCourse'){
               enrolledCourseUserList();
            }

            if(id == '#completedCourse'){
                completedCourseUsersList();
            }

            if(id == '#enrolledChallanges'){
                enrolledChallangesusersList();
            }

            if(id == '#completedChallanges'){
                completedChallangesusersList();
            }

            if(id == '#enrolledEvents'){
                enrolledEventsList();
            }

            if(id == '#completedEvents'){
                completedEventsList();
            }
        });
    });

    function enrolledCourseUserList() {
        $('#enrolledCourseUserList').dataTable().fnDestroy();
        $('#enrolledCourseUserList').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('admin.users.getUserCourseData', $userData->id) }}',
                data: {
                    status: 1,
                    type: 'enrolled'
                },
            },
            columns: [{
                data: 'joined_on',
                name: 'joined_on',
                className: 'hidden'
            }, {
                data: 'logo',
                name: 'logo',
                className: 'text-center'
            }, {
                data: 'title',
                name: 'title'
            }, {
                data: 'category',
                name: 'category'
            }],
            paging: true,
            pageLength: {{ $pagination }},
            dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
            lengthChange: false,
            searching: false,
            ordering: true,
            order: [
                [0, 'desc']
            ],
            info: true,
            autoWidth: false,
            stateSave: false
        });
    }

    function completedCourseUsersList() {
        $('#completedCourseUsersList').dataTable().fnDestroy();
        $('#completedCourseUsersList').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('admin.users.getUserCourseData', $userData->id) }}',
                data: {
                    status: 1,
                    type: 'completed'
                },
            },
            columns: [{
                data: 'joined_on',
                name: 'joined_on',
                className: 'hidden'
            }, {
                data: 'logo',
                name: 'logo',
                className: 'text-center'
            }, {
                data: 'title',
                name: 'title'
            }, {
                data: 'category',
                name: 'category'
            }],
            paging: true,
            pageLength: {{ $pagination }},
            dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
            lengthChange: false,
            searching: false,
            ordering: true,
            order: [[0, 'desc']],
            info: true,
            autoWidth: false,
            stateSave: false
        });
    }

    function enrolledChallangesusersList() {
        $('#enrolledChallangesusersList').dataTable().fnDestroy();
        $('#enrolledChallangesusersList').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('admin.users.getUserChallangeData', $userData->id) }}',
                data: {
                    status: 1,
                    type: 'enrolled'
                },
            },
            columns: [{
                data: 'updatedAt',
                name: 'updatedAt',
                className: 'hidden'
            }, {
                data: 'logo',
                name: 'logo',
                className: 'text-center'
            }, {
                data: 'title',
                name: 'title'
            }, {
                data: 'date',
                name: 'date'
            }],
            paging: true,
            pageLength: {{ $pagination }},
            dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
            lengthChange: false,
            searching: false,
            ordering: true,
            order: [[0, 'desc']],
            info: true,
            autoWidth: false,
            stateSave: false
        });
    }

    function completedChallangesusersList() {
        $('#completedChallangesusersList').dataTable().fnDestroy();
        $('#completedChallangesusersList').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('admin.users.getUserChallangeData', $userData->id) }}',
                data: {
                    status: 1,
                    type: 'completed'
                },
            },
            columns: [{
                data: 'updatedAt',
                name: 'updatedAt',
                className: 'hidden'
            }, {
                data: 'logo',
                name: 'logo',
                className: 'text-center'
            }, {
                data: 'title',
                name: 'title'
            }, {
                data: 'date',
                name: 'date'
            }],
            paging: true,
            dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
            pageLength: {{ $pagination }},
            lengthChange: false,
            searching: false,
            ordering: true,
            order: [[0, 'desc']],
            info: true,
            autoWidth: false,
            stateSave: false
        });
    }

    function enrolledEventsList() {
        $('#enrolledEventsList').dataTable().fnDestroy();
        $('#enrolledEventsList').DataTable({
            processing: true,
            serverSide: false,
            columns: [{
                data: 'updatedAt',
                name: 'updatedAt',
                className: 'hidden'
            }, {
                data: 'logo',
                name: 'logo',
                className: 'text-center'
            }, {
                data: 'title',
                name: 'title'
            }, {
                data: 'date',
                name: 'date'
            }],
            paging: true,
            dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
            pageLength: {{ $pagination }},
            lengthChange: false,
            searching: false,
            ordering: true,
            order: [[0, 'desc']],
            info: true,
            autoWidth: false,
            stateSave: false
        });
    }

    function completedEventsList() {
        $('#completedEventsList').dataTable().fnDestroy();
        $('#completedEventsList').DataTable({
            processing: true,
            serverSide: false,
            columns: [{
                data: 'updatedAt',
                name: 'updatedAt',
                className: 'hidden'
            }, {
                data: 'logo',
                name: 'logo',
                className: 'text-center'
            }, {
                data: 'title',
                name: 'title'
            }, {
                data: 'date',
                name: 'date'
            }],
            paging: true,
            dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
            pageLength: {{ $pagination }},
            lengthChange: false,
            searching: false,
            ordering: true,
            order: [[0, 'desc']],
            info: true,
            autoWidth: false,
            stateSave: false
        });
    }
</script>
@endsection
