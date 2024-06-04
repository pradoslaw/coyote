<?php
namespace Coyote\Domain\Administrator\Report;

use Carbon\Carbon;
use Coyote\Domain\Administrator\AvatarCdn;
use Coyote\Domain\Administrator\View\Date;
use Illuminate\Support\Facades\DB;

readonly class EloquentStore
{
    public function __construct(private AvatarCdn $cdn)
    {
    }

    /**
     * @return ReportedPost[]
     */
    public function reportedPosts(): array
    {
        $query = DB::select(<<<query
            SELECT 
                  posts.id AS post_id,
                  MIN(posts.text)           AS post,
                  MIN(posts.forum_id)       AS forum_id,
                  MIN(forums.slug)          AS forum_slug,
                  MIN(authors.id)           AS author_id,
                  MIN(authors.photo)        AS author_photo,
                  MIN(authors.name)         AS author_name,
                  JSON_AGG(reporters.id)    AS reporter_ids,
                  JSON_AGG(reporters.name)  AS reporter_names,
                  JSON_AGG(flag_types.name) AS report_types,
                  MIN(flags.created_at)     AS created_at,
                  MAX(flags.created_at)     AS updated_at
            FROM flags
                 JOIN users AS reporters ON reporters.id = flags.user_id
                 JOIN flag_types ON flags.type_id = flag_types.id
                 JOIN flag_resources ON flags.id = flag_resources.flag_id
                 JOIN posts ON posts.id = flag_resources.resource_id
                 JOIN users AS authors ON authors.id = posts.user_id
                 JOIN forums ON forums.id = posts.forum_id
            WHERE flag_resources.resource_type in ('Coyote\Post')
            GROUP BY posts.id, posts.text
            ORDER BY MAX(flags.created_at) DESC
            LIMIT 20;
            query,);

        return \array_map(
            fn(\stdClass $record) => new \Coyote\Domain\Administrator\Report\ReportedPost(
                $record->post_id,
                $record->post,
                $record->author_id,
                $record->author_name,
                $this->cdn->avatar($record->author_photo),
                \array_unique(\json_decode($record->reporter_ids)),
                \array_unique(\json_decode($record->reporter_names)),
                \array_values(\array_unique(\json_decode($record->report_types))),
                new Date(new Carbon($record->created_at)),
                new Date(new Carbon($record->updated_at)),
                $record->forum_id,
                $record->forum_slug,
            ),
            $query,
        );
    }

    public function reportHistory(int $postId): array
    {
        $query = DB::select(<<<'query'
            select flags.id         AS report_id,
                   reporters.id     AS reporter_id,
                   reporters.name   AS reporter_name,
                   flag_types.name  AS type,
                   flags.text       AS report_note,
                   flags.created_at AS reported_at
            from flags
                     join users AS reporters on reporters.id = flags.user_id
                     join flag_types ON flags.type_id = flag_types.id
                     join flag_resources ON flags.id = flag_resources.flag_id
            where flag_resources.resource_type in ('Coyote\Post')
              AND flag_resources.resource_id = ?
            order by flags.created_at DESC;
            query, [$postId]);
        return \array_map(
            fn(\stdClass $record) => new \Coyote\Domain\Administrator\Report\Report(
                $record->reporter_id,
                $record->reporter_name,
                $record->type,
                $record->report_note,
                new Date(new Carbon($record->reported_at)),
            ),
            $query,
        );
    }

    public function reportedPostById(int $postId): ReportedPost
    {
        return \array_values(\array_filter(
            $this->reportedPosts(),
            fn(ReportedPost $r) => $r->id === $postId,
        ))[0];
    }
}
