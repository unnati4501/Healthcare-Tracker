<?php declare (strict_types = 1);

namespace App\Http\Collections\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Http\Resources\V2\RecipeListResource;

class RecipeListCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data'       => RecipeListResource::collection($this->collection),
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

    public function withResponse($request, $response)
    {
        $jsonResponse = json_decode($response->getContent(), true);
        unset($jsonResponse['links'], $jsonResponse['meta']);
        $response->setContent(json_encode($jsonResponse));
    }
}
