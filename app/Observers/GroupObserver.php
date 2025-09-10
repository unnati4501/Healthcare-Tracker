<?php
declare (strict_types = 1);

namespace App\Observers;

use App\Models\Group;
use App\Models\Notification;

/**
 * Class GroupObserver
 *
 * @package App\Observers
 */
class GroupObserver
{
    /**
     * @param Group $group
     */
    public function creating(Group $group)
    {
        $deepLinkURI = "zevolife://zevo/group/" . $group->getKey();
        $group->forceFill(['deep_link_uri' => $deepLinkURI]);
    }

    /**
     * @param Group $group
     */
    public function created(Group $group)
    {
        $deepLinkURI = "zevolife://zevo/group/" . $group->id;
        $group->update(['deep_link_uri' => $deepLinkURI]);
    }

    /**
     * Handle the User "updated" event.
     *
     * @param  Group $group
     * @return void
     */
    public function updated(Group $group)
    {
        if ($group->is_archived) {
            // set scheduled broadcast for this group to cancelled if any
            $group->broadcast()
                ->where('type', 'scheduled')
                ->where('status', '1')
                ->update([
                    'status' => '3',
                ]);
        }
    }

    /**
     * @param Group $group
     */
    public function deleted(Group $group)
    {
        $deepLinkURI = "zevolife://zevo/group/" . $group->getKey();
        Notification::where('deep_link_uri', 'LIKE', $deepLinkURI . '/%')
            ->orWhere('deep_link_uri', 'LIKE', $deepLinkURI)
            ->delete();

        // set scheduled broadcast for this group to cancelled if any
        $group->broadcast()
            ->where('type', 'scheduled')
            ->where('status', '1')
            ->update([
                'status' => '3',
            ]);
    }
}
