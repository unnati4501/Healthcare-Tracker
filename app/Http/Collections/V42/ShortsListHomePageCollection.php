<?php declare (strict_types = 1);

namespace App\Http\Collections\V42;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\V42\ShortsListHomePageResource;

class ShortsListHomePageCollection extends ResourceCollection
{
    /**
     * session details
     *
     * @var collection
     **/
    protected $pagination;
    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource)
    {
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
        return ShortsListHomePageResource::collection($this->collection);
    }

    public function withResponse($request, $response)
    {
        $jsonResponse = json_decode($response->getContent(), true);
        unset($jsonResponse['links'], $jsonResponse['meta']);
        $response->setContent(json_encode($jsonResponse));
    }
}
