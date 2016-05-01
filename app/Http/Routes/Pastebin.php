<?php

// Obsluga modulu pastebin
Route::get('Pastebin', ['uses' => 'Pastebin\HomeController@index', 'as' => 'pastebin.home']);
Route::get('Pastebin/{pastebin}', ['uses' => 'Pastebin\ShowController@index', 'as' => 'pastebin.show'])->where('pastebin', '\d+');
Route::post('Pastebin', ['uses' => 'Pastebin\SubmitController@index']);
