<?php
$I = new FunctionalTester($scenario);
$I->wantTo('test access forum only for admin users');

$forum = $I->createForum(['name' => 'Admin forum', 'slug' => 'Admin_forum']);

$group = \Coyote\Group::first();

// tabela nie ma klucza glownego, dlatego tworzymy przez model poniewaz codeception
// zawsze zaklada ze jest klucz "id"
\Coyote\Forum\Access::create(['forum_id' => $forum->id, 'group_id' => $group->id]);
$row = $I->grabRecord('Coyote\Forum\Access', ['forum_id' => $forum->id]);

$I->assertEquals($group->id, $row->group_id);

$user = $I->createUser();

$I->amLoggedAs($user);

$I->amOnRoute('forum.home');
$I->seeAuthentication();
$I->see($user->name);
$I->dontSee($forum->name);
