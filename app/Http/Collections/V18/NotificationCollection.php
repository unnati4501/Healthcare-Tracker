<?php

namespace App\Http\Collections\V18;

use App\Http\Resources\V18\NotificationResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class NotificationCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $user = \Auth::user();

        return [
            'data'                      => NotificationResource::collection($this->collection),
            'total'                     => $this->collection->count(),
            'unread_notification_count' => $user->userUnreadNotifactionCount(),
            'pagination'                => [
                'total'        => $this->total(),
                'count'        => $this->count(),
                'per_page'     => $this->perPage(),
                'current_page' => $this->currentPage(),
                'total_pages'  => $this->lastPage(),
            ],
        ];
    }

    public function withResponse($request, $response)
    {
        $jsonResponse = json_decode($response->getContent(), true);
        unset($jsonResponse['links'], $jsonResponse['meta']);
        $response->setContent(json_encode($jsonResponse));
    }
}
