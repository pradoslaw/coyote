<?php
namespace Coyote\Providers;

use Coyote\Events\CommentSaved;
use Coyote\Events\FirewallWasDeleted;
use Coyote\Events\FirewallWasSaved;
use Coyote\Events\ForumSaved;
use Coyote\Events\MicroblogSaved;
use Coyote\Events\PostSaved;
use Coyote\Events\StreamSaved;
use Coyote\Events\SuccessfulLogin;
use Coyote\Listeners\ActivitySubscriber;
use Coyote\Listeners\ChangeImageUrl;
use Coyote\Listeners\DispatchMicroblogNotifications;
use Coyote\Listeners\DispatchPostCommentNotification;
use Coyote\Listeners\DispatchPostNotifications;
use Coyote\Listeners\FlagSubscriber;
use Coyote\Listeners\FlushFirewallCache;
use Coyote\Listeners\IndexCategory;
use Coyote\Listeners\IndexStream;
use Coyote\Listeners\JobListener;
use Coyote\Listeners\LogSentMessage;
use Coyote\Listeners\MicroblogListener;
use Coyote\Listeners\PageSubscriber;
use Coyote\Listeners\PostListener;
use Coyote\Listeners\SendLockoutEmail;
use Coyote\Listeners\SendSuccessfulLoginEmail;
use Coyote\Listeners\SetupWikiLinks;
use Coyote\Listeners\TopicListener;
use Coyote\Listeners\UpdateOnlineStatusAndUserIp;
use Coyote\Listeners\UserSubscriber;
use Coyote\Listeners\WikiListener;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Mail\Events\MessageSending;

class EventServiceProvider extends \Illuminate\Foundation\Support\Providers\EventServiceProvider
{
    protected $listen = [
        Lockout::class            => [SendLockoutEmail::class],
        FirewallWasSaved::class   => [FlushFirewallCache::class],
        FirewallWasDeleted::class => [FlushFirewallCache::class],
        SuccessfulLogin::class    => [SendSuccessfulLoginEmail::class],
        Login::class              => [UpdateOnlineStatusAndUserIp::class],
        MessageSending::class     => [ChangeImageUrl::class, LogSentMessage::class],
        ForumSaved::class         => [IndexCategory::class],
        StreamSaved::class        => [IndexStream::class],
        PostSaved::class          => [DispatchPostNotifications::class],
        MicroblogSaved::class     => [DispatchMicroblogNotifications::class],
        CommentSaved::class       => [DispatchPostCommentNotification::class],
    ];

    protected $subscribe = [
        PageSubscriber::class,
        TopicListener::class,
        JobListener::class,
        MicroblogListener::class,
        WikiListener::class,
        SetupWikiLinks::class,
        ActivitySubscriber::class,
        UserSubscriber::class,
        FlagSubscriber::class,
        PostListener::class,
    ];
}
