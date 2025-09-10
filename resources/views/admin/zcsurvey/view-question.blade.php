@extends('layouts.app')

@section('after-styles')
<link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap5.min.css?var='.rand()) }}" rel="stylesheet"/>
@endsection

@section('content-header')
<!-- Content Header (Page header) -->
@include('admin.zcsurvey.breadcrumb', [
  'mainTitle' => trans('survey.title.preview_question'),
  'breadcrumb' => Breadcrumbs::render('survey.preview'),
  'back' => true
])
<!-- /.content-header -->
@endsection


@section('content')
<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <div class="card-header detailed-header p-0">
                    <div class="flex-wrap">
                        <div>
                            {{ Form::label('preview_title', trans('labels.zcsurvey.title'), ['class' => 'text-muted mb-1']) }}
                            <p>
                                {{ $zcSurvey->title }}
                            </p>
                        </div>
                        <div>
                            {{ Form::label('preview_description', trans('labels.zcsurvey.description'), ['class' => 'text-muted mb-1']) }}
                            <p>
                                {{ $zcSurvey->description }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="card-table-outer">
                    <div class="table-responsive mt-4">
                        <table class="table custom-table selected-questions-preview-table" id="finalQuestionList">
                            <thead>
                                <tr>
                                    <th class="no-sort">
                                        {{ trans('labels.zcsurvey.sr_no') }}
                                    </th>
                                    <th class="no-sort">
                                    </th>
                                    <th>
                                        {{ trans('labels.zcsurvey.questions') }}
                                    </th>
                                    <th>
                                        {{ trans('labels.zcsurvey.category') }}
                                    </th>
                                    <th>
                                        {{ trans('labels.zcsurvey.subcategory') }}
                                    </th>
                                    <th>
                                        {{ trans('labels.zcsurvey.question_type') }}
                                    </th>
                                    <th>
                                        {{ trans('labels.zcsurvey.added_date') }}
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
@endsection

@section('after-scripts')
<script src="{{asset('assets/plugins/moment/moment.min.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/moment/moment-timezone-with-data-10-year-range.js?var='.rand()) }}">
</script>
<script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js?var='.rand())}}">
</script>
<script src="{{asset('assets/plugins/datatables/dataTables.bootstrap5.min.js?var='.rand())}}">
</script>
<script type="text/javascript">
    var timezone = `{{ $timezone }}`,
        date_format = `{{ $date_format }}`,
        _getQuestions = "{{ route('admin.zcsurvey.getQuestions', $zcSurvey->id) }}",
        pagination = {
            value: {{ $pagination }},
            previous: `{!! trans('buttons.pagination.previous') !!}`,
            next: `{!! trans('buttons.pagination.next') !!}`,
        };

    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var finalQtable = $('#finalQuestionList').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: _getQuestions
            },
            columns: [{
                data: 'id',
                name: 'id'
            }, {
                data: 'is_premium',
                name: 'is_premium'
            }, {
                data: 'title',
                name: 'title'
            }, {
                data: 'category_name',
                name: 'category_name'
            }, {
                data: 'subcategory_name',
                name: 'subcategory_name'
            }, {
                data: 'questiontype_name',
                name: 'questiontype_name'
            }, {
                data: 'created_at',
                name: 'created_at',
                render: function(data, type, row) {
                    return ((row.question_created_at) ? moment.utc(row.question_created_at).tz(timezone).format(date_format) : moment().tz(timezone).format(date_format));
                }
            }],
            paging: false,
            dom: '<<"pagination-top"lB><t><"pagination-wrap"<"pagination-left"i>p>',
            lengthChange: false,
            searching: true,
            ordering: true,
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
                }
            },
            rowCallback: function(row, data, displayNum, displayIndex, dataIndex) {
                var pageInfo = finalQtable.page.info();
                $("td:eq(0)", row).html(((pageInfo.page) * pageInfo.length) + displayIndex + 1);
                return row;
            },
        });
    });
</script>
@endsection
