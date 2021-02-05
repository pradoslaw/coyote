<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\TagRepositoryInterface;
use Coyote\Tag;
use Coyote\User\Skill;
use Illuminate\Database\Connection;

class TagRepository extends Repository implements TagRepositoryInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return 'Coyote\Tag';
    }

    /**
     * @inheritdoc
     */
    public function lookupName($name)
    {
        return $this
            ->model
            ->select(['tags.id', 'name', 'topics'])
            ->where('name', 'ILIKE', $name . '%')
            ->orderByDesc('topics')
            ->limit(100)
            ->get();
    }

    public function exists(string $name): bool
    {
        return $this
            ->model
            ->where('name', $name)
            ->exists();
    }

    /**
     * @inheritdoc
     */
    public function multiInsert(array $tags)
    {
        $ids = [];

        foreach ($tags as $name) {
            $tag = $this->model->firstOrCreate(['name' => $name]);

            $ids[] = $tag->id;
        }

        return $ids;
    }

    /**
     * @inheritdoc
     */
    public function getCategorizedTags(array $tags)
    {
        return $this
            ->model
            ->selectRaw('name, logo, COUNT(*) AS weight')
                ->join('job_tags', 'tag_id', '=', 'tags.id')
            ->whereIn('name', $tags)
            ->whereNotNull('category_id')
            ->groupBy('name')
            ->groupBy('logo')
            ->get();
    }

    public function merge(Tag $from, Tag $to)
    {
        /** @var Connection $db */
        $db = $this->app['db'];

        $select = Skill::selectRaw("$to->id, user_id, priority, \"order\"")->where('tag_id', $from->id);

        $db->table('user_skills')->insertUsing(['tag_id', 'user_id', 'priority', 'order'], $select);

        Skill::where('tag_id', $from->id)->delete();

        /////////////////////////////////////////

        $select = \Coyote\Job\Tag::selectRaw("$to->id, job_id, priority, \"order\"")->where('tag_id', $from->id);

        $db->table('job_tags')->insertUsing(['tag_id', 'job_id', 'priority', 'order'], $select);

        Skill::where('tag_id', $from->id)->delete();

        /////////////////////////////////////////

        $select = \Coyote\Topic\Tag::selectRaw("$to->id, topic_id")->where('tag_id', $from->id);

        $db->table('topic_tags')->insertUsing(['tag_id', 'topic_id'], $select);

        \Coyote\Topic\Tag::where('tag_id', $from->id)->delete();

        /////////////////////////////////////////

        $select = \Coyote\Microblog\Tag::selectRaw("$to->id, microblog_id")->where('tag_id', $from->id);

        $db->table('microblog_tags')->insertUsing(['tag_id', 'microblog_id'], $select);

        \Coyote\Microblog\Tag::where('tag_id', $from->id)->delete();
    }
}
