<?php

namespace App\Http\Collections\V4;

use Carbon\Carbon;
use DB;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;

class AppSettingCollection extends ResourceCollection
{
    protected $forceUpdateCount;
    protected $deviceOs;
    protected $lastActivityAt;
    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource, $forceUpdateCount = false, $deviceOs = "", $lastActivityAt = "")
    {
        $this->forceUpdateCount = $forceUpdateCount;
        $this->deviceOs         = $deviceOs;
        $this->lastActivityAt   = $lastActivityAt;

        parent::__construct($resource);
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $user     = \Auth::guard('api')->user();
        $userData = (!empty($user)) ? $user->getExtraFields() : [];

        $dataToReturn = [];
        if ($this->collection->count() > 0) {
            foreach ($this->collection as $setting) {
                if ($setting->type == 'file') {
                    $dataToReturn[$setting->key]['url']    = (($setting->key == 'splash_image_url') ? $setting->lg_logo : $setting->logo);
                    $dataToReturn[$setting->key]['width']  = 200;
                    $dataToReturn[$setting->key]['height'] = 200;
                } elseif ($setting->type == 'radio') {
                    $dataToReturn[$setting->key] = (!empty($setting->value)) ? true : false;
                } else {
                    $dataToReturn[$setting->key] = $setting->value;
                }
            }
        }

        if (!empty($user)) {
            $company = $user->company->first();
            $team    = $user->teams()->first();
            $dept    = $user->department()->first();

            if (!empty($company)) {
                $companywiseAppSetting = $company->companywiseAppSetting()->get();
                if ($companywiseAppSetting->count() > 0) {
                    foreach ($companywiseAppSetting as $key => $value) {
                        if ($value->type == 'file') {
                            $dataToReturn[$value->key]['url']    = (($value->key == 'splash_image_url') ? $value->lg_image_url : $value->image_url);
                            $dataToReturn[$value->key]['width']  = 200;
                            $dataToReturn[$value->key]['height'] = 200;
                        } elseif ($value->type == 'radio') {
                            $dataToReturn[$value->key] = (!empty($value->value)) ? true : false;
                        } else {
                            $dataToReturn[$value->key] = $value->value;
                        }
                    }
                }
            }

            if (!empty($team) && !empty($userData)) {
                $userData['company']['id']   = $company->getKey();
                $userData['company']['name'] = $company->name;

                $userData['department']['id']   = $dept->getKey();
                $userData['department']['name'] = $dept->name;

                $userData['team']['id']   = $team->getKey();
                $userData['team']['name'] = $team->name;
            }

            if (!empty($this->lastActivityAt)) {
                $lastActivityAt = Carbon::parse($this->lastActivityAt, config('app.timezone'))->setTimezone($user->timezone)->todatetimeString();
                $currentTime    = Carbon::now()->timezone($user->timezone)->todatetimeString();
                $userTimeZone   = $user->timezone;
                $challengeData  = DB::table("challenges")
                    ->join("challenge_wise_user_log", "challenge_wise_user_log.challenge_id", "=", "challenges.id")
                    ->select("challenges.*", "challenge_wise_user_log.is_winner")
                    ->where("challenges.challenge_type", "!=", "individual")
                    ->where("challenge_wise_user_log.user_id", $user->id)
                    ->where("finished", true)
                    ->where(\DB::raw("CONVERT_TZ(challenges.end_date, 'UTC', '{$userTimeZone}')"), ">=", $lastActivityAt)
                    ->where(\DB::raw("CONVERT_TZ(challenges.end_date, 'UTC', '{$userTimeZone}')"), "<=", $currentTime)
                    ->get();

                $participatedChallenges = array();

                if ($challengeData->count() > 0) {
                    foreach ($challengeData as $key => $value) {
                        $participatedChallenges[] = [
                            "id"    => $value->id,
                            "title" => $value->title,
                            "iswin" => (!empty($value->is_winner) && $value->is_winner == true) ? true : false,
                            "type"  => $value->challenge_type,
                        ];
                    }
                }

                $userData['completedChallenges'] = $participatedChallenges;
            }
        }

        if (!isset($dataToReturn['logo_image_url'])) {
            $dataToReturn['logo_image_url']['url']    = asset('app_assets/zevo_logo_splash.png');
            $dataToReturn['logo_image_url']['width']  = 200;
            $dataToReturn['logo_image_url']['height'] = 200;
        }

        $userData['ios_version']      = "1.0";
        $userData['ios_force_update'] = $userData['android_force_update'] = false;
        if ($this->deviceOs == 'android' && $this->forceUpdateCount >= 1) {
            $userData['android_force_update'] = true;
        } elseif ($this->deviceOs == 'ios' && $this->forceUpdateCount >= 1) {
            $userData['ios_force_update'] = true;
        }

        return [
            'data' => (!empty($userData)) ? array_merge($dataToReturn, $userData) : $dataToReturn,
        ];
    }

    public function withResponse($request, $response)
    {
        $jsonResponse = json_decode($response->getContent(), true);
        unset($jsonResponse['links'], $jsonResponse['meta']);
        $response->setContent(json_encode($jsonResponse));
    }
}
