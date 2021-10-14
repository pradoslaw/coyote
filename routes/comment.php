<?php

$this->post('Comment/{comment?}', ['uses' => 'CommentController@save', 'as' => 'comment', 'middleware' => 'auth']);
$this->delete('Comment/{comment}', ['uses' => 'CommentController@delete', 'as' => 'comment.delete', 'middleware' => 'auth']);
