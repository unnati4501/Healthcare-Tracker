<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\User;
use App\Models\CronofySchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Events\ClientUserNotesExportEvent;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use DB;

class ExportClientUserNotesReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $targetType;
    public $payload;
    public $user;
    public $columnName;
    public $cronofySchedule;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($payload, User $user, CronofySchedule $cronofySchedule)
    {
        $this->queue                  = 'mail';
        $this->payload                = $payload;
        $this->user                   = $user;
        $this->cronofySchedule        = $cronofySchedule;
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
            $timezone    = (!empty($this->user->timezone) ? $this->user->timezone : $appTimezone);
            
            $sheetTitle = [
                'User Name',
                'User Email',
                'Wellbeing Specialist Name',
                'Wellbeing Specialist Email',
                'Date/Time',
                'Note'
            ];
            $schedule = $this->cronofySchedule;
            if ($this->payload['type'] == 'wsNotes') {
                $wsClientNotes  = \DB::table('ws_client_notes')
                ->leftJoin('users', 'ws_client_notes.user_id', '=', 'users.id')
                ->leftJoin('cronofy_schedule', 'ws_client_notes.cronofy_schedule_id', '=', 'cronofy_schedule.id')
                ->leftJoin('users as ws', 'cronofy_schedule.ws_id', '=', 'ws.id')
                ->select(
                    DB::raw("CONCAT(users.first_name, ' ', users.last_name) as fullName"),
                    'users.email',
                    DB::raw("CONCAT(ws.first_name, ' ', ws.last_name) as wsFullName"),
                    'ws.email as wsEmail',
                    'ws_client_notes.comment as notes',
                )->selectRaw(
                    "DATE_FORMAT(CONVERT_TZ(ws_client_notes.created_at, ?, ?), '%d/%m/%Y %H:%i') as created_at"
                ,[
                    $appTimezone,$timezone
                ])
                ->where(function ($query) use($schedule) {
                    $query->where('cronofy_schedule.user_id', $schedule->user_id)
                    ->orWhereRaw("ws_client_notes.cronofy_schedule_id IN (SELECT session_id FROM `session_group_users` WHERE `user_id` = ?)", [$schedule->user_id]);
                })
                ->whereNull('ws.deleted_at');
                $wsClientNotes = $wsClientNotes->get()->toArray();
                
                $scheduleNotes = \DB::table('cronofy_schedule')
                ->leftJoin('users', 'cronofy_schedule.user_id', '=', 'users.id')
                ->leftJoin('users as ws', 'cronofy_schedule.ws_id', '=', 'ws.id')
                ->select(
                    DB::raw("CONCAT(users.first_name, ' ', users.last_name) as fullName"),
                    'users.email',
                    DB::raw("CONCAT(ws.first_name, ' ', ws.last_name) as wsFullName"),
                    'ws.email as wsEmail',
                    \DB::raw("CONCAT(users.first_name, ' ', users.last_name) as notesAddedBy"),
                    'cronofy_schedule.notes as notes'
                )->selectRaw("DATE_FORMAT(CONVERT_TZ(cronofy_schedule.created_at, ?, ?), '%d/%m/%Y %H:%i') as created_at",[
                    $appTimezone,$timezone
                ])
                ->where(function ($query) use($schedule) {
                    $query->where('cronofy_schedule.user_id', $schedule->user_id)
                    ->orWhereRaw("cronofy_schedule.id IN (SELECT session_id FROM `session_group_users` WHERE `user_id` = ?)", [$schedule->user_id]);
                })
                ->whereNull('ws.deleted_at');
                
                $scheduleNotes = $scheduleNotes->where('cronofy_schedule.notes', '<>', '')
                ->get()
                ->toArray();

                $data = array_merge($wsClientNotes, $scheduleNotes);
                $dateTimeString = Carbon::now()->toDateTimeString();

                $fileName = "WS_notes_".$dateTimeString.'.xlsx';
                
            } else {
                // Old session user notes
                $oldSessionUserNotes       = \DB::table('session_user_notes')
                ->leftJoin('cronofy_schedule', 'cronofy_schedule.user_id', '=', 'session_user_notes.user_id')
                ->leftJoin('users', 'cronofy_schedule.user_id', '=', 'users.id')
                ->leftJoin('users as ws', 'cronofy_schedule.ws_id', '=', 'ws.id')
                ->select(
                    DB::raw("CONCAT(users.first_name, ' ', users.last_name) as fullName"),
                    'users.email',
                    DB::raw("CONCAT(ws.first_name, ' ', ws.last_name) as wsFullName"),
                    'ws.email as wsEmail',
                    "session_user_notes.notes as notes"
                )
                ->selectRaw("DATE_FORMAT(CONVERT_TZ(session_user_notes.created_at, ?, ?), '%d/%m/%Y %H:%i') as created_at",[
                    $appTimezone,$timezone
                ])
                ->where('session_user_notes.user_id', $this->cronofySchedule->user_id)
                ->where('cronofy_schedule.id', $this->cronofySchedule->id)
                ->whereNull('ws.deleted_at')
                ->get()
                ->toArray();
                
                // Get the session user notes
                $sessionUserNotes = \DB::table('cronofy_schedule')
                ->leftJoin('users', 'cronofy_schedule.user_id', '=', 'users.id')
                ->leftJoin('users as ws', 'cronofy_schedule.ws_id', '=', 'ws.id')
                ->select(
                    DB::raw("CONCAT(users.first_name, ' ', users.last_name) as fullName"),
                    'users.email',
                    DB::raw("CONCAT(ws.first_name, ' ', ws.last_name) as wsFullName"),
                    'ws.email as wsEmail',
                    'cronofy_schedule.user_notes as notes'
                )
                ->selectRaw("DATE_FORMAT(CONVERT_TZ(cronofy_schedule.created_at, ?, ?), '%d/%m/%Y %H:%i') as created_at",[
                    $appTimezone,$timezone
                ])
                ->where('cronofy_schedule.user_notes', '<>', '')
                ->where('cronofy_schedule.user_id', $this->cronofySchedule->user_id)
                ->whereNull('ws.deleted_at')
                ->get()
                ->toArray();
                $data = array_merge($oldSessionUserNotes, $sessionUserNotes);
                
                $dateTimeString = Carbon::now()->toDateTimeString();

                $fileName = "User_notes_".$dateTimeString.'.xlsx';
            }
            
            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();
            $sheet->fromArray($sheetTitle, null, 'A1');
            
            $records = json_decode(json_encode($data), true);
            if (sizeof($records) == 0) {
                $messageData = [
                    'data'   => trans('Cronofy.client_list.messages.no_data_exists'),
                    'status' => 0,
                ];
                return \Redirect::route('admin.cronofy.clientlist.details', $this->cronofySchedule->id)->with('message', $messageData);
            }
            $notes = [];
            foreach ($records as $value) {
                $wsClientUserNotes    =  htmlspecialchars_decode(strip_tags($value['notes']));
                $note['user_name']    = $value['fullName'];
                $note['user_email']   = $value['email'];
                $note['ws_name']      = $value['wsFullName'];
                $note['ws_email']     = $value['wsEmail'];
                $note['created_at']   = $value['created_at'];
                $note['notes']        = str_replace(array("\n", "\r"), ' ', $wsClientUserNotes);
                $notes[]              = $note;
            }

            $sheet->fromArray($notes, null, 'A2');
            
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

            event(new ClientUserNotesExportEvent($this->user, $url, $this->payload, $fileName));
            return true;
            
        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
