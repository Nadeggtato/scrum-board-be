<?php

Route::post('/', 'store')->name('store');
Route::patch('/{sprint}', 'update')->name('update');
