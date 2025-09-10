<?php declare (strict_types = 1);

namespace App\Http\Collections\V21;

use App\Http\Resources\V6\EAPListResource;
use App\Models\EAPIntroduction;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EAPListCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $user         = Auth()->user();
        $company      = $user->company()->first();
        $introduction = EAPIntroduction::where('company_id', $company->id)->first();
        if (empty($introduction) || (!empty($introduction) && $introduction->introduction == null)) {
            $introduction = EAPIntroduction::find(1);
        }

        return [
            'data' => [
                'introduction' => ($introduction->introduction ?? ''),
                'eapList'      => EAPListResource::collection($this->collection),
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
