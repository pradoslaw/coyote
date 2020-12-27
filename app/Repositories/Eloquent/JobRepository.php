<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Feature;
use Coyote\Models\Job\Draft;
use Coyote\Repositories\Contracts\JobRepositoryInterface;
use Coyote\Job;
use Coyote\Repositories\Contracts\SubscribableInterface;
use Coyote\Str;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\JoinClause;

/**
 * @method mixed search(\Coyote\Services\Elasticsearch\QueryBuilderInterface $queryBuilder)
 * @method $this withTrashed()
 */
class JobRepository extends Repository implements JobRepositoryInterface, SubscribableInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return 'Coyote\Job';
    }

    /**
     * @inheritdoc
     */
    public function findManyWithOrder(array $ids)
    {
        $values = [];

        foreach ($ids as $key => $id) {
            $values[] = "($id,$key)";
        }

        $this->applyCriteria();

        $result = $this
            ->model
            ->addSelect('jobs.*')
            ->join($this->raw('(VALUES ' . implode(',', $values) . ') AS x (id, ordering)'), 'jobs.id', '=', 'x.id')
            ->orderBy('x.ordering')
            ->get();

        $this->resetModel();

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getPublished($userId)
    {
        $this->applyCriteria();

        $result = $this
            ->model
            ->select('jobs.*')
            ->where('jobs.user_id', $userId)
            ->orderBy('jobs.id', 'DESC')
            ->get();

        $this->resetModel();

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function subscribes($userId)
    {
        $this->applyCriteria();

        $result = $this
            ->model
            ->select(['jobs.*'])
            ->join('subscriptions', function (JoinClause $join) use ($userId) {
                $join->on('subscriptions.resource_id', '=', 'jobs.id')->where('subscriptions.resource_type', Job::class)->where('subscriptions.user_id', $userId);
            })
            ->get();

        $this->resetModel();

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getPopularTags($limit = 500)
    {
        return $this
            ->getTagsQueryBuilder()
            ->orderBy($this->raw('COUNT(*)'), 'DESC')
            ->limit($limit)
            ->get()
            ->pluck('name');
    }

    /**
     * @inheritdoc
     */
    public function getDefaultFeatures($userId)
    {
        $sub = $this->toSql(
            $this->model->select('id')->where('user_id', $userId)->orderBy('id', 'DESC')->limit(1)
        );

        return $this
            ->app
            ->make(Feature::class)
            ->selectRaw(
                'features.*, COALESCE(job_features.checked, 0) AS checked, COALESCE(job_features.value, \'\') AS value'
            )
            ->leftJoin('job_features', function (JoinClause $join) use ($sub) {
                return $join->on('job_id', '=', $this->raw("($sub)"))->on('feature_id', '=', 'features.id');
            })
            ->orderBy('order')
            ->get();
    }

    /**
     * @inheritdoc
     */
    public function getTagsWeight(array $tagsId)
    {
        $this->applyCriteria();

        return $this
            ->getTagsQueryBuilder()
            ->whereIn('job_tags.tag_id', $tagsId)
            ->get()
            ->pluck('count', 'name');
    }

    /**
     * @inheritdoc
     */
    public function getSubscribed($userId)
    {
        return $this
            ->app
            ->make(Job\Subscriber::class)
            ->select(['jobs.id', 'title', 'slug', 'job_subscribers.created_at'])
            ->join('jobs', 'jobs.id', '=', 'job_subscribers.job_id')
            ->where('job_subscribers.user_id', $userId)
            ->whereNull('deleted_at')
            ->orderBy('job_subscribers.id', 'DESC')
            ->paginate();
    }

    /**
     * @inheritdoc
     */
    public function getTagSuggestions(array $tags): array
    {
        $tags = array_map(function ($tag) {
            return new Str($tag);
        }, $tags);

        $sub = $this->toSql(
            $this
                ->model
                ->select(['job_id'])
                ->from('tags')
                ->whereIn('name', $tags)
                ->join('job_tags', 'job_tags.tag_id', '=', 'tags.id')
                ->join('jobs', 'jobs.id', '=', 'job_tags.job_id') // required for eloquent's scope (deleted_at column)
        );

        return $this
            ->model
            ->select(['tags.name'])
            ->from($this->raw("($sub) AS t"))
            ->join('job_tags', 'job_tags.job_id', '=', 't.job_id')
            ->join('tags', 'tags.id', '=', 'job_tags.tag_id')
            ->join('jobs', 'jobs.id', '=', 'job_tags.job_id') // required for eloquent's scope (deleted_at column)
            ->whereNotIn('name', $tags)
            ->groupBy('tags.name')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(5)
            ->pluck('name')
            ->toArray();
    }

    /**
     * @inheritdoc
     */
    public function setDraft(int $userId, string $key, string $value): void
    {
        $this->app[Draft::class]->updateOrCreate(['user_id' => $userId, 'key' => $key], ['value' => $value]);
    }

    /**
     * @inheritdoc
     */
    public function getDraft(int $userId, string $key): ?string
    {
        return $this->app[Draft::class]->where('user_id', $userId)->where('key', $key)->value('value');
    }

    /**
     * @inheritdoc
     */
    public function forgetDraft(int $userId): void
    {
        $this->app[Draft::class]->where('user_id', $userId)->delete();
    }

    /**
     * @return mixed
     */
    private function getTagsQueryBuilder()
    {
        return $this
            ->app
            ->make(Job\Tag::class)
            ->select(['name', $this->raw('COUNT(*) AS count')])
            ->join('tags', 'tags.id', '=', 'tag_id')
            ->join('jobs', 'jobs.id', '=', 'job_id')
                ->whereNull('jobs.deleted_at')
                ->whereNull('tags.deleted_at')
                ->where('deadline_at', '>', $this->raw('NOW()'))
            ->groupBy('name');
    }
}
