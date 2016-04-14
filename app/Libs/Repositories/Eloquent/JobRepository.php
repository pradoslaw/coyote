<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\JobRepositoryInterface;

class JobRepository extends Repository implements JobRepositoryInterface
{
    /**
     * @return \Coyote\Job
     */
    public function model()
    {
        return 'Coyote\Job';
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function findById($id)
    {
        $this->applyCriteria();

        return $this->model
                    ->select(['jobs.*', 'countries.name AS country_name', 'currencies.name AS currency_name'])
                    ->leftJoin('countries', 'countries.id', '=', 'country_id')
                    ->leftJoin('currencies', 'currencies.id', '=', 'currency_id')
                    ->findOrFail($id);
    }

    /**
     * @return int
     */
    public function count()
    {
        $this->applyCriteria();
        return $this->model->count();
    }

    /**
     * Get subscribed job offers for given user id
     *
     * @param int $userId
     * @return mixed
     */
    public function subscribes($userId)
    {
        return $this->model
                    ->select(['jobs.*', 'firms.name AS firm_name', 'firms.logo', 'currencies.name AS currency_name'])
                    ->join('job_subscribers', function ($join) use ($userId) {
                        $join->on('job_id', '=', 'jobs.id')->on('job_subscribers.user_id', '=', \DB::raw($userId));
                    })
                    ->leftJoin('firms', 'firms.id', '=', 'firm_id')
                    ->join('currencies', 'currencies.id', '=', 'currency_id')
                    ->with('locations')
                    ->get();
    }

    /**
     * @param int $limit
     * @return mixed
     */
    public function getPopularTags($limit = 1000)
    {
        return $this
                ->app->make('Coyote\Job\Tag')
                ->select(['name', \DB::raw('COUNT(*) AS count')])
                ->join('tags', 'tags.id', '=', 'tag_id')
                ->join('jobs', 'jobs.id', '=', 'job_id')
                    ->whereNull('jobs.deleted_at')
                    ->whereNull('tags.deleted_at')
                ->groupBy('name')
                ->orderBy(\DB::raw('COUNT(*)'), 'DESC')
                ->limit($limit)
                ->get()
                ->toArray();
    }

    /**
     * Return tags with job offers counter
     *
     * @param array $tagsId
     * @return mixed
     */
    public function getTagsWeight(array $tagsId)
    {
        $this->applyCriteria();

        return $this
                ->app->make('Coyote\Job\Tag')
                ->select(['name', \DB::raw('COUNT(*) AS count')])
                ->join('tags', 'tags.id', '=', 'tag_id')
                ->join('jobs', 'jobs.id', '=', 'job_id')
                ->whereIn('job_tags.tag_id', $tagsId)
                    ->whereNull('jobs.deleted_at')
                    ->whereNull('tags.deleted_at')
                ->groupBy('name')
                ->get();
    }
}
