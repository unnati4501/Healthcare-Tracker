<?php

namespace App\Jobs;

use App\Events\SendMcSurveyReportExportEvent;
use App\Events\SendZcSurveyReportExportEvent;
use App\Models\Company;
use App\Models\CourseSurveyQuestionAnswers;
use App\Models\User;
use App\Models\ZcSurveyResponse;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ZcSurveyReportExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Company model object
     *
     * @var Company
     */
    public $company;

    /**
     * Request payload
     *
     * @var array
     */
    public $payload;

    /**
     * User modeal object
     *
     * @var User
     **/
    public $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Company $company, User $user, $payload)
    {
        $this->company = $company;
        $this->user    = $user;
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $appTimezone = config('app.timezone');
            $dateString  = now($appTimezone)->format('Ymd');
            $time        = time();
            $fileName    = str_slug("{$this->company->name} survey report {$this->payload['start_date']} {$this->payload['end_date']} ${time}") . ".xlsx";
            $root        = config("filesystems.disks.spaces.root");
            $timezone    = (!empty($this->user->timezone) ? $this->user->timezone : $appTimezone);
            $start_date  = Carbon::parse($this->payload['start_date'], $timezone)->setTime(0, 0, 0)->toDateTimeString();
            $end_date    = Carbon::parse($this->payload['end_date'], $timezone)->setTime(23, 59, 59)->toDateTimeString();

            if ($this->payload['type'] == "masterclass") {
                $model        = new CourseSurveyQuestionAnswers();
                $questionType = config('zevolifesettings.masterclass_survey_question_type');
                $foldername   = config('zevolifesettings.report-export.masterclass');

                // Header for both sheets
                $headers = ["Timestamp", "Company Name", "Category Name", "Masterclass Name", "Survey Type", "User ID", "Question Type", "Question ID", "Question", "Index", "Response", "Answer1", "Answer2", "Answer3", "Answer4", "Answer5", "Answer6", "Answer7", "Score"];

                $sheet = $spreadsheet->getActiveSheet();
                $sheet->fromArray($headers, null, 'A1');
                $responses = $model
                    ->select(
                        'course_survey_question_answers.id',
                        'course_survey_question_answers.user_id',
                        'course_survey_question_answers.course_id',
                        'course_survey_question_answers.survey_id',
                        'course_survey_question_answers.question_id',
                        'course_survey_question_answers.question_option_id',
                        'course_survey_question_answers.created_at'
                    )
                    ->join('course_survey_questions', 'course_survey_questions.id', '=', 'course_survey_question_answers.question_id')
                    ->with([
                        'course'   => function ($query) {
                            $query
                                ->select('courses.id', 'courses.title', 'courses.sub_category_id')
                                ->with(['subCategory' => function ($subQuery) {
                                    $subQuery->select('sub_categories.id', 'sub_categories.name');
                                }]);
                        },
                        'survey'   => function ($query) {
                            $query->select('course_survey.id', 'course_survey.type');
                        },
                        'question' => function ($query) {
                            $query
                                ->select(
                                    'course_survey_questions.id',
                                    'course_survey_questions.type',
                                    'course_survey_questions.title'
                                )
                                ->with([
                                    'courseSurveyOptions' => function ($subQuery) {
                                        $subQuery
                                            ->select(
                                                'course_survey_question_options.id',
                                                'course_survey_question_options.question_id',
                                                'course_survey_question_options.choice',
                                                'course_survey_question_options.score'
                                            )
                                            ->orderBy('course_survey_question_options.id');
                                    },
                                ]);
                        },
                    ])
                    ->where('company_id', $this->company->id)
                    ->where('course_survey_questions.type', 'single_choice')
                    ->whereRaw("CONVERT_TZ(course_survey_question_answers.created_at, ? , ?) BETWEEN ? AND ?", [
                        $appTimezone,$timezone,$start_date,$end_date,
                    ])
                    ->get()
                    ->chunk(50);

                foreach ($responses as $questions) {
                    foreach ($questions as $questionKey => $question) {
                        $optionsWithScore = [];
                        $options          = [0 => "", 1 => "", 2 => "", 3 => "", 4 => "", 5 => "", 6 => ""];
                        foreach ($question->question->courseSurveyOptions as $optionKey => $option) {
                            $options[$optionKey]           = $option->choice;
                            $optionsWithScore[$option->id] = [
                                'choice' => $option->choice,
                                'score'  => $option->score,
                                'key'    => ($optionKey + 1),
                            ];
                        }

                        $records = array_merge([
                            Carbon::parse($question->created_at)->format("Y-m-d"),
                            $this->company->name,
                            $question->course->subCategory->name,
                            $question->course->title,
                            ucfirst($question->survey->type),
                            ($time + $question->user_id),
                            $questionType[$question->question->type],
                            $question->question->id,
                            $question->question->title,
                            $optionsWithScore[$question->question_option_id]['key'],
                            $optionsWithScore[$question->question_option_id]['choice'],
                        ], $options, [
                            "" . $optionsWithScore[$question->question_option_id]['score'] . "",
                        ]);

                        $sheet->fromArray($records, null, 'A' . ($questionKey + 2));
                    }
                }
            } elseif ($this->payload['type'] == "zcsurvey") {
                $model      = new ZcSurveyResponse();
                $foldername = config('zevolifesettings.report-export.survey');
                $headers    = ["Timestamp", "Survey Title", "Survey ID", "Company Name", "Company ID", "Department Name", "User ID", "Category", "Sub Category", "Question Type ", "Question ID", "Question", "Index", "Response", "Answer1", "Answer2", "Answer3", "Answer4", "Answer5", "Answer6", "Answer7", "Score"];
                $sheet      = $spreadsheet->getActiveSheet();
                $sheet->fromArray($headers, null, 'A1');

                $responses = $model
                    ->select(
                        'id',
                        'user_id',
                        'score',
                        'company_id',
                        'department_id',
                        'survey_log_id',
                        'category_id',
                        'sub_category_id',
                        'question_id',
                        'option_id',
                        'created_at'
                    )
                    ->with([
                        'surveyLog'   => function ($query) {
                            $query
                                ->select('id', 'survey_id')
                                ->with(['survey' => function ($subQuery) {
                                    $subQuery->select('id', 'title');
                                }]);
                        },
                        'company'     => function ($query) {
                            $query->select('id', 'name');
                        },
                        'department'  => function ($query) {
                            $query->select('id', 'name');
                        },
                        'category'    => function ($query) {
                            $query->select('id', 'display_name');
                        },
                        'subcategory' => function ($query) {
                            $query->select('id', 'display_name');
                        },
                        'question'    => function ($query) {
                            $query
                                ->select('id', 'title', 'question_type_id')
                                ->where('question_type_id', 2)
                                ->with([
                                    'questiontype'    => function ($subQuery) {
                                        $subQuery->select('id', 'display_name');
                                    },
                                    'questionoptions' => function ($subQuery) {
                                        $subQuery
                                            ->select('id', 'question_id', 'score', 'choice')
                                            ->where('choice', '!=', 'meta');
                                    },
                                ]);
                        },
                    ])
                    ->where('company_id', $this->company->id)
                    ->whereRaw("CONVERT_TZ(zc_survey_responses.created_at, ?, ?) BETWEEN ? AND ?", [
                        $appTimezone,$timezone,$start_date,$end_date
                    ])
                    ->whereHas('question', function ($query) {
                        $query->where('question_type_id', 2);
                    })
                    ->get()
                    ->chunk(50);

                foreach ($responses as $questions) {
                    foreach ($questions as $questionKey => $question) {
                        $optionsWithDetails = [];
                        $options            = [0 => "", 1 => "", 2 => "", 3 => "", 4 => "", 5 => "", 6 => ""];
                        foreach ($question->question->questionoptions as $optionKey => $option) {
                            $options[$optionKey]             = $option->choice;
                            $optionsWithDetails[$option->id] = [
                                'choice' => $option->choice,
                                'score'  => $option->score,
                                'key'    => ($optionKey + 1),
                            ];
                        }

                        $records = array_merge([
                            Carbon::parse($question->created_at)->format("Y-m-d"),
                            $question->surveyLog->survey->title,
                            $question->surveyLog->survey_id,
                            $question->company->name,
                            $question->company->id,
                            $question->department->name,
                            ($time + $question->user_id),
                            $question->category->display_name,
                            $question->subcategory->display_name,
                            $question->question->questiontype->display_name,
                            $question->question->id,
                            $question->question->title,
                            $optionsWithDetails[$question->option_id]['key'],
                            $optionsWithDetails[$question->option_id]['choice'],
                        ], $options, [
                            "" . $optionsWithDetails[$question->option_id]['score'] . "",
                        ]);
                        $sheet->fromArray($records, null, 'A' . ($questionKey + 2));
                    }
                }
            }

            $writer    = new Xlsx($spreadsheet);
            $temp_file = tempnam(sys_get_temp_dir(), $fileName);
            $writer->save($temp_file);

            $uploaded = uploadFileToSpaces(file_get_contents($temp_file), "{$root}/{$foldername}{$dateString}/{$fileName}", "public");
            if (null != $uploaded && is_string($uploaded->get('ObjectURL'))) {
                $this->payload['spaceUrl']    = $uploaded->get('ObjectURL');
                $this->payload['fileName']    = $fileName;
                $this->payload['companyName'] = $this->company->name;
                $uploaded                     = true;
            }

            if ($this->payload['type'] == "masterclass") {
                $logRecord = $this->company->mcSurveyReportExportLogs()->create([
                    'user_id'            => $this->user->id,
                    'status'             => '1',
                    'process_started_at' => now(config('app.timezone'))->toDateTimeString(),
                ]);

                if ($logRecord) {
                    event(new SendMcSurveyReportExportEvent($logRecord, $this->payload));
                }
            } elseif ($this->payload['type'] == "zcsurvey") {
                $logRecord = $this->company->surveyExportLogs()->create([
                    'user_id'            => $this->user->id,
                    'status'             => '1',
                    'process_started_at' => now(config('app.timezone'))->toDateTimeString(),
                ]);

                if ($logRecord) {
                    event(new SendZcSurveyReportExportEvent($logRecord, $this->payload));
                }
            }
        } catch (\Exception $exception) {
            \Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
