<?php
declare(strict_types = 1);

namespace App\Observers;

use App\Models\Notification;
use App\Models\Team;

/**
 * Class TeamObserver
 *
 * @package App\Observers
 */
class TeamObserver
{
    /**
     * @param Team $team
     */
    public function creating(Team $team)
    {
        if (\blank($team->getAttributeValue('code'))) {
            $team->forceFill(['code' => $team->createUniqueCode()]);
        }
    }

    /**
     * @param Team $team
     */
    public function deleted(Team $team)
    {
        $deepLinkURI = "zevolife://zevo/team/" . $team->getKey();
        Notification::where('deep_link_uri', 'LIKE', $deepLinkURI . '/%')
            ->orWhere('deep_link_uri', 'LIKE', $deepLinkURI)
            ->delete();
    }
}
