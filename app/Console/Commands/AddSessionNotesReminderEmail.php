<?php

namespace App\Console\Commands;

use App\Models\CronofySchedule;
use Illuminate\Console\Command;
use App\Events\SendSessionNotesReminderEvent;
use App\Models\User;
use Carbon\Carbon;

class AddSessionNotesReminderEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'session:sessionnotesreminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will send email to WBS after 24 hours of session completed.';

    /**
     * Cronofy Schedule model object
     *
     * @var CronofySchedule $cronofySchedule
     */
    protected $cronofySchedule;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(CronofySchedule $cronofySchedule)
    {
        parent::__construct();
        $this->cronofySchedule = $cronofySchedule;
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
            $appTimezone       = config('app.timezone');
            $now               = now($appTimezone)->toDateTimeString();

            $cronofyCompletedSessions = $this->cronofySchedule
                ->leftJoin('ws_client_notes', 'ws_client_notes.cronofy_schedule_id', '=', 'cronofy_schedule.id')
                ->leftJoin('users as ws', 'cronofy_schedule.ws_id', '=', 'ws.id')
                ->leftJoin('users as u', 'cronofy_schedule.user_id', '=', 'u.id')
                ->select(
                    'cronofy_schedule.id',
                    'cronofy_schedule.start_time',
                    'cronofy_schedule.company_id',
                    'ws.first_name as wsFirstName',
                    'ws.email as wsEmail',
                    'ws.timezone as timezone',
                    'u.email as userEmail',
                    'cronofy_schedule.user_id')
                ->where('cronofy_schedule.status', 'booked')
                ->whereRaw("DATE_ADD(cronofy_schedule.end_time, INTERVAL '24' HOUR) <= ?", $now)
                ->where('cronofy_schedule.is_group', false)
                ->where('cronofy_schedule.is_reminder_sent', false)
                ->whereNull('ws_client_notes.comment')
                ->where('cronofy_schedule.notes','')
                ->get();

                if (!empty($cronofyCompletedSessions)) {
                foreach ($cronofyCompletedSessions as $sessionDetailsValue) {
                    $sessionDate    = Carbon::parse($sessionDetailsValue->start_time, $appTimezone)->setTimezone($sessionDetailsValue->timezone)->format('d/m/Y');
                    $addNotesUrl    = route('admin.cronofy.sessions.show', $sessionDetailsValue->id);
                    $sessionData = [
                        'sessionId'  => (!empty($sessionDetailsValue->id) ? $sessionDetailsValue->id : ""),
                        'sessionDate'=> $sessionDate,
                        'company'    => (!empty($sessionDetailsValue->company_id) ? $sessionDetailsValue->company_id : ""),
                        'addNotesUrl'=> $addNotesUrl,
                        'email'      => $sessionDetailsValue->wsEmail,
                        'wsName'     => $sessionDetailsValue->wsFirstName,
                        'userEmail'  => $sessionDetailsValue->userEmail,
                    ];
                    event(new SendSessionNotesReminderEvent($sessionData));
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
