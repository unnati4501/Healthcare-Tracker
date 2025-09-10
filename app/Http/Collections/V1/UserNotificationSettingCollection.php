<?php

namespace App\Http\Collections\V1;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserNotificationSettingCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $dataToReturn = [];
        if ($this->collection->count() > 0) {
            foreach ($this->collection as $setting) {
                $dataToReturn[$setting->module] = ($setting->flag);
            }
        }

        return [
            'data' => $dataToReturn,
        ];
    }

    public function withResponse($request, $response)
    {
        $jsonResponse = json_decode($response->getContent(), true);
        unset($jsonResponse['links'], $jsonResponse['meta']);
        $response->setContent(json_encode($jsonResponse));
    }
}
