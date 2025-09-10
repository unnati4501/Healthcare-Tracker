<?php

namespace App\Http\Collections\V11;

use App\Http\Resources\V11\GethelpResource;
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
    public function __construct($resource, $forceUpdateCount = false, $deviceOs = "", $lastActivityAt = "", $appSettingCollection = [])
    {
        $this->forceUpdateCount     = $forceUpdateCount;
        $this->deviceOs             = $deviceOs;
        $this->lastActivityAt       = $lastActivityAt;
        $this->appSettingCollection = $appSettingCollection;

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
                    if (($setting->key == 'splash_image_url')) {
                        $dataToReturn[$setting->key] = $setting->getMediaData($setting->key, ['w' => 640, 'h' => 1280, 'zc' => 1]);
                    } elseif (($setting->key == 'logo_image_url')) {
                        $dataToReturn[$setting->key] = $setting->getMediaData($setting->key, ['w' => 320, 'h' => 320, 'ct' => 1]);
                    } else {
                        $dataToReturn[$setting->key] = $setting->getMediaData($setting->key, ['w' => 320, 'h' => 320]);
                    }
                } elseif ($setting->type == 'radio') {
                    $dataToReturn[$setting->key] = (!empty($setting->value));
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
                $dataToReturn['userRestriction'] = $company->group_restriction;
                $companywiseAppSetting           = $company->companywiseAppSetting()->get();
                if ($companywiseAppSetting->count() > 0) {
                    foreach ($companywiseAppSetting as $value) {
                        if ($value->type == 'file') {
                            if (($value->key == 'splash_image_url')) {
                                $dataToReturn[$value->key] = $value->getMediaData($value->key, ['w' => 640, 'h' => 1280, 'zc' => 1]);
                            } elseif (($value->key == 'logo_image_url')) {
                                $dataToReturn[$value->key] = $value->getMediaData($value->key, ['w' => 320, 'h' => 320, 'ct' => 1]);
                            } else {
                                $dataToReturn[$value->key] = $value->getMediaData($value->key, ['w' => 320, 'h' => 320]);
                            }
                        } elseif ($value->type == 'radio') {
                            $dataToReturn[$value->key] = (!empty($value->value));
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
                    ->whereNotNull("challenge_wise_user_log.finished_at")
                    ->where(\DB::raw("CONVERT_TZ(challenges.end_date, 'UTC', '{$userTimeZone}')"), ">=", $lastActivityAt)
                    ->where(\DB::raw("CONVERT_TZ(challenges.end_date, 'UTC', '{$userTimeZone}')"), "<=", $currentTime)
                    ->groupBy("challenge_wise_user_log.challenge_id")
                    ->get();

                $participatedChallenges = array();

                if ($challengeData->count() > 0) {
                    foreach ($challengeData as $value) {
                        $participatedChallenges[] = [
                            "id"    => $value->id,
                            "title" => $value->title,
                            "iswin" => (!empty($value->is_winner) && $value->is_winner),
                            "type"  => $value->challenge_type,
                        ];
                    }
                }

                $userData['completedChallenges'] = $participatedChallenges;
            }
            $dataToReturn['eapSetting'] =  new GethelpResource($user);
        } else {
            $dataToReturn['eapSetting'] = config('zevolifesettings.eapSetting');
        }

        if (!isset($dataToReturn['logo_image_url'])) {
            $dataToReturn['logo_image_url'] = $setting->getMediaData('logo_image_url', ['w' => 320, 'h' => 320, 'ct' => 1]);
        }

        $userData['ios_force_update'] = $userData['android_force_update'] = false;
        if ($this->deviceOs == 'android' && $this->forceUpdateCount >= 1) {
            $userData['android_force_update'] = true;
        } elseif ($this->deviceOs == 'ios' && $this->forceUpdateCount >= 1) {
            $userData['ios_force_update'] = true;
        }

        return [
            'data'               => (!empty($userData)) ? array_merge($dataToReturn, $userData) : $dataToReturn,
            'companyLabelString' => $this->appSettingCollection,
        ];
    }

    public function withResponse($request, $response)
    {
        $jsonResponse = json_decode($response->getContent(), true);
        unset($jsonResponse['links'], $jsonResponse['meta']);
        $response->setContent(json_encode($jsonResponse));
    }
}
