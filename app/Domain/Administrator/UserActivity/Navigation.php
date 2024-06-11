<?php
namespace Coyote\Domain\Administrator\UserActivity;

use Coyote\Domain\Administrator\View\Mention;
use Coyote\User;

readonly class Navigation
{
    public string $username;
    public string $stream;
    public string $users;
    public string $settings;
    public string $profile;
    public string $posts;
    public string $microblogs;
    public string $activity;
    public string $receivedFlags;
    public Mention $mention;

    public function __construct(User $user)
    {
        $this->username = $user->name;
        $this->stream = route('adm.stream', ['actor_displayName' => $user->name]);
        $this->users = route('adm.users');
        $this->settings = route('adm.users.save', [$user->id]);
        $this->profile = route('profile', [$user->id]);
        $this->posts = route('forum.user', [$user->id]);
        $this->microblogs = route('profile', [$user->id, 'tab' => 'Microblog']);
        $this->activity = route('adm.users.activity', [$user->id]);
        $this->receivedFlags = route('adm.flag', ['filter' => "is:reported author:$user->id"]);
        $this->mention = Mention::of($user);
    }
}
