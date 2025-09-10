<?php

namespace App\Jobs;

use App\Models\ZcSurveyResponse;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

/**
 * Class WellbeingDataExtractJob
 */
class WellbeingDataExtractJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var $companiesId
     */
    public $companiesId;

    /**
     * @var $dataExtractFileName
     */
    public $dataExtractFileName;

    /**
     * @var $dataExtract
     */
    protected $questionExtract;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($companiesId, $dataExtractFileName, $questionExtract)
    {
        $this->queue               = 'default';
        $this->companiesId         = $companiesId;
        $this->dataExtractFileName = $dataExtractFileName;
        $this->questionExtract     = $questionExtract;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $appTimeZone = config('app.timezone');
            $fileName    = $this->dataExtractFileName;
            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();
            $index       = 2;

            $sheet->fromArray($this->questionExtract, null, 'A1');

            $surveyResponse = ZcSurveyResponse::leftjoin('zc_survey_log', 'zc_survey_log.id', '=', 'zc_survey_responses.survey_log_id')
                ->leftJoin('zc_categories', 'zc_categories.id', '=', 'zc_survey_responses.category_id')
                ->leftJoin('zc_sub_categories', 'zc_sub_categories.id', '=', 'zc_survey_responses.sub_category_id')
                ->leftJoin('companies', 'companies.id', '=', 'zc_survey_responses.company_id')
                ->leftJoin('zc_survey', 'zc_survey.id', '=', 'zc_survey_log.survey_id')
                ->leftJoin('zc_questions', 'zc_questions.id', '=', 'zc_survey_responses.question_id')
                ->leftJoin('zc_question_types', 'zc_question_types.id', '=', 'zc_questions.question_type_id')
                ->leftJoin('zc_survey_questions', 'zc_survey_questions.question_id', '=', 'zc_questions.id')
                ->leftJoin('zc_questions_options', 'zc_questions_options.id', '=', 'zc_survey_responses.option_id')
                ->leftJoin('users', 'users.id', '=', 'zc_survey_responses.user_id')
                ->select(
                    'zc_survey.title',
                    'zc_survey_log.roll_out_date',
                    'zc_survey.id',
                    'companies.code',
                    'zc_categories.display_name AS categories_name',
                    'zc_sub_categories.display_name AS sub_categories_name',
                    'zc_question_types.name AS question_type',
                    'zc_question_types.display_name AS question_type_name',
                    'zc_questions.id AS question_id',
                    'zc_questions.title AS question_title',
                    'zc_survey_questions.order_priority',
                    'zc_questions_options.choice',
                    'zc_survey_responses.answer_value',
                    'zc_survey_responses.score',
                    'users.timezone',
                    \DB::raw('(SELECT GROUP_CONCAT(choice) FROM zc_questions_options WHERE question_id = zc_survey_responses.question_id GROUP BY question_id) AS choices')
                )
                ->whereIn('zc_survey_responses.company_id', $this->companiesId)
                ->distinct()
                ->groupBy('zc_survey_responses.id')
                ->get()
                ->chunk(500);

            $index = 2;
            $surveyResponse->each(function ($surveyRecords) use ($sheet, $index, $appTimeZone) {
                foreach ($surveyRecords as $value) {
                    $answers     = explode(',', $value['choices']);
                    $rollOutDate = Carbon::parse($value['roll_out_date'], $appTimeZone)->setTimezone($value['timezone'])->toDateTimeString();
                    $tempArray   = [
                        $value['title'] . ' - ' . $rollOutDate,
                        $value['id'],
                        $value['code'],
                        $value['categories_name'],
                        $value['sub_categories_name'],
                        $value['question_type_name'],
                        $value['question_id'],
                        $value['question_title'],
                        $value['order_priority'],
                        ($value['question_type'] == 'choice') ? $value['choice'] : $value['answer_value'],
                        array_key_exists('0', $answers) ? $answers['0'] : '',
                        array_key_exists('1', $answers) ? $answers['1'] : '',
                        array_key_exists('2', $answers) ? $answers['2'] : '',
                        array_key_exists('3', $answers) ? $answers['3'] : '',
                        array_key_exists('4', $answers) ? $answers['4'] : '',
                        array_key_exists('5', $answers) ? $answers['5'] : '',
                        array_key_exists('6', $answers) ? $answers['6'] : '',
                        $value['score'],
                    ];
                    $sheet->fromArray($tempArray, null, 'A' . $index);
                    $index = $index + 1;
                }
            });

            $writer    = new Csv($spreadsheet);
            $temp_file = tempnam(sys_get_temp_dir(), $fileName);
            $source    = fopen($temp_file, 'rb');
            $writer->save($temp_file);

            $foldername = config('data-extract.excelfolderpath');

            $uploaded = uploadFileToSpaces($source, "{$foldername}{$fileName}", "public");
            if (null != $uploaded && is_string($uploaded->get('ObjectURL'))) {
                $url      = $uploaded->get('ObjectURL');
                $uploaded = true;
            }
            return $url;
        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
