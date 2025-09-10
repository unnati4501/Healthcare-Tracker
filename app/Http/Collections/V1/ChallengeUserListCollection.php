<?php

namespace App\Http\Collections\V1;

use App\Http\Resources\V1\ChallengeTeamListResource;
use App\Http\Resources\V1\ChallengeUserListResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ChallengeUserListCollection extends ResourceCollection
{

    public $isTeamData;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource, $isTeamData)
    {
        parent::__construct($resource);

        $this->resource   = $this->collectResource($resource);
        $this->isTeamData = $isTeamData;
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
            'data'       => $this->isTeamData ? ChallengeTeamListResource::collection($this->collection) : ChallengeUserListResource::collection($this->collection),
            'total'      => $this->collection->count(),
            'pagination' => [
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
