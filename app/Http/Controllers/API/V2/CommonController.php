<?php declare (strict_types = 1);

namespace App\Http\Controllers\API\V2;

use App\Http\Collections\V1\HomeCourseCollection;
use App\Http\Collections\V2\CompanyTeamDetailsCollection;
use App\Http\Collections\V2\FeedCollection;
use App\Http\Controllers\API\V1\CommonController as v1CommonController;
use App\Http\Resources\V1\SurveyListResource;
use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Http\Traits\ServesApiTrait;
use App\Models\Company;
use App\Models\Feed;
use App\Models\HsSurvey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Validator;

class CommonController extends v1CommonController
{
    use ServesApiTrait, ProvidesAuthGuardTrait;

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHomeStatistics(Request $request)
    {
        try {
            $user        = $this->user();
            $company     = $user->company()->first();
            $appTimezone = config('app.timezone');
            $timezone    = $user->timezone ?? config('app.timezone');
            $data        = array();

            // Health score survey

            $usersurveyData = HsSurvey::where('user_id', $user->id)
                ->whereNotNull('survey_complete_time')
                ->orderBy('id', 'DESC')
                ->first();

            $headers = $request->headers->all();
            $payload = $request->all();

            if (!empty($usersurveyData)) {
                $version                                   = config('zevolifesettings.version.api_version');
                $surveyHistoryRequest                      = Request::create("api/" . $version . "/healthscore/report/" . $usersurveyData->id, 'GET', $headers, $payload);
                $surveyHistoryResponse                     = \Route::dispatch($surveyHistoryRequest);
                $surveyHistoryBody                         = json_decode($surveyHistoryResponse->getContent());
                $surveyHistoryBody->result->data->surveyId = $usersurveyData->id;
            }

            if (!empty($surveyHistoryBody)) {
                $data['surveyinfo'] = new SurveyListResource($surveyHistoryBody);
            }

            // User statistics data for current day
            $userCalorieHistory = $user->steps()->select(\DB::raw("SUM(user_step.calories) as calories"), \DB::raw("SUM(user_step.steps) as steps"))
                ->where(\DB::raw("DATE(CONVERT_TZ(user_step.log_date, '{$appTimezone}', '{$timezone}'))"), '=', now($timezone)->toDateString())
                ->first();

            $meditationCount = $user->completedMeditationTracks()
                ->where(\DB::raw("DATE(CONVERT_TZ(user_listened_tracks.created_at, '{$appTimezone}', '{$timezone}'))"), '=', now($timezone)->toDateString())
                ->count();

            $data['userstatistics']['steps'] = (!empty($userCalorieHistory) && !empty($userCalorieHistory['steps'])) ? (int) $userCalorieHistory['steps'] : 0;

            $data['userstatistics']['calories'] = (!empty($userCalorieHistory) && !empty($userCalorieHistory['calories'])) ? (double) $userCalorieHistory['calories'] : 0.0;

            $data['userstatistics']['meditation'] = $meditationCount;

            // Home Course Pannel Logic
            $runningContent = [];

            // get count of course and meditation
            $runningCourseCount = $user->courseLogs()
                ->wherePivot('joined', 1)
                ->wherePivot('completed_on', '=', null)
                ->count();

            // get user's running lessions with course data
            $runningCourseRecords = $user->courseLogs()
                ->wherePivot('joined', 1)
                ->wherePivot('completed_on', '=', null)
                ->orderByDesc('user_course.joined_on');

            // use count based on receieved data from course  API total data count must be 10 max.
            $runningCourseRecords = $runningCourseRecords->paginate(10);

            // collect required course data
            $data['courses'] = new HomeCourseCollection($runningCourseRecords);

            // Feed List get max 10 feed for home Statistics

            $feedRecords = Feed::join('feed_company', function ($join) use ($company) {
                $join->on('feeds.id', '=', 'feed_company.feed_id')
                    ->where('feed_company.company_id', '=', $company->getKey());
            })
                ->join('feed_expertise_level', function ($join) {
                    $join->on('feeds.id', '=', 'feed_expertise_level.feed_id');
                })
                ->select('feeds.*')
                ->where(function (Builder $query) use ($timezone) {
                    return $query->where(\DB::raw("CONVERT_TZ(feeds.start_date, 'UTC', feeds.timezone)"), '<=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.start_date', null);
                })
                ->where(function (Builder $query) use ($timezone) {
                    return $query->where(\DB::raw("CONVERT_TZ(feeds.end_date, 'UTC', feeds.timezone)"), '>=', \DB::raw("CONVERT_TZ(now(), @@session.time_zone , feeds.timezone)"))->orWhere('feeds.end_date', null);
                });

            $feedRecords = $feedRecords->groupBy('feeds.id')
                ->orderByDesc('feeds.updated_at')
                ->limit(10)
                ->get();

            $data['feeds'] = new FeedCollection($feedRecords, true);

            return $this->successResponse(['data' => $data], 'Data retrieved successfully.');
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }

    /**
     * Get Company teams
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCompanyTeams(Request $request, $company)
    {
        try {
            $validator = Validator::make(
                array_merge(['company' => $company], $request->all()),
                [
                    'company' => 'required|integer|exists:companies,id',
                    'page'    => 'sometimes|required|integer',
                    'count'   => 'sometimes|required|integer',
                ],
                [
                    'company.required' => 'Please provide company identity',
                    'company.integer'  => 'Please provide valid company identity',
                    'company.exists'   => 'company doesn\'t exist, pleae provide valid company identity',
                ]
            );

            if (!$validator->fails()) {
                $companyTeams = Company::find($company)
                    ->teams()
                    ->paginate(config('zevolifesettings.datatable.pagination.short'));

                return $this->successResponse(
                    ($companyTeams->count() > 0) ? new CompanyTeamDetailsCollection($companyTeams) : ['data' => []],
                    ($companyTeams->count() > 0) ? 'Company teams list retrieved successfully.' : 'No company teams found'
                );
            } else {
                return $this->invalidResponse($validator->errors()->getMessages());
            }
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse(trans('labels.common_title.something_wrong'));
        }
    }
}
