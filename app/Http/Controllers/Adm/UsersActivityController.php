<?php
namespace Coyote\Http\Controllers\Adm;

use Carbon\Carbon;
use Coyote\Domain\Administrator\Activity\Activity;
use Coyote\Domain\Administrator\Activity\Category;
use Coyote\Domain\Administrator\Activity\DeleteReason;
use Coyote\Domain\Administrator\Activity\Post;
use Coyote\Domain\Administrator\Mention;
use Coyote\Domain\PostStatistic;
use Coyote\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UsersActivityController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->breadcrumb->push('Użytkownicy', route('adm.users'));
    }

    public function show(Request $request, User $user): View
    {
        $mention = Mention::of($user);
        $this->breadcrumb->push($mention->mentionString(), route('adm.users.show', [$user->id]));
        $this->breadcrumb->push('Aktywność użytkownika', route('adm.users.activity', [$user->id]));
        $daysAgo = $this->daysAgo($request);
        return $this->view('adm.users.activity', [
            'user'     => $user,
            'activity' => new Activity(
                $user,
                $this->posts($user, $daysAgo),
                $this->postsCategoriesStatistic($user, $daysAgo),
                $this->deleteReasons($user, $daysAgo),
                $this->postStats($user, $daysAgo),
            ),
        ]);
    }

    private function posts(User $user, int $daysAgo): array
    {
        return \Coyote\Post::withTrashed()
            ->select(
                'posts.id', 'posts.forum_id', 'posts.topic_id',
                'posts.text', 'posts.created_at',
                'topics.slug', 'topics.title', 'topics.title',
                'forums.slug',
            )
            ->join('topics', 'topics.id', '=', 'posts.topic_id')
            ->join('forums', 'forums.id', '=', 'topics.forum_id')
            ->where('posts.user_id', $user->id)
            ->whereDate('posts.created_at', '>=', Carbon::now()->subDays($daysAgo))
            ->orderBy('posts.created_at', 'DESC')
            ->limit(10)
            ->get()
            ->map(fn(\Coyote\Post $post) => new Post(
                $post->text,
                $post->forum->slug,
                \route('forum.category', [$post->forum]),
                $post->topic->title,
                $this->canonicalLink($post),
                $post->created_at,
                $post->deleted_at !== null,
                $post->topic->first_post_id === $post->id,
            ))
            ->all();
    }

    private function deleteReasons(User $user, int $daysAgo): array
    {
        return DB::table('posts')
            ->select('posts.delete_reason', DB::raw('COUNT(*) as count'))
            ->where('posts.user_id', $user->id)
            ->whereNotNull('deleted_at')
            ->whereNot('posts.deleter_id', $user->id)
            ->whereDate('posts.created_at', '>=', Carbon::now()->subDays($daysAgo))
            ->groupBy('posts.delete_reason')
            ->get()
            ->map(fn(\stdClass $record) => new DeleteReason($record->delete_reason, $record->count))
            ->all();
    }

    private function canonicalLink(\Coyote\Post $post): string
    {
        $url = route('forum.topic', [$post->forum->slug, $post->topic->id, $post->topic->slug], absolute:false);
        return "{$url}?p={$post->id}#id{$post->id}";
    }

    private function daysAgo(Request $request): int
    {
        $key = $request->query('last') ?? 'default';
        $map = [
            'day'     => 1,
            'week'    => 7,
            'month'   => 31,
            'year'    => 365,
            'default' => 40 * 365,
        ];
        return $map[$key] ?? $map['default'];
    }

    private function postsCategoriesStatistic(User $user, int $daysAgo): array
    {
        return DB::table('posts')
            ->select('forums.slug', DB::raw('COUNT(*) as count'))
            ->join('topics', 'topics.id', '=', 'posts.topic_id')
            ->join('forums', 'forums.id', '=', 'topics.forum_id')
            ->where('posts.user_id', $user->id)
            ->whereDate('posts.created_at', '>=', Carbon::now()->subDays($daysAgo))
            ->groupBy('forums.slug')
            ->get()
            ->map(fn(\stdClass $record) => new Category($record->slug, $record->count))
            ->all();
    }

    private function postStats(User $user, int $daysAgo): PostStatistic
    {
        $query = DB::table('posts')
            ->where('posts.user_id', $user->id)
            ->whereDate('posts.created_at', '>=', Carbon::now()->subDays($daysAgo));

        return new PostStatistic(
            $query->clone()->count(),
            $query->clone()->where('posts.deleter_id', $user->id)->count(),
            $query->clone()->whereNot('posts.deleter_id', $user->id)->count(),
        );
    }
}
