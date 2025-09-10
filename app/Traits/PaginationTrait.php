<?php

namespace App\Traits;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

trait PaginationTrait {
    public function paginate($items, $page = null, $path = [])
    {
        $xDeviceOs    = strtolower(Request()->header('X-Device-Os', ""));
        if ($xDeviceOs == config('zevolifesettings.PORTAL')) {
            $perPage = config('zevolifesettings.datatable.pagination.portal');
        } else {
            $perPage  = config('zevolifesettings.datatable.pagination.short');
        }
        $page           = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $total          = count($items);
        $currentpage    = $page;
        $offset         = ($currentpage * $perPage) - $perPage ;
        $itemstoshow    = array_slice($items , $offset , $perPage);
        return new LengthAwarePaginator($itemstoshow ,$total   ,$perPage, null, $path);
    }
}