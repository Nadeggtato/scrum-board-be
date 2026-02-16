<?php

use Illuminate\Support\Facades\Route;

Route::post('/', 'store')->name('store');
Route::patch('/{task}', 'update')->name('update');
