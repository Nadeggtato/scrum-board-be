<?php

use Illuminate\Support\Facades\Route;

Route::get('/{project}', 'show');
Route::post('/', 'store');
Route::patch('/{project}', 'update');
