<?php
namespace App\Pagination;

use Illuminate\Pagination\LengthAwarePaginator;

class ZevoCustomPaginator extends LengthAwarePaginator
{
    /**
     * Get the instance as an array.
     *
     * This can be structured however you want and overrides
     * the function in LengthAwarePaginator.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'data' => $this->items->toArray(),
            'meta' => [
                'pagination' => [
                    'current_page'   => $this->currentPage(),
                    'first_page_url' => $this->url(1),
                    'from'           => $this->firstItem(),
                    'last_page'      => $this->lastPage(),
                    'last_page_url'  => $this->url($this->lastPage()),
                    'next_page_url'  => $this->nextPageUrl(),
                    'path'           => $this->path(),
                    'per_page'       => $this->perPage(),
                    'prev_page_url'  => $this->previousPageUrl(),
                    'to'             => $this->lastItem(),
                    'total'          => $this->total(),
                ],
            ],
        ];
    }
}
