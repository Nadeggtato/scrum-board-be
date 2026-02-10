<?php

Route::get('/{sprint}', 'show')->name('show');
Route::post('/', 'store')->name('store');
Route::patch('/{sprint}', 'update')->name('update');
