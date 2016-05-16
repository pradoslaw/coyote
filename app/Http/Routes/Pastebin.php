<?php

// Obsluga modulu pastebin
Route::get('Pastebin/{pastebin?}', ['uses' => 'Pastebin\ShowController@index', 'as' => 'pastebin.show'])->where('pastebin', '\d+');
Route::post('Pastebin', ['uses' => 'Pastebin\SubmitController@save', 'as' => 'pastebin.submit']);
Route::post('Pastebin/Delete/{pastebin}', ['uses' => 'Pastebin\DeleteController@index', 'as' => 'pastebin.delete', 'middleware' => ['auth', 'can:pastebin-delete']]);
