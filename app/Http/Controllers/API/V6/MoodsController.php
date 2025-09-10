<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V6;

use App\Http\Controllers\API\V3\MoodsController as v3MoodsController;
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

class MoodsController extends v3MoodsController
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
            $user = Auth::user();

            $moods = Mood::orderBy('title', 'ASC')->get();

            $data = [];
            $moods->each(function ($item, $key) use (&$data) {
                $data[] = [
                    'id'    => $item->id,
                    'title' => $item->title,
                    'image' => $item->getMediaData('logo', ['w' => 320, 'h' => 320, 'ct' => 1, 'zc' => 3]),
                ];
            });

            $isMoodSaved = MoodUser::where('user_id', $user->id)
                ->where('date', Carbon::today())
                ->first();

            $moodData = [
                'data'        => $data,
                'isMoodSaved' => isset($isMoodSaved) ,
            ];

            if (!empty($data)) {
                return $this->successResponse($moodData, 'Moods List retrieved successfully');
            } else {
                return $this->successResponse($moodData, 'No results');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse('Something went wrong!');
        }
    }

    /**
     * Get moods tag listing
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTags(Request $request)
    {
        try {
            $moodTags = MoodTag::orderBy('tag', 'ASC')->get();

            $tagData = [];
            $moodTags->each(function ($item, $key) use (&$tagData) {
                $tagData['data'][] = [
                    'id'  => $item->id,
                    'tag' => $item->tag,
                ];
            });

            if (!empty($tagData)) {
                return $this->successResponse($tagData, 'Tag List retrieved successfully');
            } else {
                return $this->successResponse(['data' => $tagData], 'No results');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse('Something went wrong!');
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

    /**
     * Get moods graph data
     *
     * @param Request $request, $year, $range
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGraphData(Request $request, $range)
    {
        try {
            $user = Auth::user();
            if ($range == 'weekly') {
                $start = Carbon::now()->subDays(7)->toDateString();
            } elseif ($range == 'monthly') {
                $start = Carbon::now()->subDays(30)->toDateString();
            } else {
                $start = Carbon::now()->subDays(365)->toDateString();
            }

            $end = Carbon::now()->toDateString();

            $moodUserData = MoodUser::where('user_id', $user->id)
                ->leftJoin('moods', 'mood_user.mood_id', '=', 'moods.id')
                ->whereBetween('mood_user.date', array($start, $end))
                ->select('moods.title as key', DB::raw("COUNT('mood_user.mood_id') as value"))
                ->groupBy('mood_user.mood_id')
                ->get()
                ->pluck('value', 'key')
                ->toArray();

            $moods = Mood::get()->pluck('title')->toArray();

            $data = [];
            foreach ($moods as $key => $value) {
                if (array_key_exists($value, $moodUserData)) {
                    $data[] = [
                        'key'   => $value,
                        'value' => $moodUserData[$value],
                    ];
                } else {
                    $data[] = [
                        'key'   => $value,
                        'value' => 0,
                    ];
                }
            }

            $minimumDate = MoodUser::where('user_id', $user->id)->first();

            $result = [
                'data'        => $data,
                'minimumDate' => isset($minimumDate) ? Carbon::parse($minimumDate->date)->toDateString() : null,
            ];

            return $this->successResponse($result, 'Graph data retrived successfully');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse('Something went wrong!');
        }
    }

    /**
     * Get moods history
     *
     * @param Request $request, $year, $month
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHistory(Request $request, $year, $month)
    {
        try {
            $user  = Auth::user();
            $start = Carbon::now()->year($year)->month($month)->firstOfMonth()->toDateString();
            $end   = Carbon::now()->year($year)->month($month)->lastOfMonth()->toDateString();

            $moodUserData = MoodUser::where('user_id', $user->id)
                ->whereBetween('date', array($start, $end))
                ->get();

            $data = [];
            $moodUserData->each(function ($item, $key) use ($user, &$data) {
                $moodTagUserData = MoodTagUser::where('user_id', $user->id)
                    ->where('mood_id', $item->mood_id)
                    ->where('date', $item->date)
                    ->get();

                $tags = [];
                $moodTagUserData->each(function ($i, $k) use (&$tags) {
                    $tags[] = $i->tag;
                });

                $data[] = [
                    'id'   => $item->id,
                    'date' => Carbon::parse($item->date)->toDateString(),
                    'mood' => $item->mood,
                    'tags' => $tags,
                ];
            });

            $minimumDate = MoodUser::where('user_id', $user->id)->first();

            $result = [
                'data'        => $data,
                'minimumDate' => isset($minimumDate) ? Carbon::parse($minimumDate->date)->toDateString() : null,
            ];

            if (!empty($data)) {
                return $this->successResponse($result, 'Moods History retrieved successfully');
            } else {
                return $this->successResponse(['data' => []], 'No results');
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse('Something went wrong!');
        }
    }
}
