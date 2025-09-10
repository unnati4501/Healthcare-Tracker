<?php
declare (strict_types = 1);

namespace App\Observers;

use App\Models\Challenge;
use App\Models\Group;
use App\Models\Notification;

/**
 * Class ChallengeObserver
 *
 * @package App\Observers
 */
class ChallengeObserver
{
    /**
     * @param Challenge $challenge
     */
    public function creating(Challenge $challenge)
    {
        $deepLinkURI = "zevolife://zevo/challenge/" . $challenge->getKey();
        $challenge->forceFill(['deep_link_uri' => $deepLinkURI]);
    }

    /**
     * @param Challenge $challenge
     */
    public function created(Challenge $challenge)
    {
        $deepLinkURI = "zevolife://zevo/challenge/" . $challenge->getKey();
        $challenge->update(['deep_link_uri' => $deepLinkURI]);
    }

    /**
     * @param Challenge $challenge
     */
    public function deleted(Challenge $challenge)
    {
        $deepLinkURI = "zevolife://zevo/challenge/" . $challenge->getKey();
        Notification::where('deep_link_uri', 'LIKE', $deepLinkURI . '/%')
            ->orWhere('deep_link_uri', 'LIKE', $deepLinkURI)
            ->delete();

        Group::where('model_name', 'challenge')
            ->where('model_id', $challenge->getKey())
            ->delete();
    }
}
