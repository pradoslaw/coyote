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

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\DB;

/** @var $this Router */
$this->get('/', ['as' => 'home', 'uses' => 'HomeController@index']);

$this->get('Flag', ['uses' => 'FlagController@index', 'as' => 'flag', 'middleware' => 'auth']);
$this->post('Flag', ['uses' => 'FlagController@save', 'middleware' => 'auth']);
$this->post('Flag/Delete/{flag}', ['uses' => 'FlagController@delete', 'middleware' => 'auth', 'as' => 'flag.delete']);
$this->get('Flag/Delete/{flag}', ['uses' => 'FlagController@modal', 'middleware' => 'auth', 'as' => 'flag.modal']);

$this->get('sitemap/{sitemap?}', ['uses' => 'SitemapController@index', 'as' => 'sitemap']);

$this->get('Search', ['uses' => 'SearchController@index', 'as' => 'search']);

$this->post('mailgun/permanent-failure', 'MailgunController@permanentFailure');

$this->post('github/sponsorship', 'GithubController@sponsorship');

$this->post('assets', 'AssetsController@upload')->middleware('throttle.submission:1,1');
$this->get('assets/opg', 'AssetsController@opengraph');
$this->get('assets/{asset}/{name}', ['uses' => 'AssetsController@download', 'as' => 'assets.download']);

$this->get('campaign/{banner}', ['uses' => 'CampaignController@redirect', 'as' => 'campaign.redirect']);

$this->post('Settings/Ajax', [
    'uses' => function (Request $request) {
        $key = $request->get('key');
        if ($key === null) {
            return response([], 400);
        }
        $settings = DB::table('settings_key_value');
        $setting = $settings->where(['key' => $key])->first();
        if ($setting === null) {
            $settings->insert(['key' => $key, 'value' => '1']);
        } else {
            $value = (int)$setting->value;
            $settings
                ->where(['key' => $key])
                ->update(['value' => (string)($value + 1)]);
        }
    },
]);
