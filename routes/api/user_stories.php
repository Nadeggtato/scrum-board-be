<?php

Route::get('/{userStory}', 'show')->name('show');
Route::post('/bulk-add', 'bulkAdd')->name('bulk-add');
Route::post('/', 'store')->name('store');
Route::patch('/{userStory}', 'update')->name('update');
Route::delete('/{userStory}', 'destroy')->name('delete');
