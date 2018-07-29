<?php

namespace Roster\Database\Builds;

use Roster\Pagination\Paginator;

trait BuildsQueries
{
    /**
     * Create paginator
     *
     * @param $total
     * @param $perPage
     * @param $currentPage
     * @param $lastPage
     * @return Paginator
     */
    protected function paginator($total, $perPage, $currentPage, $lastPage)
    {
        return new Paginator($total, $perPage, $currentPage, $lastPage);
    }
}
