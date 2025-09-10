<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V3;

use App\Http\Controllers\Controller;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Mood;
use App\Models\MoodUser;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MoodsController extends Controller
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Get moods static listing
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMoods(Request $request)
    {
        try {
            $moods = Mood::orderBy('title', 'ASC')->get();

            $moodData = [];
            $moods->each(function ($item, $key) use (&$moodData) {
                $moodData['data'][] = [
                    'id'    => $item->id,
                    'title' => $item->title,
                    'image' => $item->getMediaData('logo', ['w' => 320, 'h' => 320]),
                ];
            });

            return $this->successResponse($moodData, 'Moods List retrieved successfully');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Submit user mood
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitMood(Request $request)
    {
        $payload   = $request->all();
        $user      = $this->user();
        $companyId = !is_null($user->company->first()) ? $user->company->first()->id : null;

        $alreadySubmitted = MoodUser::where('user_id', $user->id)
            ->where('date', Carbon::today())
            ->first();

        if ($alreadySubmitted) {
            return $this->notFoundResponse('You have already shared the feelings for the day.');
        }

        try {
            \DB::beginTransaction();

            if (!empty($payload)) {
                $moodInput = [
                    'user_id'    => $user->id,
                    'company_id' => $companyId,
                    'mood_id'    => $payload['id'],
                    'date'       => Carbon::today(),
                ];

                $userMood = MoodUser::create($moodInput);
            }

            \DB::commit();
            return $this->successResponse([], 'Mood saved successfully.');
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
