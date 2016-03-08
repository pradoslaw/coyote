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

Route::get('/', ['as' => 'home', 'uses' => 'HomeController@index']);

// logowanie uzytkownika
Route::get('Login', ['uses' => 'Auth\LoginController@index', 'as' => 'login']);
Route::post('Login', 'Auth\LoginController@signin');
// wylogowanie
Route::get('Logout', ['uses' => 'Auth\LoginController@signout', 'as' => 'logout']);

// rejestracja uzytkownika
Route::get('Register', ['uses' => 'Auth\RegisterController@index', 'as' => 'register']);
Route::post('Register', 'Auth\RegisterController@signup');

// przypominanie hasla
Route::controller('Password', 'Auth\PasswordController');
Route::controller('Confirm', 'Auth\ConfirmController');

Route::get('OAuth/{provider}/Login', ['uses' => 'Auth\OAuthController@login', 'as' => 'oauth']);
Route::get('OAuth/{provider}/Callback', 'Auth\OAuthController@callback');

Route::group(['namespace' => 'Forum', 'prefix' => 'Forum', 'as' => 'forum.'], function () {
    // strona glowna forum
    Route::get('/', ['uses' => 'HomeController@index', 'as' => 'home']);
    Route::post('Preview', ['uses' => 'HomeController@preview', 'as' => 'preview']);
    Route::get('Search', ['uses' => 'SearchController@index', 'as' => 'search']);

    Route::get('Tag/{tag}', ['uses' => 'HomeController@tag', 'as' => 'tag']);
    Route::post('Tag/save', ['uses' => 'TagController@save', 'as' => 'tag.save']);
    Route::get('All', ['uses' => 'HomeController@all', 'as' => 'all']);
    Route::get('Unanswered', ['uses' => 'HomeController@unanswered', 'as' => 'unanswered']);
    Route::get('Mine', ['uses' => 'HomeController@mine', 'as' => 'mine']);
    Route::get('Subscribes', ['uses' => 'HomeController@subscribes', 'as' => 'subscribes']);
    Route::post('Mark', ['uses' => 'HomeController@mark', 'as' => 'mark']);

    // dodawanie zalacznika do posta
    Route::post('Upload', ['uses' => 'AttachmentController@upload', 'as' => 'upload']);
    // sciaganie zalacznika
    Route::get('Download/{id}', ['uses' => 'AttachmentController@download', 'as' => 'download']);
    // wklejanie zdjec przy pomocy Ctrl+V w textarea
    Route::post('Paste', ['uses' => 'AttachmentController@paste', 'as' => 'paste']);

    // formularz dodawania nowego watku na forum
    Route::get('{forum}/Submit', ['uses' => 'TopicController@submit', 'as' => 'topic.submit', 'middleware' => ['forum.access', 'forum.write']]);
    Route::post('{forum}/Submit', ['uses' => 'TopicController@save', 'middleware' => ['forum.access', 'forum.write']]);
    Route::post('{forum}/Mark', ['uses' => 'CategoryController@mark', 'as' => 'category.mark', 'middleware' => 'forum.access']);
    Route::post('{forum}/Section', ['uses' => 'CategoryController@section', 'as' => 'section']);

    // dodawanie lub edycja posta na forum
    Route::get('{forum}/{topic}/Submit/{post?}', ['uses' => 'PostController@submit', 'as' => 'post.submit', 'middleware' => ['topic.access', 'forum.access', 'forum.write']]);
    Route::post('{forum}/{topic}/Submit/{post?}', ['uses' => 'PostController@save', 'middleware' => ['topic.access', 'forum.access', 'forum.write']]);
    Route::get('{forum}/{topic}/Edit/{post}', ['uses' => 'PostController@edit', 'as' => 'post.edit', 'middleware' => ['topic.access', 'forum.access', 'forum.write']]);

    // obserwowanie danego watku na forum
    Route::post('Topic/Subscribe/{id}', ['uses' => 'TopicController@subscribe', 'as' => 'topic.subscribe', 'middleware' => 'auth']);
    // blokowanie watku
    Route::post('Topic/Lock/{id}', ['uses' => 'TopicController@lock', 'as' => 'lock', 'middleware' => 'auth']);
    // podpowiadanie nazwy uzytkownika (w kontekscie danego watku)
    Route::get('Topic/Prompt/{id}', ['uses' => 'TopicController@prompt', 'as' => 'prompt']);
    // przeniesienie watku do innej kategorii
    Route::post('Topic/Move/{id}', ['uses' => 'TopicController@move', 'as' => 'move']);
    // oznacz watek jako przeczytany
    Route::post('Topic/Mark/{topic}', ['uses' => 'TopicController@mark', 'as' => 'topic.mark']);
    // szybka zmiana tytulu watku
    Route::post('Topic/Subject/{topic}', ['uses' => 'TopicController@subject', 'as' => 'topic.subject', 'middleware' => 'auth']);
    // dziennik zdarzen dla watku
    Route::get('Stream/{topic}', ['uses' => 'StreamController@index', 'as' => 'stream', 'middleware' => ['auth']]);

    // widok kategorii forum
    Route::get('{forum}', ['uses' => 'CategoryController@index', 'as' => 'category', 'middleware' => 'forum.access']);
    // widok wyswietlania watku. {topic}
    Route::get('{forum}/{topic}-{slug}', ['uses' => 'TopicController@index', 'as' => 'topic', 'middleware' => ['forum.access', 'topic.access']]);

    // usuwanie posta
    Route::post('Post/Delete/{id}', ['uses' => 'PostController@delete', 'as' => 'post.delete', 'middleware' => 'auth']);
    // obserwowanie posta
    Route::post('Post/Subscribe/{id}', ['uses' => 'PostController@subscribe', 'as' => 'post.subscribe', 'middleware' => 'auth']);
    // glosowanie na dany post
    Route::post('Post/Vote/{id}', ['uses' => 'PostController@vote', 'as' => 'post.vote']);
    // akceptowanie danego posta jako poprawna odpowiedz w watku
    Route::post('Post/Accept/{id}', ['uses' => 'PostController@accept', 'as' => 'post.accept']);
    // historia edycji danego posta
    Route::get('Post/Log/{post}', ['uses' => 'PostController@log', 'as' => 'post.log']);

    // edycja/publikacja komentarza oraz jego usuniecie
    Route::post('Comment/{id?}', ['uses' => 'CommentController@save', 'as' => 'comment.save', 'middleware' => 'auth']);
    Route::get('Comment/{id}', ['uses' => 'CommentController@edit', 'middleware' => 'auth']);
    Route::post('Comment/Delete/{id}', ['uses' => 'CommentController@delete', 'as' => 'comment.delete', 'middleware' => 'auth']);

    // skrocony link do posta
    Route::get('{id}', ['uses' => 'ShareController@index', 'as' => 'share']);
});

/*
 * Modul "Praca"
 */
Route::group(['namespace' => 'Job', 'prefix' => 'Praca', 'as' => 'job.'], function () {
    Route::get('/', ['uses' => 'HomeController@index', 'as' => 'home']);

    Route::get('Submit/{id?}', ['uses' => 'SubmitController@getIndex', 'as' => 'submit', 'middleware' => 'auth']);
    Route::post('Submit', ['uses' => 'SubmitController@postIndex', 'middleware' => 'auth']);

    Route::get('Submit/Firm', ['uses' => 'SubmitController@getFirm', 'as' => 'submit.firm', 'middleware' => 'auth']);
    Route::post('Submit/Firm', ['uses' => 'SubmitController@postFirm', 'middleware' => 'auth']);

    Route::get('Submit/Preview', ['uses' => 'SubmitController@getPreview', 'as' => 'submit.preview', 'middleware' => 'auth']);
    Route::post('Submit/Save', ['uses' => 'SubmitController@save', 'as' => 'submit.save', 'middleware' => 'auth']);

    Route::get('Technologia/{name}', ['uses' => 'HomeController@index', 'as' => 'tag']);
    Route::get('Zdalna', ['uses' => 'HomeController@index', 'as' => 'remote']);
    Route::get('Miasto/{name}', ['uses' => 'HomeController@index', 'as' => 'city']);

    Route::get('{id}-{slug}', ['uses' => 'OfferController@index', 'as' => 'offer']);
});

Route::group(['namespace' => 'Firm', 'prefix' => 'Firma', 'as' => 'firm.'], function () {
    Route::post('Logo', ['uses' => 'SubmitController@logo', 'as' => 'logo']);
});

/*
 * Tymczasowe reguly
 */
Route::get('/Delphi', ['as' => 'page', 'uses' => 'Wiki\WikiController@category']);
Route::get('/Delphi/Lorem_ipsum', ['as' => 'article', 'uses' => 'Wiki\WikiController@article']);


// Obsluga mikroblogow
Route::group(['namespace' => 'Microblog', 'prefix' => 'Mikroblogi', 'as' => 'microblog.'], function () {
    Route::get('/', ['uses' => 'HomeController@index', 'as' => 'home']);
    Route::post('Edit/{id?}', ['uses' => 'SubmitController@save', 'as' => 'save', 'middleware' => 'auth']);
    Route::get('Edit/{id}', ['uses' => 'SubmitController@edit', 'middleware' => 'auth']);

    Route::get('Upload', 'SubmitController@thumbnail');
    Route::post('Upload', ['uses' => 'SubmitController@upload', 'as' => 'upload', 'middleware' => 'auth']);
    Route::post('Paste', ['uses' => 'SubmitController@paste', 'as' => 'paste', 'middleware' => 'auth']);
    Route::get('View/{id}', ['uses' => 'ViewController@index', 'as' => 'view']);
    Route::post('Vote/{id}', ['uses' => 'VoteController@post', 'as' => 'vote']);
    Route::get('Vote/{id}', ['uses' => 'VoteController@voters', 'as' => 'voters']);
    Route::post('Subscribe/{id}', ['uses' => 'SubscribeController@post', 'as' => 'subscribe', 'middleware' => 'auth']);
    Route::post('Delete/{id}', ['uses' => 'SubmitController@delete', 'as' => 'delete', 'middleware' => 'auth']);

    // edycja/publikacja komentarza oraz jego usuniecie
    Route::post('Comment/{id?}', ['uses' => 'CommentController@save', 'as' => 'comment.save', 'middleware' => 'auth']);
    Route::get('Comment/{id}', ['uses' => 'CommentController@edit', 'middleware' => 'auth']);
    Route::post('Comment/Delete/{id}', ['uses' => 'CommentController@delete', 'as' => 'comment.delete', 'middleware' => 'auth']);
    // pokaz reszte komentarzy...
    Route::get('Comment/Show/{id}', ['uses' => 'CommentController@show', 'as' => 'comment.show']);

    Route::get('Mine', ['uses' => 'HomeController@mine', 'as' => 'mine']);
    Route::get('{tag}', ['uses' => 'HomeController@tag', 'as' => 'tag']);
});

// Obsluga modulu pastebin
Route::get('Pastebin', ['uses' => 'Pastebin\HomeController@index', 'as' => 'pastebin.home']);
Route::get('Pastebin/{id}', ['uses' => 'Pastebin\HomeController@show', 'as' => 'pastebin.show'])->where('id', '\d+');
Route::post('Pastebin', ['uses' => 'Pastebin\HomeController@save']);

Route::group(['namespace' => 'User', 'prefix' => 'User', 'middleware' => 'auth', 'as' => 'user.'], function () {

    // strona glowna panelu uzytkownika
    Route::get('/', ['uses' => 'HomeController@index', 'as' => 'home']);
    // dodawanie i usuwanie zdjecia uzytkownika
    Route::post('Photo/Upload', ['uses' => 'HomeController@upload', 'as' => 'photo.upload']);
    Route::post('Photo/Delete', ['uses' => 'HomeController@delete', 'as' => 'photo.delete']);

    // ustawienia uzytkownika
    Route::get('Settings', ['uses' => 'SettingsController@index', 'as' => 'settings']);
    Route::post('Settings', 'SettingsController@save');

    Route::get('Visits', ['uses' => 'VisitsController@index', 'as' => 'visits']);
    Route::post('Visits', 'VisitsController@save');

    Route::get('Alerts', ['uses' => 'AlertsController@index', 'as' => 'alerts']);
    Route::get('Alerts/Settings', ['uses' => 'AlertsController@settings', 'as' => 'alerts.settings']);
    Route::post('Alerts/Settings', 'AlertsController@save');
    Route::get('Alerts/Ajax', ['uses' => 'AlertsController@ajax', 'as' => 'alerts.ajax']);
    Route::post('Alerts/Mark/{id?}', ['uses' => 'AlertsController@markAsRead', 'as' => 'alerts.mark']);
    Route::post('Alerts/Delete/{id}', ['uses' => 'AlertsController@delete', 'as' => 'alerts.delete']);

    Route::get('Pm', ['uses' => 'PmController@index', 'as' => 'pm']);
    Route::get('Pm/Show/{id}', ['uses' => 'PmController@show', 'as' => 'pm.show']);
    Route::get('Pm/Submit', ['uses' => 'PmController@submit', 'as' => 'pm.submit']);
    Route::post('Pm/Submit', 'PmController@save');
    Route::post('Pm/Delete/{id}', ['uses' => 'PmController@delete', 'as' => 'pm.delete']);
    Route::post('Pm/Preview', ['uses' => 'PmController@preview', 'as' => 'pm.preview']);
    Route::get('Pm/Ajax', ['uses' => 'PmController@ajax', 'as' => 'pm.ajax']);
    Route::post('Pm/Paste', ['uses' => 'PmController@paste', 'as' => 'pm.paste']);

    Route::get('Favorites', ['uses' => 'FavoritesController@index', 'as' => 'favorites']);
    Route::post('Favorites', 'FavoritesController@save');

    Route::get('Profiles', ['uses' => 'ProfilesController@index', 'as' => 'profiles']);
    Route::post('Profiles', 'ProfilesController@save');

    Route::get('Rates', ['uses' => 'RatesController@index', 'as' => 'rates']);
    Route::post('Rates', 'RatesController@save');

    Route::get('Stats', ['uses' => 'StatsController@index', 'as' => 'stats']);
    Route::post('Stats', 'StatsController@save');

    Route::get('Accepts', ['uses' => 'AcceptsController@index', 'as' => 'accepts']);
    Route::post('Accepts', 'AcceptsController@save');

    Route::get('Skills', ['uses' => 'SkillsController@index', 'as' => 'skills']);
    Route::post('Skills', 'SkillsController@save');
    Route::post('Skills/Order', ['uses' => 'SkillsController@order', 'as' => 'skills.order']);
    Route::post('Skills/{id}', ['uses' => 'SkillsController@delete', 'as' => 'skills.delete']);

    Route::get('Security', ['uses' => 'SecurityController@index', 'as' => 'security']);
    Route::post('Security', 'SecurityController@save');

    Route::get('Password', ['uses' => 'PasswordController@index', 'as' => 'password']);
    Route::post('Password', 'PasswordController@save');

    Route::get('Forum', ['uses' => 'ForumController@index', 'as' => 'forum']);
    Route::post('Forum', 'ForumController@save');
});

// wizytowka usera. komponent ktory pojawia sie po naprowadzenia kursora nad login usera
Route::get('User/Vcard/{id}', ['uses' => 'User\VcardController@index', 'as' => 'user.vcard']);
// zadanie AJAX z lista loginow (podpowiedzi)
Route::get('User/Prompt', ['uses' => 'User\PromptController@index', 'as' => 'user.prompt']);
// zapis ustawien do tabeli settings. moga to byc np. niestandardowe ustawienia takie jak
// np. domyslna zakladka na stronie glownej
Route::post('User/Settings/Ajax', ['uses' => 'User\SettingsController@ajax', 'as' => 'user.settings.ajax']);

// logowanie do panelu administracyjnego (ponowne wpisanie hasla)
Route::match(['get', 'post'], 'Adm', ['uses' => 'Adm\HomeController@index', 'as' => 'adm.home', 'middleware' => ['auth', 'adm:0']]);

// dostep do panelu administracyjnego
Route::group(['namespace' => 'Adm', 'middleware' => ['auth', 'adm:1'], 'prefix' => 'Adm', 'as' => 'adm.'], function () {
    Route::get('Dashboard', 'DashboardController@index')->name('dashboard');

    Route::get('Forum/Category', 'Forum\CategoryController@index')->name('forum.category');
    Route::get('Forum/Access', 'Forum\AccessController@index')->name('forum.access');

    Route::get('User', 'UserController@index')->name('user');
    Route::get('Stream', 'StreamController@index')->name('stream');
    Route::get('Cache', 'CacheController@index')->name('cache');
});

Route::get('Profile/{user}', ['uses' => 'Profile\HomeController@index', 'as' => 'profile']);
Route::get('Tag/Prompt', ['uses' => 'Tag\PromptController@index', 'as' => 'tag.prompt']);
Route::get('Tag/Validate', ['uses' => 'Tag\PromptController@valid', 'as' => 'tag.validate']);
Route::get('Flag', ['uses' => 'FlagController@index', 'as' => 'flag', 'middleware' => 'auth']);
Route::post('Flag', ['uses' => 'FlagController@save', 'middleware' => 'auth']);

Route::get('/{slug}', function ($slug) {
    return view('errors/404');

})->where('slug', '.*');