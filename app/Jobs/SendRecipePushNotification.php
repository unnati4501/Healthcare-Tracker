<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\Recipe;
use App\Models\User;
use App\Notifications\SystemAutoNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendRecipePushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Recipe
     */
    protected $recipe;
    protected $string;
    protected $users;
    protected $userName;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Recipe $recipe, $string, $users, $userName = '')
    {
        $this->queue    = 'notifications';
        $this->recipe   = $recipe;
        $this->string   = $string;
        $this->users    = $users;
        $this->userName = $userName;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->users = User::hydrate($this->users);

        $title         = '';
        $message       = '';
        $deep_link_uri = '';

        $isMobile = $isPortal = '';

        if ($this->string == 'community-recipe-added') {
            $title   = trans('notifications.recipe.added.title');
            $message = trans('notifications.recipe.added.message');

            $isMobile = config('notification.recipe.added.is_mobile');
            $isPortal = config('notification.recipe.added.is_portal');

            $deep_link_uri = $this->recipe->deep_link_uri;
        } elseif ($this->string == 'community-recipe-reaction') {
            $title   = trans('notifications.recipe.reaction.title');
            $message = trans('notifications.recipe.reaction.message');

            $isMobile = config('notification.recipe.reaction.is_mobile');
            $isPortal = config('notification.recipe.reaction.is_portal');

            $message       = str_replace(['#user_name#'], [$this->userName], $message);
            $deep_link_uri = $this->recipe->deep_link_uri;
        } elseif ($this->string == 'community-recipe-deleted') {
            $title   = trans('notifications.recipe.deleted.title');
            $message = trans('notifications.recipe.deleted.message');

            $isMobile = config('notification.recipe.deleted.is_mobile');
            $isPortal = config('notification.recipe.deleted.is_portal');

            $message       = str_replace(['#recipe_title#'], [$this->recipe->title], $message);
            $deep_link_uri = '';
        } elseif ($this->string == 'community-recipe-approved') {
            $title   = trans('notifications.recipe.approved.title');
            $message = trans('notifications.recipe.approved.message');

            $isMobile = config('notification.recipe.approved.is_mobile');
            $isPortal = config('notification.recipe.approved.is_portal');

            $message       = str_replace(['#recipe_title#'], [$this->recipe->title], $message);
            $deep_link_uri = $this->recipe->deep_link_uri;
        }

        if ($this->users->count() > 0) {
            if ($this->string == 'community-recipe-added') {
                $planAccess   = true;
                foreach ($this->users as $value) {
                    $company = $value->company()->first();
                    if($company->is_reseller || !is_null($company->parent_id)){
                        $planAccess = getCompanyPlanAccess($value, 'explore');
                    }
                    if($planAccess){
                        $message = trans('notifications.recipe.added.message', [
                            'first_name'   => $value->first_name,
                            'recipe_title' => $this->recipe->title,
                        ]);
                        $notificationData = [
                            'type'          => 'Auto',
                            'creator_id'    => $this->recipe->creator_id,
                            'title'         => $title,
                            'message'       => $message,
                            'push'          => true,
                            'scheduled_at'  => now()->toDateTimeString(),
                            'deep_link_uri' => $deep_link_uri,
                            'is_mobile'     => $isMobile,
                            'is_portal'     => $isPortal,
                            'tag'           => 'recipe',
                        ];
    
                        $notification = Notification::create($notificationData);
    
                        $value->notifications()->attach($notification, ['sent' => true, 'sent_on' => now()->toDateTimeString()]);
    
                        if ($value->notification_flag) {
                            // send notification to all users
                            \Notification::send(
                                $value,
                                new SystemAutoNotification($notification, $this->string)
                            );
                        }
                    }
                }
            } else {
                $notificationData = [
                    'type'          => 'Auto',
                    'creator_id'    => $this->recipe->creator_id,
                    'title'         => $title,
                    'message'       => $message,
                    'push'          => true,
                    'scheduled_at'  => now()->toDateTimeString(),
                    'deep_link_uri' => $deep_link_uri,
                    'is_mobile'     => $isMobile,
                    'is_portal'     => $isPortal,
                    'tag'           => 'recipe',
                ];

                $notification = Notification::create($notificationData);
                $planAccess   = true;
                foreach ($this->users as $value) {
                    $company = $value->company()->first();
                    if(!empty($company) && ($company->is_reseller || !is_null($company->parent_id))){
                        $planAccess = getCompanyPlanAccess($value, 'explore');
                    }
                    if($planAccess){
                        $value->notifications()->attach($notification, ['sent' => true, 'sent_on' => now()->toDateTimeString()]);
                        if ($value->notification_flag) {
                            \Notification::send(
                                $value,
                                new SystemAutoNotification($notification, $this->string)
                            );
                        }
                    }
                }
            }
        }
    }
}
