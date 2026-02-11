<?php

Route::post('/', 'store')->name('store');
Route::patch('/{userStory}', 'update')->name('update');
Route::delete('/{userStory}', 'destroy')->name('delete');
