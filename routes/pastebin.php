<?php

// Obsluga modulu pastebin
/** @var $this \Illuminate\Routing\Router */
$this->get('Pastebin/{pastebin?}', ['uses' => 'Pastebin\ShowController@index', 'as' => 'pastebin.show'])->where('pastebin', '\d+');
$this->post('Pastebin', ['uses' => 'Pastebin\SubmitController@save', 'as' => 'pastebin.submit']);
$this->post('Pastebin/Delete/{pastebin}', ['uses' => 'Pastebin\DeleteController@index', 'as' => 'pastebin.delete', 'middleware' => ['auth', 'can:pastebin-delete']]);
