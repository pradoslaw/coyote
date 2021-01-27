<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Feature;
use Coyote\Repositories\Contracts\JobRepositoryInterface;
use Coyote\Job;
use Coyote\Str;
use Illuminate\Database\Query\JoinClause;

/**
 * @method mixed search(\Coyote\Services\Elasticsearch\QueryBuilderInterface $queryBuilder)
 * @method $this withTrashed()
 */
class JobRepository extends Repository implements JobRepositoryInterface
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
    public function published($userId)
    {
        $this->applyCriteria();

        $result = $this
            ->model
            ->select('jobs.*')
            ->where('jobs.user_id', $userId)
            ->orderBy('jobs.id', 'DESC')
            ->paginate();

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
