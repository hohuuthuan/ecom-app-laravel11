<?php

namespace App\Helpers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PaginationHelper
{
    public static function appendQuery(LengthAwarePaginator $paginator): LengthAwarePaginator
    {
        return $paginator->appends(request()->query());
    }
}
