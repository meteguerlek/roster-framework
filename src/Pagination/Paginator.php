<?php

namespace Roster\Pagination;

use Roster\Http\Request;
use Roster\Logger\Log;

class Paginator
{
    public $total = null;

    public $perPage = null;

    public $currentPage = null;

    public $lastPage = null;

    /**
     * Paginator constructor.
     *
     * @param $total
     * @param $perPage
     * @param $currentPage
     * @param $lastPage
     */
    public function __construct($total, $perPage, $currentPage, $lastPage)
    {
        $this->total = $total;
        $this->perPage = $perPage;
        $this->currentPage = $currentPage + 1;
        $this->lastPage = $lastPage;
    }

    /**
     * Get prev page
     *
     * @return int|null
     */
    protected function prevPage()
    {
        $prevPage = $this->currentPage - 1;

        if ($prevPage <= 0)
        {
            return false;
        }

        return $prevPage;
    }

    /**
     * Get next page
     *
     * @return int|null
     */
    protected function nextPage()
    {
        $nextPage = $this->currentPage + 1;

        if ($nextPage > $this->lastPage)
        {
            return false;
        }

        return $nextPage;
    }

    /**
     * If last page (number of page) is more then 8
     *
     * @return array|null
     */
    protected function multiPager()
    {
        if ($this->lastPage >= 8)
        {
            $pages = [];

            for ($i = 1; $i < 5 + 1; $i++)
            {
                $prev = $this->currentPage - $i;

                if (!($prev <= 0))
                {
                    array_unshift($pages, $prev);
                }
            }

            for ($i = 1; $i < 5 + 1; $i++)
            {
                $prev = $this->currentPage + $i - 1;

                if (!($prev > $this->lastPage))
                {
                    $pages[] = $prev;
                }
            }

            return $pages;
        }

        return false;
    }

    protected function uri()
    {
        if (Request::isset())
        {
            $get = Request::get();

            if (Request::has('page'))
            {
                unset($get['page']);
            }

            $uri = '';

            foreach ($get as $index => $value)
            {
                $uri .= $uri ? '&'.$index.'='.$value : '?'.$index.'='.$value;
            }

            return $uri
                ? $uri.'&page='
                : '?page=';
        }

        return '?page=';
    }

    /**
     * Compile paginate
     *
     * @return object
     */
    protected function paginate()
    {
        return (object) [
            'total' => $this->total,
            'per_page' => $this->perPage,
            'current_page' => $this->currentPage,
            'last_page' => $this->lastPage,
            'prev_page' => $this->prevPage(),
            'next_page' => $this->nextPage(),
            'multi_pager' => $this->multiPager(),
            'uri' => $this->uri()
        ];
    }

    /**
     * Show links
     *
     * @param string $template
     * @return \Roster\View\View
     */
    public function links($template = 'pagination.paginate')
    {
        if($this->total > $this->perPage)
        {
            return customView($template, ['paginate' => $this->paginate()]);
        }
    }
}
