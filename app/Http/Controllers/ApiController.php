<?php

namespace App\Http\Controllers;

use App\Support\ParseIncludes;
use Illuminate\Http\Request;

abstract class ApiController extends Controller
{
    protected function loadIncludes($model, Request $request, array $allowed)
    {
        $include = $request->query('include');

        if (empty($include)) {
            return $model;
        }

        $includes = app(ParseIncludes::class)($include, $allowed);

        $model->load($includes);

        return $model;
    }
}
