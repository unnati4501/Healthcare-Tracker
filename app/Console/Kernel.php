<?php

namespace App\Console;

use App\Console\Commands\BroadcastMessageToGroup;
use App\Console\Commands\CancelChallengeIfNoParticipants;
use App\Console\Commands\ChallengeAutoCreationRecurringLogic;
use App\Console\Commands\ChangeBadgeStatus;
use App\Console\Commands\ChangeCompanyStatus;
use App\Console\Commands\CheckTargetForFirstToXChallenge;
use App\Console\Commands\ClearLogs;
use App\Console\Commands\CompaniesUpdatestatus;
use App\Console\Commands\DeleteOldNotifications;
use App\Console\Commands\DumpDatabase;
use App\Console\Commands\ExecuteChallengePointCalculationSPNew;
use App\Console\Commands\ExecuteImports;
use App\Console\Commands\ExpireChallengeBadges;
use App\Console\Commands\FreezeChallengeHistoryAndMarkAsFinished;
use App\Console\Commands\FreezeDataForRunningChallenge;
use App\Console\Commands\FreezeDataForStartedChallenge;
use App\Console\Commands\MarkEventAsCompleted;
use App\Console\Commands\SendBirthDayNotificationsToUser;
use App\Console\Commands\SendDailyReminderPersonalChallenge;
use App\Console\Commands\SendEventCsatNotification;
use App\Console\Commands\SendEventReminderNotification;
use App\Console\Commands\SendFeedPublishPush;
use App\Console\Commands\SendFinishReminderNotification;
use App\Console\Commands\SendFinishReminderPersonalChallenge;
use App\Console\Commands\SendMoodCron;
use App\Console\Commands\SendNotification;
use App\Console\Commands\SendNpsUserNotification;
use App\Console\Commands\SendProjectSurveyToCompanyUser;
use App\Console\Commands\SendSetProfilePictureNotificationToUsers;
use App\Console\Commands\SendStartReminderNotification;
use App\Console\Commands\SendZcSurveNotificationToCompanyUser;
use App\Console\Commands\SystemGeneratedChallengeGroups;
use App\Console\Commands\UpdateUsersAge;
use App\Console\Commands\UserFindAvgSteps;
use App\Console\Commands\UserSyncTracker;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        DumpDatabase::class,
        SendStartReminderNotification::class,
        ExecuteImports::class,
        SendNotification::class,
        ChangeCompanyStatus::class,
        ChangeBadgeStatus::class,
        DeleteOldNotifications::class,
        CancelChallengeIfNoParticipants::class,
        FreezeChallengeHistoryAndMarkAsFinished::class,
        ChallengeAutoCreationRecurringLogic::class,
        CheckTargetForFirstToXChallenge::class,
        SendFinishReminderNotification::class,
        SendBirthDayNotificationsToUser::class,
        UserSyncTracker::class,
        SendFeedPublishPush::class,
        SendNpsUserNotification::class,
        ExpireChallengeBadges::class,
        ClearLogs::class,
        FreezeDataForStartedChallenge::class,
        FreezeDataForRunningChallenge::class,
        UpdateUsersAge::class,
        SendDailyReminderPersonalChallenge::class,
        SendFinishReminderPersonalChallenge::class,
        SendMoodCron::class,
        SystemGeneratedChallengeGroups::class,
        SendZcSurveNotificationToCompanyUser::class,
        SendProjectSurveyToCompanyUser::class,
        SendSetProfilePictureNotificationToUsers::class,
        MarkEventAsCompleted::class,
        SendEventReminderNotification::class,
        SendEventCsatNotification::class,
        BroadcastMessageToGroup::class,
        CompaniesUpdatestatus::class,
        UserFindAvgSteps::class,
        ExecuteChallengePointCalculationSPNew::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        /*
        |--------------------------------------------------------------------------
        | Every Minute Crons
        |--------------------------------------------------------------------------
        |
         */
        // cron for daily reminder of personal challenges
        $schedule->command('personalchallenge:dailyreminder')->everyMinute()->withoutOverlapping()->runInBackground();

        // cron to finish challenge and freeze data and send notification of it and winner for First to reach type of challenge
        $schedule->command('challenge:firsttox')->everyMinute()->withoutOverlapping()->runInBackground();

        /*
        |--------------------------------------------------------------------------
        | Every Five Minute Crons
        |--------------------------------------------------------------------------
        |
         */
        // cron runs every five minute to send notifications scheduled from backend
        $schedule->command('notification:send')->everyFiveMinutes()->withoutOverlapping()->runInBackground();

        // // cron to finish challenge and freeze data and send notification of it and winner
        $schedule->command('challenge:freezeandfinish')->everyFiveMinutes()->withoutOverlapping()->runInBackground();

        // // cron for finish reminder of personal challenges
        $schedule->command('personalchallenge:finishreminder')->everyFiveMinutes()->withoutOverlapping()->runInBackground();

        /*
        |--------------------------------------------------------------------------
        | Every Ten Minute Crons
        |--------------------------------------------------------------------------
        |
         */
        // cron for sending feed publish notifications
        $schedule->command('feed:sendpublishpush')->everyTenMinutes()->withoutOverlapping()->runInBackground();

        // cron runs every 10 min to import user data using user import functionality
        $schedule->command('fileimport:executeimports')->everyTenMinutes()->runInBackground();

        // cron runs to cancel challenge if there are less than 2 participants.
        $schedule->command('challenge:noparticipants')->everyTenMinutes()->runInBackground();

        // Cron runs every 5 minutes to calculate challenge data from SP
        $schedule->command('execute:challengeSPNew')->everyFiveMinutes()->runInBackground();

        /*
        |--------------------------------------------------------------------------
        | Every Fifteen Minute Crons
        |--------------------------------------------------------------------------
        |
         */
        // cron runs to send company survey notifications to company users.
        $schedule->command('zcsurvey:userrollout')->everyFifteenMinutes()->runInBackground();

        // cron runs to send scheduled broadcast to group.
        $schedule->command('group:broadcast')->everyFifteenMinutes()->runInBackground();

        /*
        |--------------------------------------------------------------------------
        | Every Thirty Minute Crons
        |--------------------------------------------------------------------------
        |
         */
        // cron for sending feed publish notifications
        $schedule->command('system:groups')->everyThirtyMinutes()->runInBackground();

        // cron for send project survey notifications to company users
        $schedule->command('projectsurvey:userrollout')->everyThirtyMinutes()->runInBackground();

        // cron runs to mark event as completed once date and time is passed
        $schedule->command('event:markcompleted')->everyThirtyMinutes()->runInBackground();

        // cron for send notification to registered users before 12 hours and 1 hour of event start time
        $schedule->command('event:reminder')->everyThirtyMinutes()->runInBackground()->withoutOverlapping();

        // cron for send Event CSAT notification to registered users after 12 hours of event get completed.
        $schedule->command('event:csat')->everyThirtyMinutes()->runInBackground()->withoutOverlapping();

        // Cron to clear cron logs table entries
        $schedule->command('clear:logs')->twiceDaily(1, 13)->runInBackground()->withoutOverlapping();

        // cron runs every four hour to send start reminder of challenges
        $schedule->command('challenge:startreminder')->cron('0 */4 * * *')->runInBackground();

        // cron runs every four hour to send finish reminder of challenges
        $schedule->command('challenge:finishreminder')->cron('0 */4 * * *')->runInBackground();

        /*
        |--------------------------------------------------------------------------
        | Daily Crons. Runs at midnight
        |--------------------------------------------------------------------------
        |
         */
        // cron runs daily at midnight and dumps the copy of database
        $schedule->command('mysql:backup')->daily();

        // change status to inactive if company subscription end date is gone.
        $schedule->command('company:inactive')->daily();

        // delete notifications older than 7 days.
        $schedule->command('delete:notifications')->daily();

        // cron to generate recurring challenge
        $schedule->command('challenge:recurringchallengeautocreation')->daily();

        // Cron to send birthday wish to user as a push notification.
        $schedule->command('user:birthday')->daily();

        // Cron to send send sync tracker push notification to user
        $schedule->command('user:synctracker')->daily();

        // cron runs daily to send nps notification
        $schedule->command('user:npsfeedback')->daily();

        // // Cron to update age of each users based on date of birth
        $schedule->command('user:updateage')->daily();

        // // Cron to send mood cron daily.
        $schedule->command('send:moodSurvey')->daily();

        // // Cron to send push notification to users after 7 days of registration to set profile picture
        $schedule->command('user:setprofilepicture')->daily();

        // // Cron for company status update and update 'updated_at' records for companies list ordering
        $schedule->command('companies:updatestatus')->daily();

        // // cron for reminder of eap completed
        $schedule->command('eap:complete')->everyThirtyMinutes()->runInBackground()->withoutOverlapping();

        // // cron for daily reminder of personal challenges habit plan
        $schedule->command('habitpersonalchallenge:dailyreminder')->everyMinute()->withoutOverlapping()->runInBackground();

        // // Cron job will be run daily besis and find latest 15 days avg steps
        $schedule->command('user:findavgsteps')->daily()->runInBackground();

        // // cron runs to send session reminder notifications to company users.
        $schedule->command('cronofy:sessionstartreminder')->everyMinute()->withoutOverlapping()->runInBackground();

        // // cron for send reminder to wellbeing specialists for adding notes after session get completed.
        $schedule->command('session:sessionnotesreminder')->everyThirtyMinutes()->runInBackground()->withoutOverlapping();

        // // cron runs to remove sticky flag once feed expired
        $schedule->command('feed:removestickyflag')->everyMinute()->runInBackground()->withoutOverlapping();
        
        // // cron runs to update tags for all contetnts masterclass, meditation, feed, podcast, webinar, recipe
        $schedule->command('content:updatetags')->twiceDaily(1, 13)->runInBackground()->withoutOverlapping();

        // // Update events status which are in pending status and send notification to app/portal users for the registration
        $schedule->command('event:booked')->everyThirtyMinutes()->runInBackground()->withoutOverlapping();

        // // Remove the expired custom leaves and update availability status to available
        $schedule->command('user:removecustomleaves')->daily()->runInBackground()->withoutOverlapping();

        // clear telescope entries
        switch (app()->environment()) {
            case 'local':
            case 'dev':
            case 'qa':
                $schedule->command('telescope:prune --hours=24')->hourly();
                break;
            case 'uat':
            case 'preprod':
            case 'performance':
            case 'production':
                $schedule->command('telescope:prune --hours=24')->daily();
                break;
            default:
                break;
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}