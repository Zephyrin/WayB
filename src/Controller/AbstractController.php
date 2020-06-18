<?php

namespace App\Controller;

trait AbstractController
{
    public function setPaginateToView($paginationArray, $parent) {
        $view = $parent->view(
            $paginationArray[0]
        );
        $view->setHeader('X-Total-Count', $paginationArray[1]);
        $view->setHeader('X-Pagination-Count', $paginationArray[2]);
        $view->setHeader('X-Pagination-Page', $paginationArray[3]);
        $view->setHeader('X-Pagination-Limit', $paginationArray[4]);
        $view->setHeader('Access-Control-Expose-Headers'
            , 'X-Total-Count, X-Pagination-Count, X-Pagination-Page, X-Pagination-Limit');
        return $view;
}
}