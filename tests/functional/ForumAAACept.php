<?php
$I = new FunctionalTester($scenario);
$I->wantTo('Visit website for the first time and see if there are new topics.');

$I->amOnRoute('home');
$I->logout();
$I->dontSeeAuthentication();

DB::table('sessions')->truncate();
DB::table('session_log')->truncate();

$I->resetCookie('coyote_session');
$I->dontSeeCookie('coyote_session');

$I->dontSeeRecord('sessions');
$I->dontSeeRecord('session_log');

$forum = $I->createForum();
$topic = $I->createTopic(['forum_id' => $forum->id]);

$I->createPost(['forum_id' => $forum->id, 'topic_id' => $topic->id]);

sleep(1);
$I->amOnRoute('forum.home');

$sessionId = $I->getApplication()['request']->session()->getId();
$I->seeRecord('sessions', ['id' => $sessionId]);
$I->dontSeeRecord('session_log', ['id' => $sessionId]);

$I->seeElement('//tr[@id=\'forum-' . $forum->id . '\']/td/span[@title=\'Brak nowych postów\']');

$I->createPost(['forum_id' => $forum->id, 'topic_id' => $topic->id]);

$I->amOnRoute('forum.home');
$I->seeElement('//tr[@id=\'forum-' . $forum->id . '\']/td/a[@title=\'Kliknij, aby oznaczyć jako przeczytane\']');

$I->amOnRoute('forum.topic', [$forum->slug, $topic->id, $topic->slug]);
$I->see($topic->subject);

//        $I->amOnRoute('forum.home');
//
//        $I->seeElement('//tr[@id=\'forum-' . $forum->id . '\']/td/span[@title=\'Brak nowych postów\']');
