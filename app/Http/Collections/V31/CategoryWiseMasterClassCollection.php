<?php

namespace App\Http\Collections\V31;

use App\Http\Resources\V31\CategoryWiseMasterClassResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CategoryWiseMasterClassCollection extends ResourceCollection
{
    protected $totalEnrolledMasterclasses;
    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource, $totalEnrolledMasterclasses = false)
    {
        $this->totalEnrolledMasterclasses = $totalEnrolledMasterclasses;

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
            'data'                       => CategoryWiseMasterClassResource::collection($this->collection),
            'totalEnrolledMasterclasses' => $this->when($this->totalEnrolledMasterclasses, $this->totalEnrolledMasterclasses['counts']),
            'total'                      => $this->collection->count(),
            'pagination'                 => [
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
