<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\FlagRepositoryInterface;
use Illuminate\Database\Query\Builder;

class FlagRepository extends Repository implements FlagRepositoryInterface
{
    /**
     * @return \Coyote\Flag
     */
    public function model()
    {
        return 'Coyote\Flag';
    }

    /**
     * @param integer $value
     * @return string
     */
    private function strVal($value)
    {
        return "'" . $value . "'";
    }

    /**
     * @param array $topicsId
     * @return mixed
     */
    public function takeForTopics(array $topicsId)
    {
        return $this->model->selectRaw("url, metadata->>'topic_id' AS topic_id")
                    ->whereRaw("metadata->>'topic_id' IN(" . $this->join($topicsId) . ")")
                    ->get()
                    ->lists('url', 'topic_id');
    }

    /**
     * @param array $postsId
     * @return mixed
     */
    public function takeForPosts(array $postsId)
    {
        return $this->build()
                    ->addSelect(\DB::raw("metadata->>'post_id' AS post_id"))
                    ->whereRaw("metadata->>'post_id' IN(" . $this->join($postsId) . ")")
                    ->get()
                    ->groupBy('post_id');
    }

    /**
     * @param int $jobId
     * @return mixed
     */
    public function takeForJob($jobId)
    {
        $jobId = $this->strVal($jobId);

        return $this->build()
                    ->addSelect(\DB::raw("metadata->>'job_id' AS job_id"))
                    ->whereRaw("metadata->>'job_id' IN($jobId)")
                    ->first();
    }

    /**
     * @param $key
     * @param $value
     */
    public function deleteBy($key, $value)
    {
        $this->model->whereRaw("metadata->>'$key' = ?", [$value])->delete();
    }

    /**
     * @return Builder
     */
    private function build()
    {
        return $this->model
                    ->select(['flags.*', 'users.name AS user_name', 'flag_types.name'])
                    ->join('flag_types', 'flag_types.id', '=', 'type_id')
                    ->join('users', 'users.id', '=', 'user_id');
    }

    /**
     * @param array $data
     * @return string
     */
    private function join(array $data)
    {
        return implode(',', array_map([&$this, 'strVal'], $data));
    }
}
