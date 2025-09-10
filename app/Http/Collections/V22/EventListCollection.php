<?php

namespace App\Http\Collections\V22;

use App\Http\Resources\V22\EAPSessionResource;
use App\Http\Resources\V22\EventListingResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EventListCollection extends ResourceCollection
{

    /**
     * session details
     *
     * @var collection
     **/
    protected $sessionDetails;

    /**
     * pagination
     *
     * @var boolean
     **/
    protected $pagination;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource, $sessionDetails = null, $pagination = false)
    {
        $this->sessionDetails = $sessionDetails;
        $this->pagination     = $pagination;
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
        if ($this->pagination == false) {
            $data = [
                'data'       => EventListingResource::collection($this->collection),
                'total'      => $this->collection->count(),
                'pagination' => [
                    'total'        => $this->total(),
                    'count'        => $this->count(),
                    'per_page'     => $this->perPage(),
                    'current_page' => $this->currentPage(),
                    'total_pages'  => $this->lastPage(),
                ],
            ];

            if (!is_null($this->sessionDetails)) {
                $data['session'] = new EAPSessionResource($this->sessionDetails);
            }

            return $data;
        } else {
            return EventListingResource::collection($this->collection);
        }
    }

    public function withResponse($request, $response)
    {
        $jsonResponse = json_decode($response->getContent(), true);
        unset($jsonResponse['links'], $jsonResponse['meta']);
        $response->setContent(json_encode($jsonResponse));
    }
}
