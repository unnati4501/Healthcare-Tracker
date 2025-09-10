<?php

namespace App\Http\Collections\V2;

use App\Http\Resources\V1\ChallengeTeamListResource;
use App\Http\Resources\V1\ChallengeUserListResource;
use App\Http\Resources\V2\ChallengeCompanyListResource;
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
    public function __construct($resource, $challengeType)
    {
        parent::__construct($resource);

        $this->resource      = $this->collectResource($resource);
        $this->challengeType = $challengeType;
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if ($this->challengeType == 'individual') {
            return [
                'data'       => ChallengeUserListResource::collection($this->collection),
                'total'      => $this->collection->count(),
                'pagination' => [
                    'total'        => $this->total(),
                    'count'        => $this->count(),
                    'per_page'     => $this->perPage(),
                    'current_page' => $this->currentPage(),
                    'total_pages'  => $this->lastPage(),
                ],
            ];
        } elseif ($this->challengeType == 'team') {
            return [
                'data'       => ChallengeTeamListResource::collection($this->collection),
                'total'      => $this->collection->count(),
                'pagination' => [
                    'total'        => $this->total(),
                    'count'        => $this->count(),
                    'per_page'     => $this->perPage(),
                    'current_page' => $this->currentPage(),
                    'total_pages'  => $this->lastPage(),
                ],
            ];
        } elseif ($this->challengeType == 'company') {
            return [
                'data'       => ChallengeCompanyListResource::collection($this->collection),
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
    }

    public function withResponse($request, $response)
    {
        $jsonResponse = json_decode($response->getContent(), true);
        unset($jsonResponse['links'], $jsonResponse['meta']);
        $response->setContent(json_encode($jsonResponse));
    }
}
