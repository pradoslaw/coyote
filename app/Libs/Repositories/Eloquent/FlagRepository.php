<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\FlagRepositoryInterface;

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
     * @param $value
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
                    ->whereRaw("metadata->>'topic_id' IN(" . implode(',', array_map([&$this, 'strVal'], $topicsId)) . ")")
                    ->get()
                    ->lists('url', 'topic_id');
    }

    /**
     * @param array $postsId
     * @return mixed
     */
    public function takeForPosts(array $postsId)
    {
        return $this->model->selectRaw("flags.*, metadata->>'post_id' AS post_id, users.name AS user_name, flag_types.name")
                    ->join('flag_types', 'flag_types.id', '=', 'type_id')
                    ->join('users', 'users.id', '=', 'user_id')
                    ->whereRaw("metadata->>'post_id' IN(" . implode(',', array_map([&$this, 'strVal'], $postsId)) . ")")
                    ->get()
                    ->groupBy('post_id');
    }

    /**
     * @param $key
     * @param $value
     */
    public function deleteBy($key, $value)
    {
        $this->model->whereRaw("metadata->>'$key' = ?", [$value])->delete();
    }
}
