<?php declare (strict_types = 1);

namespace App\Http\Resources\V36;

use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Models\User;
use App\Models\UsersServices;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Resources\Json\JsonResource;

class DigitalCounsellorListResource extends JsonResource
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
        $w                  = 800;
        $h                  = 800;
        $langeuageText      = '';
        $setHoursBy         = $setAvailabilityBy         = 1;
        $digitalTherapySlot = $workingTime = [];
        $slotDisplay        = false;
        $isGeneral          = false;
        $displayTimezone    = "";
        $user               = User::where('id', $this->id)->first();
        $loginUser          = $this->user();
        $company            = $loginUser->company()->first();
        $digitalTherapy     = $company->digitalTherapy()->first();
        if (!empty($digitalTherapy)) {
            $setHoursBy        = $digitalTherapy->set_hours_by;
            $setAvailabilityBy = $digitalTherapy->set_availability_by;
        }
        if ($setHoursBy == 2 && $setAvailabilityBy == 1) {
            $userTeam           = $loginUser->teams()->first();
            $teamLocation       = $userTeam->teamlocation()->first();
            $displayTimezone    = $teamLocation->timezone;
            $digitalTherapySlot = $company->digitalTherapySlots()
                ->where('digital_therapy_slots.location_id', $teamLocation->id)
                ->get()
                ->toArray();
        } else if ($setHoursBy == 1 && $setAvailabilityBy == 1) {
            $digitalTherapySlot = $company->digitalTherapySlots()
                ->whereNull('location_id')
                ->get()
                ->toArray();
        } else if (($setHoursBy == 1 && $setAvailabilityBy == 2) || ($setHoursBy == 2 && $setAvailabilityBy == 2)) {
            $slotDisplay         = true;
            $digitalTherapySlott = $company->digitalTherapySpecificSlots();
            if ($setHoursBy == 1 && $setAvailabilityBy == 2) { // Company - specific
                $digitalTherapySlott->whereNull('location_id');
            } else { // Location - specific
                $userTeam        = $loginUser->teams()->first();
                $teamLocation    = $userTeam->teamlocation()->first();
                $displayTimezone = $teamLocation->timezone;
                $digitalTherapySlott->where('digital_therapy_specific.location_id', $teamLocation->id);
            }
            // Get Data from database based on location and company specific
            $digitalTherapySlott = $digitalTherapySlott
                ->select(
                    \DB::raw("CONCAT(DATE_FORMAT(date, '%Y-%m-%d')) AS from_date"),
                    "start_time",
                    "end_time"
                )
                ->where(\DB::raw("CONCAT(DATE_FORMAT(digital_therapy_specific.date, '%Y-%m-%d'), ' ', digital_therapy_specific.start_time)"), '>=', Carbon::now()->toDateString())
                ->where('digital_therapy_specific.ws_id', $this->id)
                ->orderBy('date', 'ASC')
                ->get()
                ->toArray();

            // Company Specific and Location Specific Login Implementation
            foreach ($digitalTherapySlott as $value) {
                if ($setHoursBy == 2 && $setAvailabilityBy == 2) {
                    $day       = Carbon::parse($value['from_date'] . ' ' . $value['start_time'])->toDateString();
                    $startTime = Carbon::parse($value['from_date'] . ' ' . $value['start_time'])->toTimeString();
                    $endTime   = Carbon::parse($value['from_date'] . ' ' . $value['end_time'])->toTimeString();
                } else {
                    $day       = $value['from_date'];
                    $startTime = $value['start_time'];
                    $endTime   = $value['end_time'];
                }
                $workingTime[$day]['day']    = Carbon::parse($day)->format('D, d M');
                $workingTime[$day]['time'][] = Carbon::createFromFormat('H:i:s', $startTime)->format('h:i A') . ' - ' . Carbon::createFromFormat('H:i:s', $endTime)->format('h:i A');
            }
            $workingTime = array_slice(array_values($workingTime), 0, 3);
        }

        if (!empty($digitalTherapySlot)) {
            $slotDisplay = true;
            $isGeneral   = true;
            $isMon       = multiArraySearch($digitalTherapySlot, 'day', 'mon');
            $isTue       = multiArraySearch($digitalTherapySlot, 'day', 'tue');
            $isWed       = multiArraySearch($digitalTherapySlot, 'day', 'wed');
            $isThu       = multiArraySearch($digitalTherapySlot, 'day', 'thu');
            $isFri       = multiArraySearch($digitalTherapySlot, 'day', 'fri');
            $isSat       = multiArraySearch($digitalTherapySlot, 'day', 'sat');
            $isSun       = multiArraySearch($digitalTherapySlot, 'day', 'sun');

            // Start Time
            if (!empty($isMon)) {
                $startTime = Carbon::createFromFormat('H:i:s', $isMon[0]['start_time'], $user->timezone)->format('h:i A');
                $startKey  = ucfirst($isMon[0]['day']);
            } elseif (!empty($isTue)) {
                $startTime = Carbon::createFromFormat('H:i:s', $isTue[0]['start_time'], $user->timezone)->format('h:i A');
                $startKey  = ucfirst($isTue[0]['day']);
            } elseif (!empty($isWed)) {
                $startTime = Carbon::createFromFormat('H:i:s', $isWed[0]['start_time'], $user->timezone)->format('h:i A');
                $startKey  = ucfirst($isWed[0]['day']);
            } elseif (!empty($isThu)) {
                $startTime = Carbon::createFromFormat('H:i:s', $isThu[0]['start_time'], $user->timezone)->format('h:i A');
                $startKey  = ucfirst($isThu[0]['day']);
            } else {
                $startTime = Carbon::createFromFormat('H:i:s', $isFri[0]['start_time'], $user->timezone)->format('h:i A');
                $startKey  = ucfirst($isFri[0]['day']);
            }

            // End Time
            if (!empty($isFri)) {
                $keyTime = $isFri[count($isFri) - 1];
                $endTime = Carbon::createFromFormat('H:i:s', $keyTime['end_time'], $user->timezone)->format('h:i A');
                $endKey  = ucfirst($keyTime['day']);
            } elseif (!empty($isThu)) {
                $keyTime = $isThu[count($isThu) - 1];
                $endTime = Carbon::createFromFormat('H:i:s', $keyTime['end_time'], $user->timezone)->format('h:i A');
                $endKey  = ucfirst($keyTime['day']);
            } elseif (!empty($isWed)) {
                $keyTime = $isWed[count($isWed) - 1];
                $endTime = Carbon::createFromFormat('H:i:s', $keyTime['end_time'], $user->timezone)->format('h:i A');
                $endKey  = ucfirst($keyTime['day']);
            } elseif (!empty($isTue)) {
                $keyTime = $isTue[count($isTue) - 1];
                $endTime = Carbon::createFromFormat('H:i:s', $keyTime['end_time'], $user->timezone)->format('h:i A');
                $endKey  = ucfirst($keyTime['day']);
            } elseif (!empty($isMon)) {
                $keyTime = $isMon[count($isMon) - 1];
                $endTime = Carbon::createFromFormat('H:i:s', $keyTime['end_time'], $user->timezone)->format('h:i A');
                $endKey  = ucfirst($keyTime['day']);
            }

            $workingTime[0]['day']    = ($startKey == $endKey) ? $startKey : $startKey . '-' . $endKey;
            $workingTime[0]['time'][] = $startTime . ' - ' . $endTime;

            if (!empty($isSat)) {
                $secondStartTime = Carbon::createFromFormat('H:i:s', $isSat[0]['start_time'], $user->timezone)->format('h:i A');
                $startKey        = ucfirst($isSat[0]['day']);
                $secondEndTime   = Carbon::createFromFormat('H:i:s', $isSat[0]['end_time'], $user->timezone)->format('h:i A');
                $endKey          = ucfirst($isSat[0]['day']);
            }

            if (!empty($isSun)) {
                $secondKeyTime = $isSun[count($isSun) - 1];
                $secondEndTime = Carbon::createFromFormat('H:i:s', $secondKeyTime['end_time'], $user->timezone)->format('h:i A');
                $endKey        = ucfirst($secondKeyTime['day']);

                if (!isset($secondStartTime)) {
                    $secondStartTime = Carbon::createFromFormat('H:i:s', $secondKeyTime['start_time'], $user->timezone)->format('h:i A');
                    $startKey        = ucfirst($secondKeyTime['day']);
                }
            }

            if (!empty($isSat) || !empty($isSun)) {
                $workingTime[1]['day']    = ($startKey == $endKey) ? $startKey : $startKey . '-' . $endKey;
                $workingTime[1]['time'][] = $secondStartTime . ' - ' . $secondEndTime;
            }
        }

        $wsDetails = $user->wsuser()->first();
        if (!empty($wsDetails->language)) {
            $languageIds  = explode(',', $wsDetails->language);
            $languageList = config('zevolifesettings.userLanguage');
            foreach ($languageIds as $langId) {
                $langeuageText .= $languageList[$langId] . ', ';
            }
            $langeuageText = substr($langeuageText, 0, -2);
        }

        $subServiceCategories = UsersServices::where('user_id', $this->id)->leftjoin('service_sub_categories', 'service_sub_categories.id', '=', 'users_services.service_id')->select('service_sub_categories.name')->distinct()->get()->pluck('name')->toArray();

        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'exprience'       => $wsDetails->years_of_experience,
            'language'        => $langeuageText,
            'bio'             => ($this->about != null) ? $this->about : '',
            'gender'          => ($this->gender != null) ? $this->gender : '',
            'logo'            => $user->getMediaData('logo', ['w' => $w, 'h' => $h, 'zc' => 3]),
            'subServices'     => $subServiceCategories,
            'slotDisplay'     => $slotDisplay,
            'isGeneral'       => $isGeneral,
            'workingTime'     => $this->when(!empty($workingTime), $workingTime),
            'displayTimezone' => $this->when(!empty($displayTimezone), $displayTimezone),
        ];
    }
}
