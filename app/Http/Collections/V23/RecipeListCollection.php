<?php declare (strict_types = 1);

namespace App\Http\Collections\V23;

use App\Http\Resources\V23\RecipeSearchListResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class RecipeListCollection extends ResourceCollection
{
    /**
     * to indicate pass paginated respond or normal
     *
     * @var boolean
     **/
    protected $pagination;

    /**
     * Create a new resource instance.
     *
     * @param mixed  $resource
     * @param boolean $pagination
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
        if ($this->pagination == false) {
            return RecipeSearchListResource::collection($this->collection);
        } else {
            return [
                'data'       => RecipeSearchListResource::collection($this->collection),
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
