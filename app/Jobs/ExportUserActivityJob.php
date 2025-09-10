<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\Company;
use App\Models\User;
use App\Models\EventBookingLogs;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Events\UserActivityExportEvent;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use DB;

class ExportUserActivityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $targetType;
    public $payload;
    public $user;
    public $columnName;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($payload, User $user)
    {
        $this->queue                  = 'mail';
        $this->payload                = $payload;
        $this->user                   = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $appTimezone = config('app.timezone');
            $timezone    = (!empty($this->user['timezone']) ? $this->user['timezone'] : $appTimezone);

            $queryStr    = json_decode($this->payload['queryString'], true);
            $startDate = $endDate = '';
            if ($this->payload['tab'] == 'steps') {
                if (empty($queryStr['stepTextSearch']) && (!empty($queryStr['dtFromdate']) || !empty($queryStr['dtTodate']))) {
                    $startDateSearch = !empty($queryStr['dtFromdate']) ? str_replace("%2F", "/", $queryStr['dtFromdate']) : $exportStartDate;
                    $endDateSearch = !empty($queryStr['dtTodate']) ? str_replace("%2F", "/", $queryStr['dtTodate']) : $exportEndDate;

                    if (!empty($startDateSearch)) {
                        $startDate = Carbon::parse($startDateSearch, $timezone)->setTime(0, 0, 0, 0)->setTimezone($appTimezone)->toDateTimeString();
                    }
                    if (!empty($endDateSearch)) {
                        $endDate   = Carbon::parse($endDateSearch, $timezone)->setTime(23, 59, 59, 0)->setTimezone($appTimezone)->toDateTimeString();
                    }
                } elseif (!empty($queryStr['stepTextSearch']) && (empty($queryStr['dtFromdate']) || empty($queryStr['dtTodate']))) {
                    $startDate = $endDate = '';
                } elseif (!empty($queryStr['stepTextSearch']) && (!empty($queryStr['dtFromdate']) || !empty($queryStr['dtTodate']))) {
                    $startDateSearch = !empty($queryStr['dtFromdate']) ? str_replace("%2F", "/", $queryStr['dtFromdate']) : $exportStartDate;
                    $endDateSearch = !empty($queryStr['dtTodate']) ? str_replace("%2F", "/", $queryStr['dtTodate']) : $exportEndDate;

                    if (!empty($startDateSearch)) {
                        $startDate = Carbon::parse($startDateSearch, $timezone)->setTime(0, 0, 0, 0)->setTimezone($appTimezone)->toDateTimeString();
                    }
                    if (!empty($endDateSearch)) {
                        $endDate   = Carbon::parse($endDateSearch, $timezone)->setTime(23, 59, 59, 0)->setTimezone($appTimezone)->toDateTimeString();
                    }
                } else {
                    $exportStartDate = !empty($this->payload['start_date']) ? $this->payload['start_date'] : '';
                    $exportEndDate = !empty($this->payload['end_date']) ? $this->payload['end_date'] : '';

                    if (!empty($exportStartDate)) {
                        $startDate = Carbon::parse($exportStartDate, $timezone)->setTime(0, 0, 0, 0)->setTimezone($appTimezone)->toDateTimeString();
                    }
                    if (!empty($exportEndDate)) {
                        $endDate   = Carbon::parse($exportEndDate, $timezone)->setTime(23, 59, 59, 0)->setTimezone($appTimezone)->toDateTimeString();
                    }
                }
                
                $userStepsData = DB::table('user_step')
                ->select(
                    DB::raw("CONCAT(users.first_name,' ',users.last_name) as fullName"),
                    'users.email',
                    'user_step.tracker',
                    'user_step.steps',
                    'user_step.distance',
                    'user_step.calories',
                    \DB::raw("DATE_FORMAT(user_step.log_date, '%b %d, %Y, %H:%i')"),
                    \DB::raw("DATE_FORMAT(user_step.updated_at, '%b %d, %Y, %H:%i')")
                )
                ->join("users", "users.id", "=", "user_step.user_id");
            
                if (!empty($queryStr['stepTextSearch'])) {
                    $stepsSearch = $queryStr['stepTextSearch'];
                    $userEmailPos = strpos($queryStr['stepTextSearch'], '%40');
                    $userFullNamePos = strpos($queryStr['stepTextSearch'], '%20');
                    if ($userEmailPos) {
                        $stepsSearch =  str_replace("%40", "@", $queryStr['stepTextSearch']);
                    } elseif ($userFullNamePos) {
                        $stepsSearch =  str_replace("%20", " ", $queryStr['stepTextSearch']);
                    }
                    $userStepsData->where(DB::raw("CONCAT(users.first_name,' ',users.last_name)"), 'like', '%' .$stepsSearch . '%')->orWhere('users.email', 'like', '%' . $stepsSearch . '%');
                }
                
                if (!empty($startDate) && !empty($endDate)) {
                    $userStepsData->whereBetween('user_step.log_date', [$startDate, $endDate]);
                } elseif (!empty($startDate) && empty($endDate)) {
                    $userStepsData->where('user_step.log_date', '>=', $startDate);
                } elseif (empty($startDate) && !empty($endDate)) {
                    $userStepsData->where('user_step.log_date', '<=', $endDate);
                }
                $userStepsData->orderBy('user_step.id', 'DESC');
                
                $userNPSDataRecords = $userStepsData->get()->chunk(100);
               
                if ($userStepsData->get()->count() == 0) {
                    $messageData = [
                        'data'   => trans('challenges.messages.export_error'),
                        'status' => 0,
                    ];
                    return \Redirect::route('admin.reports.users-activities')->with('message', $messageData);
                }
                $dateTimeString = Carbon::now()->toDateTimeString();

                $sheetTitle = [
                    'User Name',
                    'Email',
                    'Tracker',
                    'Steps',
                    'Distance',
                    'Calories',
                    'Log Date',
                    'Sync Date'
                ];
                $fileName = "User_steps_summary_".$this->user['full_name']."_".$dateTimeString.'.xlsx';
            } elseif ($this->payload['tab'] == 'exercise') {
                if (empty($queryStr['exercisesTextSearch']) && (!empty($queryStr['dtExerciseFromdate']) || !empty($queryStr['dtExerciseTodate']))) {
                    $startDateSearch = !empty($queryStr['dtExerciseFromdate']) ? str_replace("%2F", "/", $queryStr['dtExerciseFromdate']) : $exportStartDate;
                    $endDateSearch = !empty($queryStr['dtExerciseTodate']) ? str_replace("%2F", "/", $queryStr['dtExerciseTodate']) : $exportEndDate;

                    if (!empty($startDateSearch)) {
                        $startDate = Carbon::parse($startDateSearch, $timezone)->setTime(0, 0, 0, 0)->setTimezone($appTimezone)->toDateTimeString();
                    }
                    if (!empty($endDateSearch)) {
                        $endDate   = Carbon::parse($endDateSearch, $timezone)->setTime(23, 59, 59, 0)->setTimezone($appTimezone)->toDateTimeString();
                    }
                } elseif (!empty($queryStr['exercisesTextSearch']) && (empty($queryStr['dtExerciseFromdate']) || empty($queryStr['dtExerciseTodate']))) {
                    $startDate = $endDate = '';
                } elseif (!empty($queryStr['exercisesTextSearch']) && (!empty($queryStr['dtExerciseFromdate']) || !empty($queryStr['dtExerciseTodate']))) {
                    $startDateSearch = !empty($queryStr['dtExerciseFromdate']) ? str_replace("%2F", "/", $queryStr['dtExerciseFromdate']) : $exportStartDate;
                    $endDateSearch = !empty($queryStr['dtExerciseTodate']) ? str_replace("%2F", "/", $queryStr['dtExerciseTodate']) : $exportEndDate;

                    if (!empty($startDateSearch)) {
                        $startDate = Carbon::parse($startDateSearch, $timezone)->setTime(0, 0, 0, 0)->setTimezone($appTimezone)->toDateTimeString();
                    }
                    if (!empty($endDateSearch)) {
                        $endDate   = Carbon::parse($endDateSearch, $timezone)->setTime(23, 59, 59, 0)->setTimezone($appTimezone)->toDateTimeString();
                    }
                } else {
                    $exportStartDate = !empty($this->payload['start_date']) ? $this->payload['start_date'] : '';
                    $exportEndDate = !empty($this->payload['end_date']) ? $this->payload['end_date'] : '';

                    if (!empty($exportStartDate)) {
                        $startDate = Carbon::parse($exportStartDate, $timezone)->setTime(0, 0, 0, 0)->setTimezone($appTimezone)->toDateTimeString();
                    }
                    if (!empty($exportEndDate)) {
                        $endDate   = Carbon::parse($exportEndDate, $timezone)->setTime(23, 59, 59, 0)->setTimezone($appTimezone)->toDateTimeString();
                    }
                }

                $userExercisesData = DB::table('user_exercise')
                ->select(
                    DB::raw("CONCAT(users.first_name,' ',users.last_name) as fullName"),
                    'users.email',
                    'exercises.title',
                    'user_exercise.tracker',
                    'user_exercise.distance',
                    'user_exercise.calories',
                    'user_exercise.duration',
                    \DB::raw("CONCAT(DATE_FORMAT(user_exercise.start_date, '%b %d, %Y, %H:%i'),'-',DATE_FORMAT(user_exercise.end_date, '%b %d, %Y, %H:%i'))"),
                    \DB::raw("DATE_FORMAT(user_exercise.updated_at, '%b %d, %Y, %H:%i')")
                )
                ->join("users", "users.id", "=", "user_exercise.user_id")
                ->join("exercises", "exercises.id", "=", "user_exercise.exercise_id")
                ->whereNull("user_exercise.deleted_at");

                if (!empty($queryStr['exercisesTextSearch'])) {
                    $exerciseSearch = $queryStr['exercisesTextSearch'];
                    $userEmailPos = strpos($queryStr['exercisesTextSearch'], '%40');
                    $userFullNamePos = strpos($queryStr['exercisesTextSearch'], '%20');
                    if ($userEmailPos) {
                        $exerciseSearch =  str_replace("%40", "@", $queryStr['exercisesTextSearch']);
                    } elseif ($userFullNamePos) {
                        $exerciseSearch =  str_replace("%20", " ", $queryStr['exercisesTextSearch']);
                    }
                    $userExercisesData->where(DB::raw("CONCAT(users.first_name,' ',users.last_name)"), 'like', '%' .$exerciseSearch . '%')->orWhere('users.email', 'like', '%' . $exerciseSearch . '%');
                }

                if (!empty($startDate) && !empty($endDate)) {
                    $userExercisesData->whereBetween('user_exercise.start_date', [$startDate, $endDate]);
                } elseif (!empty($startDate) && empty($endDate)) {
                    $userExercisesData->where('user_exercise.start_date', '>=', $startDate);
                } elseif (empty($startDate) && !empty($endDate)) {
                    $userExercisesData->where('user_exercise.start_date', '<=', $endDate);
                }
                $userExercisesData->orderBy('user_exercise.id', 'DESC');
        
                $userNPSDataRecords = $userExercisesData->get()->chunk(100);
                $dateTimeString = Carbon::now()->toDateTimeString();

                $sheetTitle = [
                    'User Name',
                    'Email',
                    'Exercise',
                    'Tracker',
                    'Distance',
                    'Calories',
                    'Duration (Seconds)',
                    'Start/End Date',
                    'Sync Date'
                ];
                $fileName = "User_Exercise_summary_".$this->user['full_name']."_".$dateTimeString.'.xlsx';
            } else {
                if (empty($queryStr['meditationsTextSearch']) && (!empty($queryStr['dtMeditationFromdate']) || !empty($queryStr['dtMeditationTodate']))) {
                    $startDateSearch = !empty($queryStr['dtMeditationFromdate']) ? str_replace("%2F", "/", $queryStr['dtMeditationFromdate']) : $exportStartDate;
                    $endDateSearch = !empty($queryStr['dtMeditationTodate']) ? str_replace("%2F", "/", $queryStr['dtMeditationTodate']) : $exportEndDate;

                    if (!empty($startDateSearch)) {
                        $startDate = Carbon::parse($startDateSearch, $timezone)->setTime(0, 0, 0, 0)->setTimezone($appTimezone)->toDateTimeString();
                    }
                    if (!empty($endDateSearch)) {
                        $endDate   = Carbon::parse($endDateSearch, $timezone)->setTime(23, 59, 59, 0)->setTimezone($appTimezone)->toDateTimeString();
                    }
                } elseif (!empty($queryStr['meditationsTextSearch']) && (empty($queryStr['dtMeditationFromdate']) || empty($queryStr['dtMeditationTodate']))) {
                    $startDate = $endDate = '';
                } elseif (!empty($queryStr['meditationsTextSearch']) && (!empty($queryStr['dtMeditationFromdate']) || !empty($queryStr['dtMeditationTodate']))) {
                    $startDateSearch = !empty($queryStr['dtMeditationFromdate']) ? str_replace("%2F", "/", $queryStr['dtMeditationFromdate']) : $exportStartDate;
                    $endDateSearch = !empty($queryStr['dtMeditationTodate']) ? str_replace("%2F", "/", $queryStr['dtMeditationTodate']) : $exportEndDate;

                    if (!empty($startDateSearch)) {
                        $startDate = Carbon::parse($startDateSearch, $timezone)->setTime(0, 0, 0, 0)->setTimezone($appTimezone)->toDateTimeString();
                    }
                    if (!empty($endDateSearch)) {
                        $endDate   = Carbon::parse($endDateSearch, $timezone)->setTime(23, 59, 59, 0)->setTimezone($appTimezone)->toDateTimeString();
                    }
                } else {
                    $exportStartDate = !empty($this->payload['start_date']) ? $this->payload['start_date'] : '';
                    $exportEndDate = !empty($this->payload['end_date']) ? $this->payload['end_date'] : '';

                    if (!empty($exportStartDate)) {
                        $startDate = Carbon::parse($exportStartDate, $timezone)->setTime(0, 0, 0, 0)->setTimezone($appTimezone)->toDateTimeString();
                    }
                    if (!empty($exportEndDate)) {
                        $endDate   = Carbon::parse($exportEndDate, $timezone)->setTime(23, 59, 59, 0)->setTimezone($appTimezone)->toDateTimeString();
                    }
                }

                $userMeditationData = DB::table('user_listened_tracks')
                ->select(
                    DB::raw("CONCAT(users.first_name,' ',users.last_name) as fullName"),
                    'users.email',
                    'meditation_tracks.title',
                    'user_listened_tracks.duration_listened',
                    \DB::raw("DATE_FORMAT(user_listened_tracks.created_at, '%b %d, %Y, %H:%i')")
                )
                ->join("users", "users.id", "=", "user_listened_tracks.user_id")
                ->join("meditation_tracks", "meditation_tracks.id", "=", "user_listened_tracks.meditation_track_id");
               
                if (!empty($queryStr['meditationsTextSearch'])) {
                    $userMeditationData->where(DB::raw("CONCAT(users.first_name,' ',users.last_name)"), 'like', '%' . $queryStr['meditationsTextSearch'] . '%')->orWhere('users.email', 'like', '%' . $queryStr['meditationsTextSearch'] . '%');
                }

                if (!empty($queryStr['meditationsTextSearch'])) {
                    $meditationSearch = $queryStr['meditationsTextSearch'];
                    $userEmailPos = strpos($queryStr['meditationsTextSearch'], '%40');
                    $userFullNamePos = strpos($queryStr['meditationsTextSearch'], '%20');
                    if ($userEmailPos) {
                        $meditationSearch =  str_replace("%40", "@", $queryStr['meditationsTextSearch']);
                    } elseif ($userFullNamePos) {
                        $meditationSearch =  str_replace("%20", " ", $queryStr['meditationsTextSearch']);
                    }
                    $userMeditationData->where(DB::raw("CONCAT(users.first_name,' ',users.last_name)"), 'like', '%' .$meditationSearch . '%')->orWhere('users.email', 'like', '%' . $meditationSearch . '%');
                }

                if (!empty($startDate) && !empty($endDate)) {
                    $userMeditationData->whereBetween('user_listened_tracks.created_at', [$startDate, $endDate]);
                } elseif (!empty($startDate) && empty($endDate)) {
                    $userMeditationData->where('user_listened_tracks.created_at', '>=', $startDate);
                } elseif (empty($startDate) && !empty($endDate)) {
                    $userMeditationData->where('user_listened_tracks.created_at', '<=', $endDate);
                }
                
                $userMeditationData->orderBy('user_listened_tracks.id', 'DESC');
                $userNPSDataRecords = $userMeditationData->get()->chunk(100);
                
                $dateTimeString = Carbon::now()->toDateTimeString();
                
                $sheetTitle = [
                    'User Name',
                    'Email',
                    'Track Title',
                    'Duration',
                    'Log Date',
                ];
                $fileName = "User_Meditationtrack_summary_".$this->user['full_name']."_".$dateTimeString.'.xlsx';
            }
            
            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();
            $sheet->fromArray($sheetTitle, null, 'A1');
            
            $index = 2;
            foreach ($userNPSDataRecords as $value) {
                $records = (count($value) > 0) ? json_decode(json_encode($value), true) : [];
                $sheet->fromArray($records, null, 'A'.$index, true);
                $index = $index + count($value);
            }
            
            $writer = new Xlsx($spreadsheet);
            $temp_file = tempnam(sys_get_temp_dir(), $fileName);
            $source = fopen($temp_file, 'rb');
            $writer->save($temp_file);
            
            $root     = config("filesystems.disks.spaces.root");
            $foldername = config('zevolifesettings.excelfolderpath');

            $uploaded = uploadFileToSpaces($source, "{$root}/{$foldername}/{$fileName}", "public");
            if (null != $uploaded && is_string($uploaded->get('ObjectURL'))) {
                $url      = $uploaded->get('ObjectURL');
                $uploaded = true;
            }
            
            event(new UserActivityExportEvent($this->user, $url, $this->payload, $fileName));
            return true;
            
        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
