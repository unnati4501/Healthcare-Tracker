@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
@endsection

@section('content-header')

@include('admin.contentChallenge.breadcrumb', [
  'mainTitle' => trans('contentChallenge.activities.title.manage'),
  'breadcrumb' => Breadcrumbs::render('contentChallengeActivity.index', $challengeCategory->category),
  'back' => true
])
<!-- /.content-header -->
@endsection

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="card-table-outer" id="contentChallengeActivityManagment-wrap">
                <div class="table-responsive">
                    <table class="table custom-table" id="contentChallengeActivityManagment">
                        <h5>
                            <b>
                                {{$challengeCategory->category}} Activities Limits and Points
                                <b>
                                </b>
                            </b>
                        </h5>
                        <thead>
                            <tr>
                                <th>
                                    {{ trans('contentChallenge.activities.table.id') }}
                                </th>
                                <th>
                                    {{ trans('contentChallenge.activities.table.activity') }}
                                </th>
                                <th>
                                    {{ trans('contentChallenge.activities.table.daily_limit') }}
                                </th>
                                <th>
                                    {{ trans('contentChallenge.activities.table.points_per_action') }}
                                </th>
                                <th class="th-btn-4 no-sort">
                                    {{ trans('contentChallenge.activities.table.action') }}
                                </th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        <div class="card form-card">
            {{ Form::open(['route' => ['admin.contentChallenge.update', $challengeCategory->id], 'class' => 'form-horizontal zevo_form_submit', 'method'=>'post', 'role' => 'form', 'id'=>'contentChallengeUpdate', 'files' => true]) }}
            <div class="row d-flex justify-content-center">
                <div class="col-lg-10 col-xl-10">
                    <div class="form-group">
                        {{ Form::label('description', trans('contentChallenge.form.labels.description')) }}
                        {{ Form::textarea('description', old('description', (isset($challengeCategory->description) ? htmlspecialchars_decode($challengeCategory->description) : null)), ['class' => 'form-control article-ckeditor', 'id' => 'description', 'data-errplaceholder' => '#description-error-cstm', 'data-formid' => "#feedAdd", 'data-upload-path' => route('admin.ckeditor-upload.feed-description', ['_token' => csrf_token() ]), 'disabled' => false]) }}
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="save-cancel-wrap">
                    <a class="btn btn-outline-primary" href="{!! route('admin.contentChallenge.index') !!}">
                        {{trans('buttons.general.cancel')}}
                    </a>
                    <button class="btn btn-primary" id="contentChallengeSubmit" type="submit">
                        {{trans('buttons.general.save')}}
                    </button>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>
@endsection
<!-- include datatable css -->
@section('after-scripts')
<!-- DataTables -->
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script src="{{ asset('assets/plugins/ckeditor5/ckeditor.js?var='.rand()) }}">
</script>
<script src="{{ asset('js/external/external-ckeditor.js?var='.rand()) }}">
</script>
<script type="text/javascript">
    var pagination                 = {{$pagination}};
    var challengeActivityListUrl   = '{{ route('admin.contentChallengeActivity.getContentChallengeActivities') }}';
    var challengeActivityUpdateUrl = '{{ route('admin.contentChallengeActivity.updateActivity') }}';
    var categoryId = '{{$challengeCategory->id}}';
    var message = {
        daily_limit_required         : '{{ trans('contentChallenge.activities.validation.daily_limit_required') }}',
        points_per_action_required   : '{{ trans('contentChallenge.activities.validation.points_per_action_required') }}',
    };
</script>
<script src="{{mix('js/contentchallenge/activity/index.js')}}">
</script>
@endsection
