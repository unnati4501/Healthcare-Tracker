<?php

namespace App\Http\Collections\V1;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\V1\OngoingCourseResource;

class OnGoingCourseCollection extends ResourceCollection
{
    protected $total;
    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource, $total = false)
    {
        $this->total = $total;

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
            'data'       => OngoingCourseResource::collection($this->collection),
            'total'      => $this->total,
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
