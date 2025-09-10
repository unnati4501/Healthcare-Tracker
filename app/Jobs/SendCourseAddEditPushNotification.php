<?php

namespace App\Jobs;

use App\Models\Company;
use App\Models\Course;
use App\Models\Notification;
use App\Models\User;
use App\Notifications\SystemAutoNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCourseAddEditPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Course object
     */
    protected $course;
    /**
     * @var String type of notification
     */
    protected $string;
    /**
     * @var associated company id's array
     */
    protected $associatedCompIds;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Course $course, $string, array $associatedCompIds = [])
    {
        $this->queue             = 'notifications';
        $this->course            = $course;
        $this->string            = $string;
        $this->associatedCompIds = $associatedCompIds;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $title        = "";
        $message      = "";
        $isMobile     = "";
        $isPortal     = "";
        $finalusers   = [];

        if ($this->string == "course-published") {
            $title = trans('notifications.masterclass.publish.title');

            $isMobile = config('notification.academy.new_masterclass.is_mobile');
            $isPortal = config('notification.academy.new_masterclass.is_portal');
            if (empty($this->associatedCompIds)) {
                $this->associatedCompIds = $this->course->masterclassteam->pluck('id')->toArray();
            }

            foreach ($this->associatedCompIds as $teamId) {
                $usersData = Company::select('users.id', 'users.first_name', 'user_notification_settings.flag AS notification_flag', 'companies.id AS companyid')
                    ->join('user_team', function ($join) {
                        $join->on('user_team.company_id', '=', 'companies.id');
                    })
                    ->join('users', function ($join) {
                        $join->on('users.id', '=', 'user_team.user_id');
                    })
                    ->leftJoin('user_notification_settings', function ($join) {
                        $join->on('user_notification_settings.user_id', '=', 'users.id')
                            ->where('user_notification_settings.flag', '=', 1)
                            ->whereRaw('(`user_notification_settings`.`module` = ? OR `user_notification_settings`.`module` = ?)', ['courses', 'all']);
                    })
                    ->where('user_team.team_id', $teamId)
                    ->where('is_blocked', false)
                    ->groupBy('users.id')
                    ->get();

                $finalusers[] = User::hydrate($usersData->toArray());
            }
        }

        if (sizeof($finalusers) > 0) {
            foreach ($finalusers as $users) {
                $planAccess = true;
                foreach ($users as $user) {
                    $company = $user->company()->first();
                    if($company->is_reseller || !is_null($company->parent_id)){
                        $planAccess = getCompanyPlanAccess($user, 'masterclass');
                    }
                    if($planAccess){
                        $message = trans('notifications.masterclass.publish.message', [
                            'first_name'       => $user->first_name,
                            'masterclass_name' => $this->course->title,
                        ]);
    
                        $notification = Notification::create([
                            'type'          => 'Auto',
                            'creator_id'    => $this->course->creator_id,
                            'company_id'    => $user->companyid,
                            'title'         => $title,
                            'message'       => $message,
                            'push'          => true,
                            'scheduled_at'  => now()->toDateTimeString(),
                            'deep_link_uri' => $this->course->deep_link_uri,
                            'is_mobile'     => $isMobile,
                            'is_portal'     => $isPortal,
                            'tag'           => 'masterclass',
                        ]);
                        $user->notifications()->attach($notification, ['sent' => true, 'sent_on' => now()->toDateTimeString()]);
    
                        if ($user->notification_flag) {
                            // send push notification to users
                            \Notification::send(
                                $user,
                                new SystemAutoNotification($notification, $this->string)
                            );
                        }
                    }
                }
            }
        }
    }
}
