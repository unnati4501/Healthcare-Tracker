<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V33;

use App\Http\Controllers\API\V32\FeedController as v32FeedController;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Feed;
use Illuminate\Http\Request;

class FeedController extends v32FeedController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;
    /**
     * like-un-like feed
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function likeUnlike(Request $request, Feed $feed)
    {
        try {
            $user           = $this->user();
            $pivotExsisting = $feed
                ->feedUserLogs()
                ->wherePivot('user_id', $user->getKey())
                ->wherePivot('feed_id', $feed->getKey())
                ->first();

            if (!empty($pivotExsisting)) {
                $liked                        = $pivotExsisting->pivot->liked;
                $pivotExsisting->pivot->liked = (($liked == 1) ? 0 : 1);
                $pivotExsisting->pivot->save();

                if ($liked == 1) {
                    RemovePointContentActivities('feed', $feed->id, $user->id, 'like');

                    return $this->successResponse(['data' => ['totalLikes' => $feed->getTotalLikes()]], trans('api_messages.feed.unliked'));
                } else {
                    UpdatePointContentActivities('feed', $feed->id, $user->id, 'like');

                    return $this->successResponse(['data' => ['totalLikes' => $feed->getTotalLikes()]], trans('api_messages.feed.liked'));
                }
            } else {
                $feed
                    ->feedUserLogs()
                    ->attach($user, ['liked' => true]);
                return $this->successResponse(['data' => ['totalLikes' => $feed->getTotalLikes()]], trans('api_messages.feed.liked'));
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
