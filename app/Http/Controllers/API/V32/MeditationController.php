<?php
declare (strict_types = 1);

namespace App\Http\Controllers\API\V32;

use App\Http\Collections\V32\RecentMeditationCollection;
use App\Http\Controllers\API\V31\MeditationController as v31MeditationController;
use App\Models\MeditationTrack;
use App\Models\User;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeditationController extends v31MeditationController
{
    /**
     * Get recent meditations
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function recentMeditations(Request $request)
    {
        try {
            $user     = $this->user();
            $company  = $user->company()->first();
            $team     = $user->teams()->first();
            $timezone = $user->timezone ?? config('app.timezone');
            $limit    = config('zevolifesettings.default_limits.most_liked_meditation_limit');

            $data['recentMeditations'] = $this->getRecentMeditationList();
            $data['guidedMeditations'] = $this->getRecentMeditationList('guided');
            $data['mostPlayed']        = $this->getRecentMeditationList('played');
            $data['mostLiked']         = MeditationTrack::select(
                'meditation_tracks.*',
                "sub_categories.name as meditationSubCategory",
                DB::raw('IFNULL(sum(user_meditation_track_logs.liked),0) AS most_liked'),
                DB::raw("(SELECT duration_listened FROM user_incompleted_tracks WHERE meditation_track_id = `meditation_tracks`.`id` AND user_id = " . $user->getKey() . " ) AS duration_listened"),
                DB::raw("(SELECT COUNT(NULLIF(liked, 0)) as totalLikes FROM user_meditation_track_logs WHERE meditation_track_id = `meditation_tracks`.`id` AND user_id = " . $user->getKey() . " ) AS totalLikes"),
                DB::raw("(SELECT favourited_at FROM user_meditation_track_logs WHERE meditation_track_id = `meditation_tracks`.`id` AND user_id = " . $user->getKey() . " ) AS favourited_at"),
                DB::raw("(SELECT count(id) FROM user_listened_tracks WHERE meditation_track_id = `meditation_tracks`.`id` GROUP BY meditation_track_id ) AS count_user_listened")
            )
                ->join('meditation_tracks_team', function ($join) use ($team) {
                    $join->on('meditation_tracks.id', '=', 'meditation_tracks_team.meditation_track_id')
                        ->where('meditation_tracks_team.team_id', '=', $team->getKey());
                })
                ->leftJoin('user_meditation_track_logs', 'user_meditation_track_logs.meditation_track_id', '=', 'meditation_tracks.id')
                ->join('sub_categories', function ($join) {
                    $join->on('sub_categories.id', '=', 'meditation_tracks.sub_category_id');
                })
                ->orderBy('most_liked', 'DESC')
                ->groupBy('meditation_tracks.id')
                ->having('most_liked', '>', '0')
                ->limit($limit)
                ->get();
            // Collect required data and return response
            return $this->successResponse(new RecentMeditationCollection($data), 'recent meditations listed successfully');
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get recent meditation [Recent, Most Played, Guided]
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function getRecentMeditationList($type = "")
    {
        try {
            $user     = $this->user();
            $company  = $user->company()->first();
            $team     = $user->teams()->first();
            $timezone = $user->timezone ?? config('app.timezone');
            $limit    = config('zevolifesettings.default_limits.recent_meditation_limit');

            $records = MeditationTrack::select(
                'meditation_tracks.*',
                "sub_categories.name as meditationSubCategory",
                DB::raw('IFNULL(sum(user_meditation_track_logs.view_count),0) AS view_count'),
                DB::raw('IFNULL(sum(user_meditation_track_logs.liked),0) AS most_liked'),
                DB::raw("(SELECT duration_listened FROM user_incompleted_tracks WHERE meditation_track_id = `meditation_tracks`.`id` AND user_id = " . $user->getKey() . " ) AS duration_listened"),
                DB::raw("(SELECT COUNT(NULLIF(liked, 0)) as totalLikes FROM user_meditation_track_logs WHERE meditation_track_id = `meditation_tracks`.`id` AND user_id = " . $user->getKey() . " ) AS totalLikes"),
                DB::raw("(SELECT favourited_at FROM user_meditation_track_logs WHERE meditation_track_id = `meditation_tracks`.`id` AND user_id = " . $user->getKey() . " ) AS favourited_at"),
                DB::raw("(SELECT count(id) FROM user_listened_tracks WHERE meditation_track_id = `meditation_tracks`.`id` GROUP BY meditation_track_id ) AS count_user_listened")
            )
                ->join('meditation_tracks_team', function ($join) use ($team) {
                    $join->on('meditation_tracks.id', '=', 'meditation_tracks_team.meditation_track_id')
                        ->where('meditation_tracks_team.team_id', '=', $team->getKey());
                })
                ->leftJoin('user_meditation_track_logs', 'user_meditation_track_logs.meditation_track_id', '=', 'meditation_tracks.id')
                ->join('sub_categories', function ($join) {
                    $join->on('sub_categories.id', '=', 'meditation_tracks.sub_category_id');
                });

            if ($type == 'guided') {
                $limit = config('zevolifesettings.default_limits.guided_meditation_limit');
                $records->where(["meditation_tracks.type" => 1, "meditation_tracks.audio_type" => 2]);
            }

            if ($type == 'played') {
                $records->orderBy('view_count', 'DESC')
                    ->orderBy('meditation_tracks.updated_at', 'DESC');
            } else {
                $records->orderBy('meditation_tracks.updated_at', 'DESC');
            }
            $records = $records->groupBy('meditation_tracks.id');
            $records = $records->limit($limit)->get()->shuffle();

            return $records;
        } catch (\Exception $e) {
            \DB::rollback();
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
