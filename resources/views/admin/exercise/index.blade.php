@extends('layouts.app')

@section('after-styles')
<!-- DataTables -->
<link href="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand())}}" rel="stylesheet"/>
@endsection
@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.exercise.breadcrumb',[
    'appPageTitle' => trans('exercise.title.index_title'),
    'breadcrumb' => 'exercise.index',
    'create'     => true,
])
<!-- /.content-header -->
@endsection
@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card search-card">
            <!-- /.card-header -->
            <div class="card-body pb-0">
                <h4 class="d-md-none">
                    {{ trans('exercise.title.filter') }}
                </h4>
                {{ Form::open(['route' => 'admin.exercises.index', 'class' => 'form-horizontal', 'method'=>'get','role' => 'form', 'id'=>'exerciseSearch']) }}
                <div class="search-outer d-md-flex justify-content-between">
                    <div>
                        <div class="form-group">
                            {{ Form::text('exerciseName', request()->get('exerciseName'), ['class' => 'form-control', 'placeholder' => trans('exercise.filter.search_by_exercise'), 'id' => 'exerciseName', 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                    <div class="search-actions align-self-start">
                        <button class="me-md-4 filter-apply-btn" type="submit">
                            {{trans('buttons.general.apply')}}
                        </button>
                        <a class="filter-cancel-icon" href="{{ route('admin.exercises.index') }}">
                            <i class="far fa-times">
                            </i>
                            <span class="d-md-none ms-2 ms-md-0">
                                {{trans('buttons.general.reset')}}
                            </span>
                        </a>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="card-table-outer" id="exerciseManagment-wrap">
                    <div class="table-responsive">
                        <table class="table custom-table" id="exerciseManagment">
                            <thead>
                                <tr>
                                    <th class="text-center" style="display: none">
                                        {{trans('exercise.table.updated_at')}}
                                    </th>
                                    <th class="text-center no-sort th-btn-2">
                                        {{trans('exercise.table.logo')}}
                                    </th>
                                    <th>
                                        {{trans('exercise.table.exercise_name')}}
                                    </th>
                                    <th>
                                        {{trans('exercise.table.description')}}
                                    </th>
                                    <th class="text-center th-btn-2 no-sort">
                                        {{trans('exercise.table.action')}}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Delete Model Popup -->
@include('admin.exercise.delete-model')
@endsection
<!-- include datatable css -->
@section('after-scripts')
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script type="text/javascript">
    var url = {
        datatable: `{{ route('admin.exercises.getExercises') }}`,
        delete: `{{ route('admin.exercises.delete', ':id') }}`,
    },
    pagination = {
        value: {{ $pagination }},
        previous: `{!! trans('buttons.pagination.previous') !!}`,
        next: `{!! trans('buttons.pagination.next') !!}`,
    },
    message = {
        exercise_deleted: `{{ trans('exercise.modal.exercise_deleted') }}`,
        exercise_in_use: `{{ trans('exercise.modal.exercise_in_use') }}`,
        unable_to_delete_exercise: `{{ trans('exercise.modal.unable_to_delete_exercise') }}`,
    };
</script>
<script src="{{ asset('js/exercise/index.js') }}" type="text/javascript">
</script>
@endsection
