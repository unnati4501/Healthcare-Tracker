<?php

namespace App\Console\Commands;

use App\Jobs\BookingDataExtractJob;
use App\Jobs\ExportDataExtractJob;
use App\Jobs\SendDataExportEmailJob;
use App\Jobs\WellbeingDataExtractJob;
use App\Models\Company;
use App\Models\Course;
use App\Models\Feed;
use App\Models\MeditationTrack;
use App\Models\Recipe;
use App\Models\Webinar;
use App\Models\ZcSurveyLog;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;

/**
 * Class SendCompaniesDataExtract
 */
class SendCompaniesDataExtract extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'companies:dataextract';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Data extract based on company code which mention in config file. - Irishlife data extract';

    /**
     * Company model object
     *
     * @var Company $model
     */
    protected $model;

    /**
     * Course model object
     *
     * @var Course $courseModel
     */
    protected $courseModel;

    /**
     * Feed (Stories) model object
     *
     * @var Feed $feedModel
     */
    protected $feedModel;

    /**
     * Meditation Track model object
     *
     * @var MeditationTrack $meditationTrackModel
     */
    protected $meditationTrackModel;

    /**
     * Webinar model object
     *
     * @var Webinar $webinarModel
     */
    protected $webinarModel;

    /**
     * Recipe model object
     *
     * @var Recipe $recipeModel
     */
    protected $recipeModel;

    /**
     * Survey model object
     *
     * @var ZcSurveyLog $zcSurveyLog
     */
    protected $zcSurveyLog;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Company $model, Course $courseModel, Feed $feedModel, MeditationTrack $meditationTrackModel, Webinar $webinarModel, Recipe $recipeModel, ZcSurveyLog $zcSurveyLog)
    {
        parent::__construct();
        $this->model                = $model;
        $this->courseModel          = $courseModel;
        $this->feedModel            = $feedModel;
        $this->meditationTrackModel = $meditationTrackModel;
        $this->webinarModel         = $webinarModel;
        $this->recipeModel          = $recipeModel;
        $this->zcSurveyLog          = $zcSurveyLog;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $cronData = [
            'cron_name'  => class_basename(__CLASS__),
            'unique_key' => generateProcessKey(),
        ];
        cronlog($cronData);

        try {
            $appEnvironment          = app()->environment();
            $appTimeZone             = config('app.timezone');
            $now                     = now($appTimeZone)->toDateTimeString();
            $unixTimestamp           = Carbon::now($appTimeZone)->timestamp;
            $dataExtractFileName     = 'ILH_Data_extract_' . $unixTimestamp . '.csv';
            $questionExtractFileName = 'ILH_WellbeingQuestions_extract_' . $unixTimestamp . '.csv';
            $bookingExtractFileName  = 'ILH_Bookings_extract_' . $unixTimestamp . '.csv';
            $dataExtract             = config('data-extract.irishlife_data_extract.heading.extract');
            $questionExtract         = config('data-extract.irishlife_data_extract.heading.question_extract');
            $bookingExtract          = config('data-extract.irishlife_data_extract.heading.booking_extract');
            $email                   = config('data-extract.irishlife_data_extract.email');
            $data                    = [
                'fileNames' => [
                    'ILH_Data_extract_' . $unixTimestamp               => $dataExtractFileName,
                    'ILH_WellbeingQuestions_extract_' . $unixTimestamp => $questionExtractFileName,
                    'ILH_Bookings_extract_' . $unixTimestamp           => $bookingExtractFileName,
                ],
            ];

            if ($appEnvironment == 'production') {
                $companyCode = config('data-extract.irishlife_data_extract.company_code.production');
            } elseif ($appEnvironment == 'uat') {
                $companyCode = config('data-extract.irishlife_data_extract.company_code.uat');
            } elseif ($appEnvironment == 'local') {
                $companyCode = config('data-extract.irishlife_data_extract.company_code.local');
            } elseif ($appEnvironment == 'dev') {
                $companyCode = config('data-extract.irishlife_data_extract.company_code.dev');
            } else {
                $companyCode = config('data-extract.irishlife_data_extract.company_code.qa');
            }

            $companyId = $this->model
                ->whereIn('code', $companyCode)
                ->where('subscription_start_date', '<=', $now)
                ->where('subscription_end_date', '>=', $now)
                ->select('id')
                ->first();

            if (!empty($companyId)) {
                $companies = $this->model
                    ->where('id', $companyId->id)
                    ->orWhere('parent_id', $companyId->id)
                    ->select('id', 'code')
                    ->get();

                if (!empty($companies)) {
                    $companiesIds = array_column($companies->toArray(), 'id');

                    // Get Masterclass Records based on companies Ids
                    $masterclassRecords = $this->courseModel
                        ->leftJoin('sub_categories', 'courses.sub_category_id', '=', 'sub_categories.id')
                        ->leftJoin('masterclass_company', 'courses.id', '=', 'masterclass_company.masterclass_id')
                        ->leftJoin('companies', 'companies.id', '=', 'masterclass_company.company_id')
                        ->select(
                            'courses.id',
                            'courses.title',
                            'courses.creator_id',
                            'companies.code',
                            'companies.id AS company_id',
                            DB::raw('"masterclass" as content_type'),
                            DB::raw("sub_categories.name as subcategory")
                        )
                        ->whereIn('masterclass_company.company_id', $companiesIds)
                        ->groupBy('courses.id')
                        ->get()
                        ->toArray();

                    // Get Stories Records based on Companies Ids
                    $feedRecords = $this->feedModel
                        ->leftJoin('sub_categories', 'feeds.sub_category_id', '=', 'sub_categories.id')
                        ->leftJoin('feed_company', 'feeds.id', '=', 'feed_company.feed_id')
                        ->leftJoin('companies', 'companies.id', '=', 'feed_company.company_id')
                        ->select(
                            'feeds.id',
                            'feeds.title',
                            'feeds.creator_id',
                            'companies.code',
                            'companies.id AS company_id',
                            DB::raw('"feed" AS content_type'),
                            DB::raw("sub_categories.name as subcategory")
                        )
                        ->whereIn('feed_company.company_id', $companiesIds)
                        ->groupBy('feeds.id')
                        ->get()
                        ->toArray();

                    // Get Meditation Track based on Companies Ids
                    $meditationRecords = $this->meditationTrackModel
                        ->leftJoin('sub_categories', 'meditation_tracks.sub_category_id', '=', 'sub_categories.id')
                        ->leftJoin('meditation_tracks_company', 'meditation_tracks.id', '=', 'meditation_tracks_company.meditation_track_id')
                        ->leftJoin('companies', 'companies.id', '=', 'meditation_tracks_company.company_id')
                        ->select(
                            'meditation_tracks.id',
                            'meditation_tracks.title',
                            DB::raw('1 AS creator_id'),
                            'companies.code',
                            'companies.id AS company_id',
                            DB::raw('"meditation" as content_type'),
                            DB::raw("sub_categories.name as subcategory")
                        )
                        ->whereIn('meditation_tracks_company.company_id', $companiesIds)
                        ->groupBy('meditation_tracks.id')
                        ->get()
                        ->toArray();

                    // Get Webinar Records based on Companies Ids
                    $webinarRecords = $this->webinarModel
                        ->leftJoin('sub_categories', 'webinar.sub_category_id', '=', 'sub_categories.id')
                        ->leftJoin('webinar_company', 'webinar.id', '=', 'webinar_company.webinar_id')
                        ->leftJoin('companies', 'companies.id', '=', 'webinar_company.company_id')
                        ->select(
                            'webinar.id',
                            'webinar.title',
                            DB::raw('1 AS creator_id'),
                            'companies.code',
                            'companies.id AS company_id',
                            DB::raw('"webinar" as content_type'),
                            DB::raw("sub_categories.name as subcategory")
                        )
                        ->whereIn('webinar_company.company_id', $companiesIds)
                        ->groupBy('webinar.id')
                        ->get()
                        ->toArray();

                    // Get Recipe Records based on Companies Ids
                    $recipeRecords = $this->recipeModel
                        ->leftJoin('recipe_category', 'recipe_category.recipe_id', '=', 'recipe.id')
                        ->leftJoin('sub_categories', 'recipe_category.sub_category_id', '=', 'sub_categories.id')
                        ->leftJoin('recipe_company', 'recipe.id', '=', 'recipe_company.recipe_id')
                        ->leftJoin('companies', 'companies.id', '=', 'recipe_company.company_id')
                        ->select(
                            'recipe.id',
                            'recipe.title',
                            'recipe.creator_id',
                            'companies.code',
                            'companies.id AS company_id',
                            DB::raw('"recipe" as content_type'),
                            DB::raw("sub_categories.name as subcategory")
                        )
                        ->whereIn('recipe_company.company_id', $companiesIds)
                        ->groupBy('recipe.id')
                        ->get()
                        ->toArray();

                    // Get Survey Log based on companies Ids
                    $surveyRecords = $this->zcSurveyLog
                        ->leftJoin('companies', 'companies.id', '=', 'zc_survey_log.company_id')
                        ->select(
                            'zc_survey_log.survey_id',
                            'zc_survey_log.company_id',
                            'zc_survey_log.roll_out_date',
                            'companies.code',
                        )
                        ->whereIn('zc_survey_log.company_id', $companiesIds)
                        ->distinct()
                        ->get()
                        ->toArray();

                    $appEnvironment = app()->environment();

                    if ($appEnvironment == 'production') {
                        $doSpaceBucket = config('data-extract.DO_SPACES_BUCKET');
                        $doSpaceKey    = config('data-extract.DO_SPACES_KEY');
                        $doSpaceSecret = config('data-extract.DO_SPACES_SECRET');

                        config(['filesystems.disks.spaces.bucket' => $doSpaceBucket]);
                        config(['filesystems.disks.spaces.key' => $doSpaceKey]);
                        config(['filesystems.disks.spaces.secret' => $doSpaceSecret]);
                    } else {
                        config(['data-extract.excelfolderpath' => 'irishlife_data_extract/']);
                    }

                    // Extract Job For User, Masterclass, Stories, Survey
                    \dispatch(new ExportDataExtractJob($companies, $dataExtractFileName, $dataExtract, $masterclassRecords, $surveyRecords, $feedRecords, $meditationRecords, $webinarRecords, $recipeRecords))->onQueue('default');

                    // Extract Job for Wellbeing Question Data Extract
                    \dispatch(new WellbeingDataExtractJob($companiesIds, $questionExtractFileName, $questionExtract))->onQueue('default');

                    // Extract Job for Event Booking Data Extract
                    \dispatch(new BookingDataExtractJob($companiesIds, $bookingExtractFileName, $bookingExtract))->onQueue('default');

                    // Job for Sending Emails
                    \dispatch(new SendDataExportEmailJob($email, $data))->onQueue('default');
                }
            }

            cronlog($cronData, 1);
        } catch (\Exception $exception) {
            \Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
            $cronData['is_exception'] = 1;
            $cronData['log_desc']     = $exception->getMessage();
            cronlog($cronData, 1);
        }
    }
}
