<?php
declare (strict_types = 1);

namespace App\Observers;

use App\Models\Course;
use App\Models\Group;
use App\Models\Notification;

/**
 * Class CourseObserver
 *
 * @package App\Observers
 */
class CourseObserver
{
    /**
     * @param Course $course
     */
    public function creating(Course $course)
    {
        $deepLinkURI = "zevolife://zevo/masterclass/" . $course->getKey();
        $course->forceFill(['deep_link_uri' => $deepLinkURI, 'random_students' => rand(10, 50)]);
    }

    /**
     * @param Course $course
     */
    public function created(Course $course)
    {
        $deepLinkURI = "zevolife://zevo/masterclass/" . $course->getKey();
        $course->update(['deep_link_uri' => $deepLinkURI]);
    }

    /**
     * @param Course $course
     */
    public function deleted(Course $course)
    {
        $deepLinkURI    = "zevolife://zevo/masterclass/" . $course->getKey();
        $npsDeeplinkURI = __(config('zevolifesettings.deeplink_uri.masterclass_csat'), [
            'id' => $course->getKey(),
        ]);

        // delete cousre related notifications
        Notification::where('deep_link_uri', 'LIKE', $deepLinkURI . '/%')
            ->orWhere('deep_link_uri', 'LIKE', $deepLinkURI)
            ->delete();
        Notification::where('deep_link_uri', 'LIKE', $npsDeeplinkURI . '/%')
            ->orWhere('deep_link_uri', 'LIKE', $npsDeeplinkURI)
            ->delete();

        $group = Group::where('model_name', 'masterclass')
            ->where('model_id', $course->getKey())
            ->first();
        if (!is_null($group)) {
            $group->delete();
        }
    }
}
