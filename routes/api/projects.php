<?php

use Illuminate\Support\Facades\Route;

Route::get('/{project}', 'show')->name('show');
Route::post('/', 'store')->name('store');
Route::patch('/{project}', 'update')->name('update');
