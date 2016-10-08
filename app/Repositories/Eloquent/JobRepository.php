<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\JobRepositoryInterface;
use Coyote\Job;
use Coyote\Repositories\Contracts\SubscribableInterface;

/**
 * @method mixed search(array $body)
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
     * @param int $id
     * @return mixed
     */
    public function findById($id)
    {
        $this->applyCriteria();

        return $this
            ->model
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
     * @param string $city
     * @return int
     */
    public function countOffersInCity(string $city)
    {
        return $this
            ->model
            ->join('job_locations', 'jobs.id', '=', 'job_locations.job_id')
            ->where('city', $city)
            ->where('deadline_at', '>', $this->raw('NOW()'))
            ->count();
    }

    /**
     * Get subscribed job offers for given user id
     *
     * @param int $userId
     * @return mixed
     */
    public function subscribes($userId)
    {
        return $this
            ->model
            ->select(['jobs.*', 'firms.name AS firm.name', 'firms.logo AS firm.logo', 'currencies.name AS currency_name'])
            ->join('job_subscribers', function ($join) use ($userId) {
                $join->on('job_id', '=', 'jobs.id')->on('job_subscribers.user_id', '=', $this->raw($userId));
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
            ->getQuery()
            ->orderBy($this->raw('COUNT(*)'), 'DESC')
            ->limit($limit)
            ->get()
            ->lists('count', 'name');
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
            ->getQuery()
            ->whereIn('job_tags.tag_id', $tagsId)
            ->get()
            ->lists('count', 'name');
    }

    /**
     * @return mixed
     */
    private function getQuery()
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

    /**
     * @param int $userId
     * @return mixed
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
}
