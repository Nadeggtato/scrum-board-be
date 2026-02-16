<?php

use Illuminate\Support\Facades\Route;

Route::post('/', 'store')->name('store');
Route::delete('/{user}', 'destroy')->name('delete');
