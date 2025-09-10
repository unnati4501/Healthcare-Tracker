<section class="col-lg-12">
    <div class="card collapsed-card">
        <div class="card-header detailed-header" data-qid="{{ $question->id }}" data-widget="collapse">
            <h3 class="align-items-center card-title d-flex mb-1">
                <span class="badge bg-primary me-2 font-12">
                    {{ $index }}
                </span>
                {{ $question->question }}
            </h3>
            <div class="card-sub-title">
                <span>
                    {{ $question->category_name }}
                </span>
                <i class="fal fa-long-arrow-right ms-1 me-1">
                </i>
                <span>
                    {{ $question->sub_category_name }}
                </span>
            </div>
            <div class="card-tools">
                <button class="btn btn-tool" data-widget="collapse" type="button">
                    <i class="fa fa-chevron-up">
                    </i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table custom-table" id="answers{{ $question->id }}">
                    <thead>
                        <tr>
                            <th class="th-btn-sm">
                                {{ trans('survey.hr_report.free_text_table.sr_no') }}
                            </th>
                            @if($isSA)
                            <th class="th-btn-3">
                                {{ trans('survey.hr_report.free_text_table.company') }}
                            </th>
                            @endif
                            <th>
                                {{ trans('survey.hr_report.free_text_table.answers') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center" colspan="2">
                                <i class="fa fa-spinner fa-spin">
                                </i>
                                {{ trans('survey.hr_report.messages.loading') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>