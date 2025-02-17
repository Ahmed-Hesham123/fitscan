<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FilterDataService
{
    public static function addFiltering($request, $query, $sortable = [], $searchable = [], $pagination = false)
    {
        $sortBy = null;
        $sortDirection = 'desc';
        $search = '';
        $page = 1;
        $perPage = 10;

        // -------------------- Validation -----------------------
        // validate sort
        if (
            Validator::make($request->all(), ['sort_by' => ['required', Rule::in(array_keys($sortable))]])->passes()
            && Validator::make($request->all(), ['sort_direction' => ['required', Rule::in(['asc', 'desc'])]])->passes()
        ) {
            $sortBy = $request->input('sort_by');
            $sortDirection = $request->input('sort_direction');
        }

        // validate search
        if (Validator::make($request->all(), ['search' => ['required', 'string']])->passes()) {
            $search = trim($request->input('search'));
        }

        // validate pagination
        if (
            Validator::make($request->all(), ['page' => ['required', 'integer', 'min:1']])->passes()
            && Validator::make($request->all(), ['per_page' => ['required', 'integer', 'min:1']])->passes()
        ) {
            $page = $request->input('page');
            $perPage = $request->input('per_page');
        }

        // -------------------- Building Queries -----------------------
        // Add sorting to query
        $query->when($sortBy, function ($q) use ($sortable, $sortBy, $sortDirection) {
            return $q->orderBy(DB::raw($sortable[$sortBy]), $sortDirection);
        });

        // Add searching to query for specific columns
        $query->when($search, function ($q) use ($searchable, $search) {
            $firstColumn = true;

            foreach ($searchable as $key => $column) {
                // Use 'where' for the first column, and 'orWhere' for the rest
                $method = $firstColumn ? 'where' : 'orWhere';

                $q->$method(DB::raw('upper(' . $column . ')'), 'like', '%' . strtoupper($search) . '%');

                // After the first iteration, set $firstColumn to false
                $firstColumn = false;
            }
        });

        // Add pagination to query
        if ($pagination == true) {
            $query->skip(($page - 1) * $perPage)->take($perPage);
        }

        return $query;
    }
}
