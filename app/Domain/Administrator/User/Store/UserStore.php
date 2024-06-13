<?php
namespace Coyote\Domain\Administrator\User\Store;

use Carbon\Carbon;
use Coyote\Domain\PostStatistic;
use Coyote\Microblog;
use Coyote\Post;
use Coyote\User;
use Illuminate\Support\Facades\DB;

class UserStore
{
    public function __construct(private User $user, private Carbon $subDays)
    {
    }

    public function reportReasons(): array
    {
        $records = DB::select('SELECT flag_types.name, count(*)
            FROM "flag_resources"
                     LEFT JOIN "posts"         ON "posts"."id"         = "flag_resources"."resource_id" and "flag_resources"."resource_type" = ?
                     LEFT JOIN "microblogs"    ON "microblogs"."id"    = "flag_resources"."resource_id" and "flag_resources"."resource_type" = ?
                     LEFT JOIN "post_comments" ON "post_comments"."id" = "flag_resources"."resource_id" and "flag_resources"."resource_type" = ?
                     INNER JOIN "flags"    ON "flags"."id"  = "flag_resources"."flag_id"
                     INNER JOIN flag_types ON flags.type_id = flag_types.id
            WHERE flags.created_at > ? AND (posts.user_id = ? OR microblogs.user_id = ? OR post_comments.user_id = ?)
            GROUP by flag_types.name',
            [Post::class, Microblog::class, Post\Comment::class, $this->subDays, $this->user->id, $this->user->id, $this->user->id]);
        return \array_map(fn(\stdClass $record) => new ReportReason($record->name, $record->count), $records);
    }

    public function deleteReasons(): array
    {
        return DB::table('posts')
            ->select('posts.delete_reason', DB::raw('COUNT(*) as count'))
            ->where('posts.user_id', $this->user->id)
            ->whereNotNull('deleted_at')
            ->whereNot('posts.deleter_id', $this->user->id)
            ->whereDate('posts.created_at', '>=', $this->subDays)
            ->groupBy('posts.delete_reason')
            ->get()
            ->map(fn(\stdClass $record) => new DeleteReason($record->delete_reason, $record->count))
            ->all();
    }

    public function postsCategoriesStatistic(): array
    {
        return DB::table('posts')
            ->select('forums.slug', DB::raw('COUNT(*) as count'))
            ->join('topics', 'topics.id', '=', 'posts.topic_id')
            ->join('forums', 'forums.id', '=', 'topics.forum_id')
            ->where('posts.user_id', $this->user->id)
            ->whereDate('posts.created_at', '>=', $this->subDays)
            ->groupBy('forums.slug')
            ->get()
            ->map(fn(\stdClass $record) => new Category($record->slug, $record->count))
            ->all();
    }

    public function postStats(): PostStatistic
    {
        $query = DB::table('posts')
            ->where('posts.user_id', $this->user->id)
            ->whereDate('posts.created_at', '>=', $this->subDays);
        return new PostStatistic(
            $query->clone()->count(),
            $query->clone()->where('posts.deleter_id', $this->user->id)->count(),
            $query->clone()->whereNot('posts.deleter_id', $this->user->id)->count(),
        );
    }
}
