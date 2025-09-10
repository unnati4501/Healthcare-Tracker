<?php

namespace App\Http\Collections\V8;

use App\Http\Resources\V8\GoalTagResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class GoalTagCollection extends ResourceCollection
{
    protected $userSelectedGoals;
    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource, $userSelectedGoals = array())
    {
        $this->userSelectedGoals = $userSelectedGoals;

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
        GoalTagResource::using($this->userSelectedGoals);
        return GoalTagResource::collection($this->collection);
    }

    public function withResponse($request, $response)
    {
        $jsonResponse = json_decode($response->getContent(), true);
        unset($jsonResponse['links'], $jsonResponse['meta']);
        $response->setContent(json_encode($jsonResponse));
    }
}
