<?php declare (strict_types = 1);

namespace App\Observers;

use App\Models\Notification;
use App\Models\PersonalChallenge;

/**
 * Class PersonalChallengeObserver
 *
 * @package App\Observers
 */
class PersonalChallengeObserver
{
    /**
     * @param PersonalChallenge $personalChallenge
     */
    public function created(PersonalChallenge $personalChallenge)
    {
        if ($personalChallenge->challenge_type == 'habit') {
            $deepLinkURI = "zevolife://zevo/habit-challenge/" . $personalChallenge->getKey();
        } else {
            $deepLinkURI = "zevolife://zevo/personal-challenge/" . $personalChallenge->getKey();
        }
        $personalChallenge->update(['deep_link_uri' => $deepLinkURI]);
    }

    /**
     * @param PersonalChallenge $personalChallenge
     */
    public function deleted(PersonalChallenge $personalChallenge)
    {
        if ($personalChallenge->challenge_type == 'habit') {
            $deepLinkURI = "zevolife://zevo/habit-challenge/" . $personalChallenge->getKey();
        } else {
            $deepLinkURI = "zevolife://zevo/personal-challenge/" . $personalChallenge->getKey();
        }
        Notification::where('deep_link_uri', 'LIKE', $deepLinkURI . '/%')
            ->orWhere('deep_link_uri', 'LIKE', $deepLinkURI)
            ->delete();
    }
}
