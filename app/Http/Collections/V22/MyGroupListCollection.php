<?php

namespace App\Http\Collections\V22;

use App\Http\Resources\V22\MyGroupListResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MyGroupListCollection extends ResourceCollection
{
    protected $unreadMessageCount;
    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource, $unreadMessageCount = false)
    {
        $this->unreadMessageCount = $unreadMessageCount;

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
        return [
            'data'               => MyGroupListResource::collection($this->collection),
            'total'              => $this->collection->count(),
            'unreadMessageCount' => $this->unreadMessageCount,
            'pagination'         => [
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
