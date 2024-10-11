<?php
namespace Cms\Traits;
use Illuminate\Support\Arr;

trait Pagination
{

    public function GetPageData($Count, $PerPage, $CurrentPage = 1){
        $PerPage = min($PerPage, 50);
        $totalPages = ceil($Count / $PerPage);
        return [
            'currentPage'   => $CurrentPage,
            'nextPage'      => $CurrentPage >= $totalPages ? null : $CurrentPage + 1,
            'prevPage'      => $CurrentPage <= 1 ? null : $CurrentPage - 1,
            'totalPages'    => $totalPages,
            'limit'         => $PerPage,
            'offset'        => ($CurrentPage-1) * $PerPage
        ];
    }

}