<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

/** @var $this \Illuminate\Routing\Router */
$this->get('/', ['as' => 'home', 'uses' => 'HomeController@index']);

$this->get('Flag', ['uses' => 'FlagController@index', 'as' => 'flag', 'middleware' => 'auth']);
$this->post('Flag', ['uses' => 'FlagController@save', 'middleware' => 'auth']);
$this->post('Flag/Delete/{flag}', ['uses' => 'FlagController@delete', 'middleware' => 'auth', 'as' => 'flag.delete']);
$this->get('Flag/Delete/{flag}', ['uses' => 'FlagController@modal', 'middleware' => 'auth', 'as' => 'flag.modal']);

$this->get('sitemap/{sitemap?}', ['uses' => 'SitemapController@index', 'as' => 'sitemap']);

$this->get('Search', ['uses' => 'SearchController@index', 'as' => 'search']);

$this->get('mailing/unsubscribe/{uuid}', 'MailingController@unsubscribe')->name('mailing.unsubscribe');
$this->post('mailgun/permanent-failure', 'MailgunController@permanentFailure');

$this->post('github/sponsorship', 'GithubController@sponsorship');

$this->post('assets', 'AssetsController@upload');
$this->get('assets/opg', 'AssetsController@opengraph');
$this->get('assets/{asset}/{name}', ['uses' => 'AssetsController@download', 'as' => 'assets.download']);

$this->get('campaign/{banner}', ['uses' => 'CampaignController@redirect', 'as' => 'campaign.redirect']);
