<?php

namespace App\Jobs;

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
 * Class ExportDataExtractJob
 */
class ExportDataExtractJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var $companies
     */
    public $companies;

    /**
     * @var $dataExtractFileName
     */
    public $dataExtractFileName;

    /**
     * @var $dataExtract
     */
    protected $dataExtract;

    /**
     * @var $masterclassRecords
     */
    protected $masterclassRecords;

    /**
     * @var $surveyRecords
     */
    protected $surveyRecords;

    /**
     * @var $feedRecords
     */
    protected $feedRecords;
    /**
     * @var $meditationRecords
     */
    protected $meditationRecords;
    /**
     * @var $webinarRecords
     */
    protected $webinarRecords;
    /**
     * @var $recipeRecords
     */
    protected $recipeRecords;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($companies, $dataExtractFileName, $dataExtract, $masterclassRecords, $surveyRecords, $feedRecords, $meditationRecords, $webinarRecords, $recipeRecords)
    {
        $this->queue               = 'default';
        $this->companies           = $companies;
        $this->dataExtractFileName = $dataExtractFileName;
        $this->dataExtract         = $dataExtract;
        $this->masterclassRecords  = $masterclassRecords;
        $this->surveyRecords       = $surveyRecords;
        $this->feedRecords         = $feedRecords;
        $this->meditationRecords   = $meditationRecords;
        $this->webinarRecords      = $webinarRecords;
        $this->recipeRecords       = $recipeRecords;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $registedUserSubCategories    = config('data-extract.irishlife_data_extract.registered_platform_users.sub_category');
            $registedUserField            = config('data-extract.irishlife_data_extract.registered_platform_users.field');
            $activeUserSubCategories      = config('data-extract.irishlife_data_extract.active_user.sub_category');
            $activeUserField              = config('data-extract.irishlife_data_extract.active_user.field');
            $masterclassUserField         = config('data-extract.irishlife_data_extract.masterclass.field');
            $wellbeingSurveySubCategories = config('data-extract.irishlife_data_extract.wellbeing_survey.sub_category');
            $wellbeingSurveyField         = config('data-extract.irishlife_data_extract.wellbeing_survey.field');
            $fileName                     = $this->dataExtractFileName;
            $spreadsheet                  = new Spreadsheet();
            $sheet                        = $spreadsheet->getActiveSheet();
            $index                        = 2;
            $date                         = Carbon::now()->toDateString();

            $sheet->fromArray($this->dataExtract, null, 'A1');
            // All registed user Records
            foreach ($this->companies as $value) {
                $records = [];
                foreach ($registedUserSubCategories as $subcategoryKey => $subcategoryValue) {
                    $records = GetUserCountBasedOnFields($subcategoryKey, $value->id, 'registered');
                    foreach ($registedUserField as $userFieldKey => $userFieldValue) {
                        $tempArray = [
                            $value->code,
                            'Registered Platform Users',
                            $subcategoryValue,
                            $userFieldValue,
                            isset($records[$userFieldKey]) ? $records[$userFieldKey] : '0',
                            'Whole',
                        ];
                        $sheet->fromArray($tempArray, null, 'A' . $index);
                        $index = $index + 1;
                    }
                }
            }

            // Active user Records
            foreach ($this->companies as $value) {
                $records = $recordsAvg = [];
                foreach ($activeUserSubCategories as $subcategoryKey => $subcategoryValue) {
                    $records    = GetUserCountBasedOnFields($subcategoryKey, $value->id, 'active');
                    $recordsAvg = GetUserCountBasedOnFields($subcategoryKey, $value->id, 'active', 'avg');
                    foreach ($activeUserField as $userFieldKey => $userFieldValue) {
                        $tempArray = [
                            $value->code,
                            'Active Users',
                            $subcategoryValue,
                            $userFieldValue . ' - Number',
                            isset($records[$userFieldKey]) ? $records[$userFieldKey] : '0',
                            'Whole',
                        ];
                        $sheet->fromArray($tempArray, null, 'A' . $index);
                        $index        = $index + 1;
                        $tempAvgArray = [
                            $value->code,
                            'Active Users',
                            $subcategoryValue,
                            $userFieldValue . ' - Average Age',
                            isset($recordsAvg[$userFieldKey]) ? round($recordsAvg[$userFieldKey]) : '0',
                            'Whole',
                        ];
                        $sheet->fromArray($tempAvgArray, null, 'A' . $index);
                        $index = $index + 1;
                    }
                }
            }

            // Masterclass, Recipe, Meditation, Webinar, Stories All content Records
            $mergeContent = [$this->masterclassRecords, $this->feedRecords, $this->meditationRecords, $this->webinarRecords, $this->recipeRecords];
            $records      = $recordsAvg      = [];
            foreach ($mergeContent as $contentValue) {
                foreach ($contentValue as $cValue) {
                    $records    = GetUserCountBasedOnContents($cValue['id'], $cValue['company_id'], $cValue['content_type']);
                    $recordsAvg = GetUserCountBasedOnContents($cValue['id'], $cValue['company_id'], $cValue['content_type'], 'avg');
                    foreach ($masterclassUserField as $userFieldKey => $userFieldValue) {
                        $tempArray = [
                            $cValue['code'],
                            ucfirst($cValue['content_type']) . ' - ' . $cValue['subcategory'],
                            $cValue['title'],
                            $userFieldValue . ' - Number',
                            isset($records[$userFieldKey]) ? $records[$userFieldKey] : '0',
                            'Whole',
                        ];
                        $sheet->fromArray($tempArray, null, 'A' . $index);
                        $index        = $index + 1;
                        $tempAvgArray = [
                            $cValue['code'],
                            ucfirst($cValue['content_type']) . ' - ' . $cValue['subcategory'],
                            $cValue['title'],
                            $userFieldValue . ' - Average Age',
                            isset($recordsAvg[$userFieldKey]) ? round($recordsAvg[$userFieldKey]) : '0',
                            'Whole',
                        ];
                        $sheet->fromArray($tempAvgArray, null, 'A' . $index);
                        $index = $index + 1;
                    }
                }
            }

            // Wellbeing Survey Sub Categories Records
            foreach ($this->surveyRecords as $value) {
                $records = [];
                $date    = Carbon::parse($value['roll_out_date'])->toDateString();
                foreach ($wellbeingSurveySubCategories as $subcategoryKey => $subcategoryValue) {
                    $records    = GetUserCountBasedOnWellbeingSurvey($value['survey_id'], $value['company_id'], $subcategoryKey);
                    $totalCount = array_sum($records);
                    foreach ($wellbeingSurveyField as $userFieldKey => $userFieldValue) {
                        $userValue = isset($records[$userFieldKey]) ? $records[$userFieldKey] : '0';
                        if ($userValue != 0) {
                            $userValue = (($subcategoryValue == 'Responses') ? $userValue : ($userValue * 100 / $totalCount));
                        }
                        $tempArray = [
                            $value['code'],
                            'Wellbeing Survey - ' . $value['survey_id'],
                            $subcategoryValue . ' ' . $date,
                            $userFieldValue . ' - Number',
                            ($subcategoryValue == 'Responses') ? $userValue : number_format((float)$userValue, 2, '.', ''),
                            ($subcategoryValue == 'Responses') ? 'Whole' : 'Percentage',
                        ];
                        $sheet->fromArray($tempArray, null, 'A' . $index);
                        $index = $index + 1;
                    }
                }
            }

            $writer    = new Csv($spreadsheet);
            $temp_file = tempnam(sys_get_temp_dir(), $fileName);
            $source    = fopen($temp_file, 'rb');
            $writer->save($temp_file);

            $foldername    = config('data-extract.excelfolderpath');

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
