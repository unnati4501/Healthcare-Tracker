<?php declare (strict_types = 1);

namespace App\Http\Resources\V34;

use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Models\ConsentFormLogs;
use App\Models\ScheduleUsers;
use App\Models\Service;
use App\Models\ServiceSubCategory;
use App\Models\user;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class CronofySessionDetailsResource extends JsonResource
{
    use ProvidesAuthGuardTrait;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $user                = $this->user();
        $company             = $user->company()->select('companies.id')->first();
        $digitalTherapy      = $company->digitalTherapy()->first();
        $wsUser              = user::where('id', $this->ws_id)->first();
        $oldMexicoTimezone   = config('zevolifesettings.mexico_city_timezone.old_timezone');
        $newMexicoTimezone   = config('zevolifesettings.mexico_city_timezone.new_timezone');
        $wsUserDetails       = $wsUser->wsuser()->first();
        $appTimezone         = config('app.timezone');
        $userTimeZone        = (!empty($this->timezone) ? $this->timezone : (!empty($user->timezone) ? $user->timezone : $appTimezone));
        $userTimeZone        = ($userTimeZone == $oldMexicoTimezone && $wsUser->is_timezone) ? $newMexicoTimezone : $userTimeZone;
        $startDate           = Carbon::parse($this->start_time)->setTimezone($userTimeZone);
        $endDate             = Carbon::parse($this->end_time)->setTimezone($userTimeZone);
        $currentTime         = now($appTimezone)->todatetimeString();
        $diff                = $startDate->diffInMinutes($endDate);
        $startTime           = $startDate->format('h:ia');
        $endTime             = $endDate->format('h:ia');
        $day                 = $startDate->format('l, M d, Y');
        $sessionDurationTime = $startTime . ' - ' . $endTime . ' ' . $day;
        $langeuageText       = '';
        $sessionUpdate       = config('cronofy.sessionUpdate');
        $service             = [];
        $topic               = [];
        $isConsentFormSent   = true;
        $isConsentChecked    = true;
        $status              = null;

        if (!empty($digitalTherapy)) {
            $sessionUpdate    = $digitalTherapy->dt_session_update;
            $isConsentChecked = ($digitalTherapy->consent == 1);
        }
        $sessionUpdate = $sessionUpdate * 3600;

        if (!empty($wsUserDetails->language)) {
            $languageIds  = explode(',', $wsUserDetails->language);
            $languageList = config('zevolifesettings.userLanguage');
            foreach ($languageIds as $langId) {
                $langeuageText .= $languageList[$langId] . ', ';
            }
            $langeuageText = substr($langeuageText, 0, -2);
        }

        if ($this->start_time >= $currentTime && $this->status != 'rescheduled' && $this->status != 'canceled') {
            $status = 'upcoming';
        } elseif ($this->start_time <= $currentTime && $this->end_time >= $currentTime && $this->status != 'rescheduled' && $this->status != 'open' && $this->status != 'canceled' && $this->status != 'completed') {
            $status = 'ongoing';
        } elseif (($this->end_time <= $currentTime && $this->status != 'open' && $this->status != 'rescheduled' && $this->status != 'canceled') || $this->status == 'completed') {
            $status = 'completed';
        } elseif ($this->status == 'canceled') {
            $status = 'cancelled';
        }

        if ($this->is_group) {
            $checkGroupSessionDetails = ScheduleUsers::where('user_id', $user->id)->where('session_id', $this->id)->select('is_cancelled', 'cancelled_at', 'cancelled_reason')->first();
            if (!empty($checkGroupSessionDetails) && $checkGroupSessionDetails->is_cancelled) {
                $status                 = 'cancelled';
                $this->status           = 'canceled';
                $this->cancelled_at     = $checkGroupSessionDetails->cancelled_at;
                $this->cancelled_reason = $checkGroupSessionDetails->cancelled_reason;
            }
        }

        $cancellationDetails = [];
        if ($this->status == 'canceled') {
            $cancelledAt         = Carbon::parse($this->cancelled_at)->setTimezone($userTimeZone);
            $cancellationDetails = [
                'reason' => (!empty($this->cancelled_reason) ? $this->cancelled_reason : ""),
                'at'     => $cancelledAt->format('g:ia, l, F j, Y'),
            ];
        }

        $allowBook = ($this->status != 'canceled' ? !$this->hasUpComingSession : false);

        if ($this->service_id != null) {
            $serviceDetails  = Service::where('id', $this->service_id)->select('id', 'name')->first();
            $serviceIcon     = $serviceDetails->getMediaData('icon', ['w' => 36, 'h' => 36, 'zc' => 3]);
            $service['id']   = $serviceDetails->id;
            $service['name'] = $serviceDetails->name;
            $service['icon'] = $serviceIcon;
        }
        if ($this->topic_id != null) {
            $topic = ServiceSubCategory::where('id', $this->topic_id)->select('id', 'name')->first()->toArray();
        }

        //Check if consent form is submitted or not
        $getConsentFormLogs = ConsentFormLogs::where(['user_id' => $user->id, 'ws_id' => $this->ws_id])->select('id')->count();
        if ($isConsentChecked) {
            if ($getConsentFormLogs > 0) {
                $isConsentFormSent = true;
            } elseif ($getConsentFormLogs == 0) {
                $isConsentFormSent = false;
            }
        }

        return [
            'id'                         => $this->id,
            'name'                       => $this->name,
            'wellbeingSpecialistDetails' => $this->getWellbeingSpecialistData(),
            'coverImage'                 => $wsUser->getMediaData('counsellor_cover', ['w' => 1280, 'h' => 640, 'zc' => 3]),
            'notes'                      => $this->notes,
            'duration'                   => $diff . ' min',
            'sessionDurationTime'        => $sessionDurationTime,
            'startTime'                  => $startDate->toAtomString(),
            'endTime'                    => $endDate->toAtomString(),
            'language'                   => $langeuageText,
            'timezone'                   => (isset($this->timezone) && !empty($this->timezone) ? $this->timezone : $wsUser->timezone),
            'eventIdentifier'            => (!empty($this->event_identifier)) ? $this->event_identifier : "",
            'location'                   => $this->location,
            'isGroup'                    => ($this->is_group) ,
            'status'                     => $status,
            'cancellationDetails'        => $this->when(($this->status == 'canceled'), $cancellationDetails),
            'service'                    => $this->when(!empty($service), $service),
            'topic'                      => $this->when(!empty($topic), $topic),
            'participants'               => $this->scheduleUsers()->count(),
            'sessionUpdate'              => $sessionUpdate,
            'allowBook'                  => $allowBook,
            'isConsentFormSent'          => $isConsentFormSent,
        ];
    }
}
