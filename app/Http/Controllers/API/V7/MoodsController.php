<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V7;

use App\Http\Controllers\API\V6\MoodsController as v6MoodsController;
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

class MoodsController extends v6MoodsController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

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

            $moods = Mood::leftJoin("mood_user", function ($join) use ($user, $start, $end) {
                            $join->on("moods.id", "=", "mood_user.mood_id")
                            ->where("mood_user.user_id", $user->id)
                            ->whereBetween('mood_user.date', array($start, $end));
            })
                        ->select("moods.title", DB::raw("count(mood_user.mood_id) as totalMood"))
                        ->orderBy("totalMood", "DESC")
                        ->groupBy("moods.id")
                        ->get()->pluck('title')->toArray();

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
}
