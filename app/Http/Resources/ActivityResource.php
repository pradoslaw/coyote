<?php
namespace Coyote\Http\Resources;

use Carbon\Carbon;
use Coyote\Forum;
use Coyote\Post;
use Coyote\Services\UrlBuilder;
use Coyote\Topic;
use Coyote\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $user_id
 * @property string $user_name
 * @property string $excerpt
 * @property string $content_type
 * @property int $content_id
 * @property Carbon $created_at
 * @property Post|Post\Comment $content
 * @property User $user
 * @property Topic $topic
 * @property Forum $forum
 */
class ActivityResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'excerpt'    => $this->excerpt,
            'created_at' => $this->created_at,
            'headline'   => $this->headline(),
            'user_id'    => $this->user_id,
            'object'     => strtolower(class_basename($this->content_type)),
            'user'       => [
                'photo' => $this->user_id ? $this->user->photo : '',
            ],
        ];
    }

    public function headline(): string
    {
        return trans('activity.headline.' . strtolower(class_basename($this->content_type)), [
            'user'  => $this->user(),
            'topic' => $this->topic(),
        ]);
    }

    public function user(): string
    {
        if ($this->user_id) {
            return link_to_route('profile', $this->user->name, ['user_trashed' => $this->user_id]);
        }
        return $this->user_name;
    }

    public function topic(): string
    {
        if ($this->content_type === Post::class) {
            $this->content->setRelations(['topic' => $this->topic, 'forum' => $this->forum]);
            return link_to(UrlBuilder::post($this->content), $this->topic->title);
        }
        $post = (new Post)
            ->forceFill(['id' => $this->content->post_id])
            ->setRelations(['topic' => $this->topic, 'forum' => $this->forum]);
        $this->content->setRelations(['post' => $post]);
        return link_to(UrlBuilder::postComment($this->content), $post->topic->title);
    }
}
