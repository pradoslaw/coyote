<?php
namespace Coyote\Http\Controllers\Adm;

use Coyote\Domain\Administrator\Activity\Activity;
use Coyote\Domain\Administrator\Activity\Date;
use Coyote\Domain\Administrator\Activity\Post;
use Coyote\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UsersActivityController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->breadcrumb->push('Użytkownicy', route('adm.users'));
    }

    public function show(User $user): View
    {
        $this->breadcrumb->push($user->name, route('adm.users.show', [$user->id]));
        $this->breadcrumb->push('Aktywność użytkownika', route('adm.users.activity', [$user->id]));
        return $this->view('adm.users.activity', [
            'user'     => $user,
            'activity' => new Activity(
                $user,
                $this->postDates($user),
                $this->posts($user),
            ),
        ]);
    }

    private function postDates(User $user): array
    {
        return $this->tableSegments($user, 'posts');
    }

    private function tableSegments(User $user, string $table): array
    {
        return $this->segments(Db::table($table)
            ->where('user_id', $user->id)
            ->selectRaw("Date_Trunc('month', created_at) AS date, Count(id) AS count")
            ->groupByRaw('date')
            ->get()
            ->all());
    }

    private function posts(User $user): array
    {
        return \Coyote\Post::query()
            ->where('user_id', $user->id)
            ->join('topics', 'topics.id', '=', 'posts.topic_id')
            ->join('forums', 'forums.id', '=', 'posts.forum_id')
            ->orderBy('posts.created_at', 'DESC')
            ->limit(10)
            ->get()
            ->map(fn(\Coyote\Post $post) => new Post(
                $post->text,
                $post->forum->slug,
                \route('forum.category', [$post->forum]),
                $post->topic->title,
                $post->created_at,
            ))
            ->all();
    }

    private function segments(array $records): array
    {
        return \array_map(
            fn(\stdClass $segment): array => [new Date($segment->date), $segment->count],
            $records);
    }
}
