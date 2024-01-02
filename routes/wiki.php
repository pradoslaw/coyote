<?php

use Illuminate\Routing\Router;

/** @var $this Router */
$this->group(['namespace' => 'Wiki', 'prefix' => '', 'as' => 'wiki.'], function () {
    /** @var $this Router */
    $this->get('Edit/{wiki?}', [
        'as'         => 'submit',
        'uses'       => 'SubmitController@index',
        'middleware' => [
            'auth', 'wiki.lock',
        ],
    ]);

    $this->get('Create/{path}', ['as' => 'create', 'uses' => 'SubmitController@create', 'middleware' => ['auth']]);

    $this->post('Edit/{wiki?}', ['as' => 'edit', 'uses' => 'SubmitController@save', 'middleware' => ['auth', 'wiki.lock']]);
    $this->post('Edit/Preview', ['as' => 'preview', 'uses' => 'SubmitController@preview', 'middleware' => 'auth']);

    $this->post('Upload', ['uses' => 'AttachmentController@upload', 'as' => 'upload']);
    $this->post('Paste', ['uses' => 'AttachmentController@paste', 'as' => 'paste']);

    $this->get('Purge/{wiki}', [
        'as'         => 'purge',
        'uses'       => 'PurgeController@index',
        'middleware' => ['auth', 'can:wiki-admin'],
    ]);

    $this->get('Clone/{wiki}', [
        'as'         => 'clone',
        'uses'       => 'CloneController@index',
        'middleware' => ['auth', 'can:wiki-admin'],
    ]);

    $this->post('Clone/{wiki}', [
        'uses'       => 'CloneController@save',
        'middleware' => ['auth', 'can:wiki-admin'],
    ]);

    $this->get('Move/{wiki}', [
        'as'         => 'move',
        'uses'       => 'MoveController@index',
        'middleware' => ['auth', 'can:wiki-admin'],
    ]);

    $this->post('Move/{wiki}', [
        'uses'       => 'MoveController@save',
        'middleware' => ['auth', 'can:wiki-admin'],
    ]);

    $this->post('Delete/{wiki}', [
        'as'         => 'delete',
        'uses'       => 'DeleteController@delete',
        'middleware' => ['auth', 'can:wiki-admin'],
    ]);

    $this->post('Unlink/{wiki}', [
        'as'         => 'unlink',
        'uses'       => 'DeleteController@unlink',
        'middleware' => ['auth', 'can:wiki-admin'],
    ]);

    $this->post('Restore/{id}', [
        'as'         => 'restore',
        'uses'       => 'RestoreController@index',
        'middleware' => ['auth', 'can:wiki-admin'],
    ]);

    $this->post('Comment/Save/{wiki}/{id?}', [
        'as'         => 'comment.save',
        'uses'       => 'CommentController@save',
        'middleware' => 'auth',
    ]);

    $this->post('Comment/Delete/{wiki}/{id?}', [
        'as'         => 'comment.delete',
        'uses'       => 'CommentController@delete',
        'middleware' => 'auth',
    ]);

    $this->post('Wiki/Subscribe/{wiki}', [
        'uses'       => 'SubscribeController@index',
        'as'         => 'subscribe',
        'middleware' => 'auth',
    ]);

    $this->get('Log/{wiki}', ['uses' => 'LogController@index', 'as' => 'log']);
    $this->get('Download/{wiki}/{id}', ['uses' => 'AttachmentController@download', 'as' => 'download']);
    $this->get('Blog', ['uses' => 'BlogController@index', 'as' => 'blog']);
    $this->get('Kategorie', ['uses' => 'HomeController@index', 'as' => 'home']);

    $this->get('{path}', [
        'as'         => 'show',
        'uses'       => 'ShowController@index',
        'middleware' => ['wiki.access:wiki-admin', 'page.hit', 'wiki.legacy'],
    ]);
});
