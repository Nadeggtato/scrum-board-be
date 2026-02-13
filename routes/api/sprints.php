<?php

Route::get('/{sprint}', 'show')->name('show');
Route::post('/', 'store')->name('store');
Route::post('/bulk-add', 'bulkAdd')->name('bulk-add');
Route::patch('/{sprint}', 'update')->name('update');
