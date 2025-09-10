<?php
namespace App\Builders;

use App\Pagination;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;

class BaseBuilder extends Builder
{

    /**
     * Create a new paginator instance.
     *
     * @param  \Illuminate\Support\Collection  $items
     * @param  int  $total
     * @param  int  $perPage
     * @param  int  $currentPage
     * @param  array  $options
     * @return \App\Pagination\ZevoCustomPaginator
     */
    protected function paginator($items, $total, $perPage, $currentPage, $options)
    {
        return Container::getInstance()->makeWith(
            Pagination\ZevoCustomPaginator::class,
            compact(
                'items',
                'total',
                'perPage',
                'currentPage',
                'options'
            )
        );
    }

    /**
     * Paginate the given query.
     *
     * @param  int|null  $perPage
     * @param  array  $columns
     * @param  string  $pageName
     * @param  int|null  $page
     * @return App\Pagination\YourCustomPaginator
     *
     * @throws \InvalidArgumentException
     */
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $requestLimit = Request()->input('perPage');
        $xDeviceOs    = strtolower(Request()->header('X-Device-Os', ""));
        if ($requestLimit) {
            $perPage = (int) $requestLimit;
        } elseif ($xDeviceOs == config('zevolifesettings.PORTAL')) {
            $limit   = config('zevolifesettings.datatable.pagination.portal');
            $perPage = $limit;
        } else {
            $limit   = config('zevolifesettings.datatable.pagination.short');
            $perPage = $limit;
        }
        $page    = $page ?: Paginator::resolveCurrentPage($pageName);
        $perPage = $perPage ?: $this->model->getPerPage();
        $results = ($total = $this->toBase()->getCountForPagination())
        ? $this->forPage($page, $perPage)->get($columns)
        : $this->model->newCollection();
        return new \App\Pagination\ZevoCustomPaginator($results, $total, $perPage, $page, [
            'path'     => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }
}
