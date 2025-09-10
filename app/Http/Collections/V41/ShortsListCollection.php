<?php declare (strict_types = 1);

namespace App\Http\Collections\V41;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\V41\ShortsListResource;

class ShortsListCollection extends ResourceCollection
{
    /**
     * session details
     *
     * @var collection
     **/
    protected $recomended;
    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource, $recomended = null)
    {
        $this->recomended     = $recomended;
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
        $data = [];
        if (!is_null($this->recomended) && $this->recomended) {
            $data['recomended'] = ShortsListResource::collection($this->recomended);
        }

        $data['data']  =   ShortsListResource::collection($this->collection);
        $data['total'] =   $this->collection->count();
        $data['pagination'] = [
            'total'        => $this->total(),
            'count'        => $this->count(),
            'per_page'     => $this->perPage(),
            'current_page' => $this->currentPage(),
            'total_pages'  => $this->lastPage(),
        ];

        return $data;
    }

    public function withResponse($request, $response)
    {
        $jsonResponse = json_decode($response->getContent(), true);
        unset($jsonResponse['links'], $jsonResponse['meta']);
        $response->setContent(json_encode($jsonResponse));
    }
}
