<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V33;

use App\Http\Controllers\API\V7\MoodsController as v7MoodsController;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Mood;
use App\Models\MoodTag;
use App\Models\MoodTagUser;
use App\Models\MoodUser;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MoodsController extends v7MoodsController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

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
                    'mood_id'    => $payload['moodId'],
                    'date'       => Carbon::today(),
                ];

                $userMood = MoodUser::create($moodInput);

                if (isset($payload['tagList']) && $userMood) {
                    $tags         = $payload['tagList'];
                    $moodTagInput = [];
                    foreach ($tags as $key => $value) {
                        $moodTagInput[] = [
                            'user_id'    => $user->id,
                            'company_id' => $companyId,
                            'mood_id'    => $payload['moodId'],
                            'tag_id'     => $value,
                            'date'       => Carbon::today(),
                        ];
                    }
                    if (!empty($moodTagInput)) {
                        $userMoodTag = MoodTagUser::insert($moodTagInput);
                    }
                }
            }

            if (isset($userMoodTag)) {
                
                UpdatePointContentActivities('moods', $payload['moodId'], $user->id, 'mood');
                \DB::commit();
                return $this->successResponse([], trans('api_messages.survey.submit'));
            }
            \DB::rollback();
            return $this->internalErrorResponse('Something went wrong!');
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse('Something went wrong!');
        }
    }
}
