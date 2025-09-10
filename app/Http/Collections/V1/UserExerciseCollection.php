<?php

namespace App\Http\Collections\V1;

use App\Http\Resources\V1\UserExerciseResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserExerciseCollection extends ResourceCollection
{
    protected $page;
    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource, $page = false)
    {
        $this->page = $page;

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
        if ($this->page == 0) {
            return [
                'data' => UserExerciseResource::collection($this->collection),
            ];
        } else {
            return [
                'data'       => UserExerciseResource::collection($this->collection),
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
