<?php

namespace App\Http\Collections\V31;

use App\Http\Resources\V22\EventListingResource;
use App\Http\Resources\V31\EAPSessionResource;
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
     * Session Access or not from Company plan
     *
     * @var collection
     **/
    protected $checkSessionAccess;

    /**
     * Event Access or not from company plan
     *
     * @var collection
     **/
    protected $checkEventAccess;

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
    public function __construct($resource, $checkSessionAccess, $checkEventAccess, $sessionDetails = null, $pagination = false)
    {
        $this->sessionDetails     = $sessionDetails;
        $this->checkSessionAccess = $checkSessionAccess;
        $this->checkEventAccess   = $checkEventAccess;
        $this->pagination         = $pagination;
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
            if ($this->checkEventAccess) {
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
            }

            if (!is_null($this->sessionDetails) && $this->checkSessionAccess) {
                //$data['session'] = new EAPSessionResource($this->sessionDetails);
                $data['session'] = EAPSessionResource::collection($this->sessionDetails);
            }

            if (!$this->checkEventAccess && !$this->checkSessionAccess) {
                $data = [
                    'data'    => [],
                    'session' => [],
                ];
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
