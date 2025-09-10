<div class="content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-xl-3">
                <h1>
                    {{ $mainTitle }}
                </h1>
                {{ Breadcrumbs::render($breadcrumb) }}
            </div>
            <!-- /.col -->
            <div class="col-xl-9 align-self-center text-xl-end mt-md-3 mt-xl-0">
                <div class="card search-card dashboard-main-filter">
                    <div class="card-body pb-0">
                        <div class="search-outer d-md-flex justify-content-between">
                            <div>
                                <div class="form-group">
                                    @if(\Auth::user()->roles()->first()->group == 'zevo')
                                    {{ Form::select('company', $companies, request()->get('company'), ['class' => 'form-control select2','id'=>'company', 'placeholder' => '', 'data-placeholder'=>trans('moods.analysis.filter.company'), 'autocomplete' => 'off', 'target-data' => 'department'] ) }}
                                    @else
                                    {{ Form::select('company', $companies, \Auth::user()->company()->first()->id, ['class' => 'form-control select2','id'=>'company', 'placeholder' => '', 'data-placeholder'=>trans('moods.analysis.filter.company'), 'autocomplete' => 'off', 'target-data' => 'department', 'disabled'=>true] ) }}
                                    @endif
                                </div>
                                <div class="form-group">
                                    @if(!empty($departments))
                                    {{ Form::select('department', $departments, request()->get('department'), ['class' => 'form-control select2','id'=>'department', 'placeholder' => '', 'data-placeholder'=>trans('moods.analysis.filter.department'), 'autocomplete' => 'off'] ) }}
                                    @else
                                    {{ Form::select('department', [], request()->get('department'), ['class' => 'form-control select2','id'=>'department', 'placeholder' => '', 'data-placeholder'=>trans('moods.analysis.filter.department'), 'autocomplete' => 'off', 'disabled'=>true] ) }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.col -->
        </div>
    </div>
    <!-- /.row -->
</div>