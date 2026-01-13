<?php

namespace App\Http\Controllers\API;

use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;

abstract class Controller
{
    protected function perPage(int $max = 50): int
    {
        $limit = request()->input('limit');

        if (is_numeric($limit) && $limit > 0 && $limit <= $max) {
            return $limit;
        }

        return 15;
    }

    protected function paginate(Builder|Relation $builder): CursorPaginator|LengthAwarePaginator
    {
        $type = request()->header('x-pagination');

        if ($type && Str::lower($type) === 'cursor') {
            return $builder->cursorPaginate($this->perPage());
        } else {
            return $builder->paginate($this->perPage());
        }
    }
}
