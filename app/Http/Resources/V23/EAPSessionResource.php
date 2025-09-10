<?php declare (strict_types = 1);

namespace App\Http\Resources\V23;

use App\Http\Traits\ProvidesAuthGuardTrait;
use App\Models\user;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class EAPSessionResource extends JsonResource
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
        $user         = $this->user();
        $appTimezone  = config('app.timezone');
        $userTimeZone = (!empty($user->timezone) ? $user->timezone : $appTimezone);
        $therapist    = $this->therapist()->select('id', 'first_name', 'last_name', 'timezone')->first();
        $startDate    = Carbon::parse($this->start_time, $appTimezone)->setTimezone($userTimeZone);
        $status       = $this->status;
        $currentTime  = now($appTimezone)->todatetimeString();
        $bookAgainUrl = "";

        if ($this->start_time >= $currentTime && $this->status != 'rescheduled' && $this->status != 'canceled') {
            $status = 'upcoming';
        } elseif ($this->start_time <= $currentTime && $this->end_time >= $currentTime && $this->status != 'rescheduled' && $this->status != 'canceled' && $this->status != 'completed') {
            $status = 'ongoing';
        } elseif (($this->end_time <= $currentTime && $this->status != 'rescheduled' && $this->status != 'canceled') || $this->status == 'completed') {
            $status = 'completed';
        } elseif ($this->status == 'canceled') {
            $status = 'cancelled';
        }

        $isBookedCount = $user->bookedSessions()
            ->where('end_time', '>=', $currentTime)
            ->whereNull('cancelled_at')
            ->count();

        $allowBook = ($this->status != 'canceled' && $isBookedCount <= 0) ? true : false;
        if ($allowBook ) {
            // find the last booked session of the current session's therapist and logged in user and grab TherapistCalendlyHandle to book this session again
            $lastBookedSession = $user->myZdTickets()
                ->select('id', 'custom_fields')
                ->where('therapist_id', $therapist->id)
                ->orderByDesc('id')
                ->first();
            if (!empty($lastBookedSession)) {
                $fullName     = $user->first_name . ' ' . $user->last_name;
                $fullName     = preg_replace('/[^A-Za-z0-9\-]/', '%20', $fullName);
                $bookAgainUrl = ((!empty($lastBookedSession->custom_fields) && isset($lastBookedSession->custom_fields->TherapistCalendlyHandle)) ? $lastBookedSession->custom_fields->TherapistCalendlyHandle . '?name=' . $fullName . '&email=' . $user->email : "");
            }
        }

        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'therapistDetails' => [
                'id'   => $therapist->id,
                'name' => $therapist->full_name,
                'logo' => $therapist->getMediaData('counsellor_cover', ['w' => 1280, 'h' => 640, 'zc' => 3]),
            ],
            'startTime'        => $this->start_time->setTimezone($userTimeZone)->toAtomString(),
            'status'           => $status,
            'allowBook'        => ($this->status != 'canceled' && $isBookedCount <= 0) ? true : false,
            'bookAgainUrl'     => $this->when(($allowBook ), $bookAgainUrl),
        ];
    }
}
