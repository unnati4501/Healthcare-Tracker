<?php

namespace App\Jobs;

use App\Models\BroadcastMessage;
use App\Models\Notification;
use App\Notifications\SystemAutoNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class BroadcastMessageToGroup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * variable to store the model object
     * @var BroadcastMessage $message
     */
    protected $message;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(BroadcastMessage $message)
    {
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $appTimezone      = config('app.timezone');
            $now              = now($appTimezone)->toDateTimeString();
            $created_at       = ($this->message->type == 'scheduled' ? $this->message->scheduled_at : $now);
            $user             = $this->message->user()->select('users.id')->first();
            $group            = $this->message->group()->select('groups.id', 'groups.is_archived')->first();
            $checkGroupAccess = getCompanyPlanAccess($user, 'group');
            // set broadcast status to cancelled as group is deleted
            if (is_null($group)) {
                $this->message->status = 3;
                $this->message->save();
                return;
            }

            // set broadcast status to cancelled as group is archived
            if ($group->is_archived) {
                $this->message->status = 3;
                $this->message->save();
                return;
            }

            // attach broadcast into group
            $group->groupMessages()->attach($user, [
                'message'              => $this->message->message,
                'is_broadcast'         => true,
                'broadcast_company_id' => $this->message->company_id,
                'created_at'           => $created_at,
            ]);

            // update group updated_at
            $group->update(['updated_at' => now()->toDateTimeString()]);

            // update broadcast status
            $this->message->status = 2;
            $this->message->save();

            // send notification to users
            $notificationUsers = $group->members()
                ->select('users.id')
                ->wherePivot('status', 'Accepted')
                ->when($this->message->company_id, function ($query, $companyId) {
                    $query
                        ->join('user_team', 'user_team.user_id', '=', 'group_members.user_id')
                        ->where('user_team.company_id', $companyId);
                })
                ->get();

            if (!empty($notificationUsers) && $checkGroupAccess) {
                $notification = Notification::create([
                    'type'          => 'Auto',
                    'creator_id'    => $user->id,
                    'title'         => trans('notifications.group.broadcast.title'),
                    'message'       => __(trans('notifications.group.broadcast.message'), [
                        'message' => mb_strimwidth($this->message->message, 0, 80, '...'),
                    ]),
                    'push'          => true,
                    'scheduled_at'  => $created_at,
                    'deep_link_uri' => __(config('zevolifesettings.deeplink_uri.group'), [
                        'id' => $group->id,
                    ]),
                    'is_mobile'     => config('notification.group.broadcast.is_mobile', true),
                    'is_portal'     => config('notification.group.broadcast.is_portal', false),
                    'tag'           => 'broadcast',
                ]);

                foreach ($notificationUsers as $user) {
                    $user->notifications()->attach($notification, ['sent' => true, 'sent_on' => $created_at]);
                }

                \Notification::send(
                    $notificationUsers,
                    new SystemAutoNotification($notification, 'broadcast')
                );
            }
        } catch (\Exception $exception) {
            Log::critical(__CLASS__, [__FILE__, __LINE__, $exception->getMessage()]);
        }
    }
}
