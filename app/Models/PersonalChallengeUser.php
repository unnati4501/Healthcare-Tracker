<?php

namespace App\Models;

use App\Jobs\SendPersonalChallengePushNotification;
use App\Models\Notification;
use App\Models\NotificationSetting;
use App\Models\PersonalChallenge;
use App\Models\PersonalChallengeUser;
use App\Models\PersonalChallengeUserTask;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PersonalChallengeUser extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'personal_challenge_users';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'personal_challenge_id',
        'user_id',
        'joined',
        'start_date',
        'end_date',
        'reminder_at',
        'frequency_type',
        'from_time',
        'to_time',
        'in_every',
        'completed',
        'is_winner',
        'recursive_count',
        'recursive_completed',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'start_date',
        'end_date',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * "belongs to" relation to `personal_challenges` table
     * via `personal_challenge_id` field.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function personalChallenge()
    {
        return $this->belongsTo(\App\Models\PersonalChallenge::class, 'personal_challenge_id');
    }

    /**
     * "belongs to" relation to `users` table
     * via `user_id` field.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * "has many" relation to `personal_challenge_user_tasks` table
     * via `personal_challenge_user_id` field.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function personalChallengeUserTasks(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\PersonalChallengeTask', 'personal_challenge_user_tasks', 'personal_challenge_user_id', 'personal_challenge_tasks_id')
            ->withPivot('personal_challenge_id', 'date', 'completed')
            ->withTimestamps();
    }

    /**
     * Send finish reminder notification to user for personal challenge
     */
    public function sendFinishReminderNotification()
    {
        $user = User::find($this->user_id);

        if (Carbon::parse($this->end_date)->setTimezone($user->timezone)->toDateTimeString() > Carbon::now()->setTimezone($user->timezone)->toDateTimeString()) {
            return;
        }

        if ($this->recursive_count != 0 && $this->recursive_count > $this->recursive_completed) {
            $personalChallenge = $this->personalChallenge()->first();
            $lastEndDate       = Carbon::parse($this->end_date)->toDateString();
            $startDate         = Carbon::parse($lastEndDate, $user->timezone)->addDay()->setTime(0, 0, 0)->setTimeZone(config('app.timezone'))->toDateTimeString();
            $endDate           = Carbon::parse($startDate)->addDays($personalChallenge->duration)->toDateTimeString();

            $personalChallengeUserInput = [
                'personal_challenge_id' => $personalChallenge->id,
                'joined'                => 1,
                'start_date'            => $startDate,
                'end_date'              => $endDate,
                'reminder_at'           => $this->reminder_at,
                'completed'             => 0,
                'recursive_count'       => $this->recursive_count,
                'recursive_completed'   => $this->recursive_completed + 1,
            ];

            $personalChallenge->personalChallengeUsers()->attach($user->id, $personalChallengeUserInput);

            $userPivotId = PersonalChallengeUser::where('user_id', $user->id)
                ->where('personal_challenge_id', $personalChallenge->id)
                ->orderBy('id', 'DESC')
                ->pluck('id')
                ->first();

            $personalChallengeUserTaskInput = [];
            if ($personalChallenge->type == 'streak') {
                $taskData = $personalChallenge->personalChallengeTasks()->first();

                for ($i = 0; $i < $personalChallenge->duration; $i++) {
                    $date                             = Carbon::parse($startDate)->addDays($i)->toDateTimeString();
                    $personalChallengeUserTaskInput[] = [
                        'personal_challenge_id'       => $taskData->personal_challenge_id,
                        'personal_challenge_user_id'  => $userPivotId,
                        'personal_challenge_tasks_id' => $taskData->id,
                        'date'                        => $date,
                    ];
                }
            } else {
                $taskData = $personalChallenge->personalChallengeTasks()->get();

                $taskData->each(function ($item) use (&$personalChallengeUserTaskInput, $userPivotId) {
                    $personalChallengeUserTaskInput[] = [
                        'personal_challenge_id'       => $item->personal_challenge_id,
                        'personal_challenge_user_id'  => $userPivotId,
                        'personal_challenge_tasks_id' => $item->id,
                    ];
                });
            }

            if (!empty($personalChallengeUserTaskInput)) {
                PersonalChallengeUserTask::insert($personalChallengeUserTaskInput);
            }

            if (!empty($userPivotId)) {
                if (Carbon::now()->setTimezone($user->timezone)->toDateTimeString() < $startDate) {
                    $this->setStartReminder($personalChallenge, $startDate, $userPivotId);
                }
                $endDateTz = Carbon::parse($endDate, config('app.timezone'))->setTimezone($user->timezone)->toDateTimeString();
                $this->setEndReminder($personalChallenge, $endDateTz, $userPivotId);
            }
        }

        $userNotification = NotificationSetting::select('flag')
            ->where(['flag' => 1, 'user_id' => $user->getKey()])
            ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['challenges', 'all'])
            ->first();

        $personalChallenge = $this->personalChallenge()->first();

        $this->update(['completed' => 1]);

        // dispatch job to send push notification to app user when challenge get finished
        \dispatch(new SendPersonalChallengePushNotification($personalChallenge, "challenge-finished", $user, [
            'push'       => ($userNotification->flag ?? false),
            'mapping_id' => $this->id,
        ]));

        $winner = false;
        if ($personalChallenge->challenge_type == 'routine' || $personalChallenge->challenge_type == 'habit') {
            $userTasks          = $this->personalChallengeUserTasks()->get()->count();
            $userCompletedTasks = $this->personalChallengeUserTasks()->wherePivot('completed', 1)->get()->count();

            if ($userTasks === $userCompletedTasks) {
                $winner = true;
            }
        } else {
            $challengeStartDate = Carbon::parse($this->start_date, config('app.timezone'))->setTimezone($user->timezone);
            $challengeEndDate   = Carbon::parse($this->end_date, config('app.timezone'))->setTimezone($user->timezone);
            $daysRange          = \createDateRange($challengeStartDate, $challengeEndDate->subSecond());
            $target             = $personalChallenge->target_value;
            $completedTarget    = 0;

            foreach ($daysRange as $day) {
                if ($personalChallenge->type == 'steps') {
                    $value = (int) $user->getSteps($day->toDateString(), config('app.timezone'), $user->timezone);
                } elseif ($personalChallenge->type == 'distance') {
                    $value = (int) $user->getDistance($day->toDateString(), config('app.timezone'), $user->timezone);
                } else {
                    $value = (int) $user->getMeditation($day->toDateString(), config('app.timezone'), $user->timezone);
                }
                $completedTarget += $value;
            }

            if ($completedTarget >= $target) {
                $winner = true;
            }
        }

        if ($winner) {
            $badgeData = Badge::where("challenge_type_slug", "personal")->first();

            $badgeInput = [
                'status'     => "Active",
                'model_id'   => $personalChallenge->id,
                'model_name' => 'personal_challenge',
                'level'      => 0,
            ];
            $badgeData->badgeusers()->attach($user->id, $badgeInput);

            $this->update(['is_winner' => 1]);

            // dispatch job to send push notification to user when he/she completed all the tasks
            \dispatch(new SendPersonalChallengePushNotification($personalChallenge, "challenge-won", $user, [
                'push'       => ($userNotification->flag ?? false),
                'mapping_id' => $this->id,
            ]));
        }
    }

    /**
     * Send daily reminder notification to user for personal challenge
     */
    public function sendDailyReminderNotification()
    {
        $user = User::find($this->user_id);

        if (Carbon::now()->setTimezone($user->timezone)->toDateString() < Carbon::parse($this->start_date)->setTimezone($user->timezone)->toDateString()) {
            return;
        }

        $personalChallenge = $this->personalChallenge()->first();

        if (($personalChallenge->type == 'streak' || $personalChallenge->challenge_type == 'habit') && (Carbon::now()->setTimezone($user->timezone)->format('H:i') == '09:00')) {
            $subDayUserTasks          = $this->personalChallengeUserTasks()->wherePivot('date', Carbon::today()->subDay())->get()->count();
            $subDayUserCompletedTasks = $this->personalChallengeUserTasks()->wherePivot('date', Carbon::today()->subDay())->wherePivot('completed', 1)->get()->count();

            if ($subDayUserTasks !== $subDayUserCompletedTasks) {
                // dispatch job to send push notification to app user for reminder to tick off yesterday's task
                \dispatch(new SendPersonalChallengePushNotification($personalChallenge, "yesterday-reminder", $user, [
                    'mapping_id' => $this->id,
                ]));
            }
        }

        if (Carbon::parse($this->reminder_at)->format('H:i') != Carbon::now()->setTimezone($user->timezone)->format('H:i')) {
            return;
        }

        if ($personalChallenge->challenge_type == 'routine') {
            if ($personalChallenge->type == 'to-do') {
                $userTasks          = $this->personalChallengeUserTasks()->get()->count();
                $userCompletedTasks = $this->personalChallengeUserTasks()->wherePivot('completed', 1)->get()->count();
            } else {
                $userTasks          = $this->personalChallengeUserTasks()->wherePivot('date', Carbon::today())->get()->count();
                $userCompletedTasks = $this->personalChallengeUserTasks()->wherePivot('date', Carbon::today())->wherePivot('completed', 1)->get()->count();
            }

            if ($userTasks !== $userCompletedTasks) {
                // dispatch job to send push notification to app user for reminder to tick off task
                \dispatch(new SendPersonalChallengePushNotification($personalChallenge, "reminder", $user, [
                    'mapping_id' => $this->id,
                ]));
            }
        } elseif ($personalChallenge->challenge_type == 'challenge') {
            $challengeStartDate = Carbon::parse($this->start_date, config('app.timezone'))->setTimezone($user->timezone);
            $challengeEndDate   = Carbon::parse($this->end_date, config('app.timezone'))->setTimezone($user->timezone);
            $daysRange          = \createDateRange($challengeStartDate, $challengeEndDate->subSecond());
            $target             = $personalChallenge->target_value;
            $completedTarget    = 0;

            foreach ($daysRange as $day) {
                if ($personalChallenge->type == 'steps') {
                    $value = (int) $user->getSteps($day->toDateString(), config('app.timezone'), $user->timezone);
                } elseif ($personalChallenge->type == 'distance') {
                    $value = (int) $user->getDistance($day->toDateString(), config('app.timezone'), $user->timezone);
                } else {
                    $value = (int) $user->getMeditation($day->toDateString(), config('app.timezone'), $user->timezone);
                }
                $completedTarget += $value;
            }

            if ($completedTarget <= $target) {
                // dispatch job to send push notification to app user for reminder to tick off task
                \dispatch(new SendPersonalChallengePushNotification($personalChallenge, "pfc-reminder", $user, [
                    'mapping_id' => $this->id,
                ]));
            }
        }
    }

    /**
     * @param PersonalChallenge $personalChallenge, $startDate
     * @return void
     */
    protected function setStartReminder(PersonalChallenge $personalChallenge, $startDate, $personalChallengeMappingId): void
    {
        $user = User::find($this->user_id);
        $date = Carbon::parse($startDate)->subHours(24)->toDateTimeString();
        $time = Carbon::parse($date, $user->timezone)
            ->setTimezone(config('app.timezone'))
            ->todatetimeString();
        $userNotification = NotificationSetting::select('flag')
            ->where(['flag' => 1, 'user_id' => $user->getKey()])
            ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['challenges', 'all'])
            ->first();

        // dispatch job to send push notification to app user when challenge start reminder
        \dispatch(new SendPersonalChallengePushNotification($personalChallenge, "challenge-start", $user, [
            'type'         => 'Manual',
            'scheduled_at' => $time,
            'push'         => ($userNotification->flag ?? false),
            'mapping_id'   => $personalChallengeMappingId,
        ]));
    }

    /**
     * @param PersonalChallenge $personalChallenge, $endDate
     * @return void
     */
    protected function setEndReminder(PersonalChallenge $personalChallenge, $endDate, $personalChallengeMappingId): void
    {
        $user = User::find($this->user_id);
        $date = Carbon::parse($endDate)->subHours(12)->toDateTimeString();
        $time = Carbon::parse($date, $user->timezone)
            ->setTimezone(config('app.timezone'))
            ->todatetimeString();
        $userNotification = NotificationSetting::select('flag')
            ->where(['flag' => 1, 'user_id' => $user->getKey()])
            ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['challenges', 'all'])
            ->first();
        $notification_end_date = Carbon::parse($endDate, $user->timezone)
            ->format(config('zevolifesettings.date_format.default_datetime'));

        // dispatch job to send push notification to app user for challenge end reminder
        \dispatch(new SendPersonalChallengePushNotification($personalChallenge, "challenge-end", $user, [
            'type'         => 'Manual',
            'scheduled_at' => $time,
            'push'         => ($userNotification->flag ?? false),
            'mapping_id'   => $personalChallengeMappingId,
            'end_date'     => $notification_end_date,
        ]));
    }

    /**
     * Send daily habit plan reminder notification to user for personal challenge
     */
    public function sendDailyHabitPlanReminderNotification()
    {
        $user              = User::find($this->user_id);
        $personalChallenge = $this->personalChallenge()->first();

        if ($this->frequency_type == 'daily') {
            if (Carbon::parse($this->reminder_at)->format('H:i') != Carbon::now()->setTimezone($user->timezone)->format('H:i')) {
                return;
            }
            $userTasks          = $this->personalChallengeUserTasks()->get()->count();
            $userCompletedTasks = $this->personalChallengeUserTasks()->wherePivot('completed', 1)->get()->count();

            if ($userTasks !== $userCompletedTasks) {
                // dispatch job to send push notification to app user for reminder to tick off task
                \dispatch(new SendPersonalChallengePushNotification($personalChallenge, "reminder", $user, [
                    'mapping_id' => $this->id,
                ]));
            }
        } else {
            $currentTime = Carbon::now()->setTimezone($user->timezone)->format('H:i');
            $startTime   = Carbon::parse($this->from_time)->format('H:i');
            $toTime      = Carbon::parse($this->to_time)->format('H:i');

            if ($startTime <= $currentTime && $toTime >= $currentTime) {
                $reminderTime = ($this->reminder_at != null) ? Carbon::parse($this->reminder_at)->format('H:i') : $startTime;

                if ($reminderTime == $currentTime) {
                    // dispatch job to send push notification to app user for reminder to tick off task
                    \dispatch(new SendPersonalChallengePushNotification($personalChallenge, "reminder", $user, [
                        'mapping_id' => $this->id,
                    ]));

                    $nextReminder = Carbon::parse($reminderTime)->addSeconds($this->in_every)->format('H:i');

                    if ($nextReminder <= $toTime) {
                        PersonalChallengeUser::where('id', $this->id)
                            ->update(['reminder_at' => $nextReminder]);
                    } else {
                        PersonalChallengeUser::where('id', $this->id)
                            ->update(['reminder_at' => null]);
                    }
                }
            }
        }
    }
}
