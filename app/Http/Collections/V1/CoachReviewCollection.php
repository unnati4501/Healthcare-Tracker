<?php

namespace App\Http\Collections\V1;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\V1\CoachReviewResource;

class CoachReviewCollection extends ResourceCollection
{
    protected $pagination;
    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource, $pagination = false)
    {
        $this->pagination = $pagination;

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
        if ($this->pagination) {
            return [
                'data' => CoachReviewResource::collection($this->collection),
                'total' => $this->collection->count(),
                'pagination' => [
                    'total' => $this->total(),
                    'count' => $this->count(),
                    'per_page' => $this->perPage(),
                    'current_page' => $this->currentPage(),
                    'total_pages' => $this->lastPage()
                ],
            ];
        } else {
            return CoachReviewResource::collection($this->collection);
        }
    }

    public function withResponse($request, $response)
    {
        $jsonResponse = json_decode($response->getContent(), true);
        unset($jsonResponse['links'], $jsonResponse['meta']);
        $response->setContent(json_encode($jsonResponse));
    }
}
