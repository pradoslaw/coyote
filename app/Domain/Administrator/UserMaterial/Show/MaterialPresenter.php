<?php
namespace Coyote\Domain\Administrator\UserMaterial\Show;

use Carbon\Carbon;
use Coyote\Domain\Administrator\AvatarCdn;
use Coyote\Domain\Administrator\UserMaterial\List\View\Time;
use Coyote\Domain\Administrator\UserMaterial\Show\View\HistoryItem;
use Coyote\Domain\Administrator\UserMaterial\Show\View\Link;
use Coyote\Domain\Administrator\UserMaterial\Show\View\Material;
use Coyote\Domain\Administrator\UserMaterial\Show\View\Person;
use Coyote\Domain\Administrator\View\Mention;
use Coyote\Models\Flag\Resource;
use Coyote\Post;

class MaterialPresenter
{
    public function __construct(
        private Time      $time,
        private AvatarCdn $cdn,
    )
    {
    }

    public function post(int $postId): Material
    {
        /** @var Post $post */
        $post = Post::query()->withTrashed()->findOrFail($postId);
        $postId = $post->id;
        $forumSlug = $post->forum->slug;
        $createdAt = $post->created_at;
        $contentMarkdown = $post->text;
        $userId = $post->user_id;
        $username = $post->user->name;
        $avatarUrl = $post->user->photo->getFilename();
        $topicTitle = $post->topic->title;
        $topicId = $post->topic_id;
        $topicSlug = $post->topic->slug;
        $deletedAt = $post->deleted_at;

        /** @var Resource[] $resources */
        $resources = \Coyote\Models\Flag\Resource::query()
            ->with(['flag', 'flag.type', 'flag.moderator'])
            ->where('resource_id', $postId)
            ->where('resource_type', Post::class)
            ->get();

        $historyItems = [];
        if ($deletedAt !== null) {
            $historyItems[] = new HistoryItem(
                $post->deleter ? new Mention($post->deleter_id, $post->deleter->name) : null,
                $this->time->date(new Carbon($deletedAt)),
                'delete',
                'post',
                null,
            );
        }

        foreach ($resources as $resource) {
            $flag = $resource->flag;

            $historyItems[] = new HistoryItem(
                new Mention($flag->user_id, $flag->user->name),
                $this->time->date($flag->created_at),
                'report',
                $flag->type->name,
                $resource->text);
            if ($flag->deleted_at) {
                $historyItems[] = new HistoryItem(
                    new Mention($flag->moderator_id, $flag->moderator->name),
                    $this->time->date(new Carbon($flag->deleted_at, 'Europe/Warsaw')),
                    'close-report',
                    $flag->type->name,
                    '',
                );
            }
        }

        \uSort($historyItems, function (HistoryItem $a, HistoryItem $b): int {
            return $b->createdAt->timestamp() - $a->createdAt->timestamp();
        });

        return new Material(
            new Link(
                route('forum.category', [$forumSlug]),
                $forumSlug,
            ),
            new Link(
                route('forum.topic', [$forumSlug, $topicId, $topicSlug]),
                $topicTitle,
            ),
            route('forum.topic', [$forumSlug, $topicId, $topicSlug]) . '?p=' . $postId . '#id' . $postId,
            $this->time->date($createdAt),
            new Person(
                $userId,
                $username,
                $this->cdn->avatar($avatarUrl),
            ),
            $contentMarkdown,
            [
                ...$historyItems,
                new HistoryItem(
                    new Mention($userId, $username),
                    $this->time->date($createdAt),
                    'create',
                    'post',
                    null,
                ),
            ],
        );
    }
}
