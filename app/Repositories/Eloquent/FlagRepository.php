<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\FlagRepositoryInterface;
use Illuminate\Database\Query\Builder;

class FlagRepository extends Repository implements FlagRepositoryInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return 'Coyote\Flag';
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
                    ->pluck('url', 'topic_id');
    }

    public function findAllByModel(string $model, array $ids)
    {
        $key = strtolower(class_basename($model)) . '_id';

        return $this->build($key, $ids)->get();
    }

    /**
     * @param array $postsId
     * @return mixed
     */
    public function takeForPosts(array $postsId)
    {
        return $this->build('post_id', $postsId)->get()->groupBy('post_id');
    }

    /**
     * @param int $jobId
     * @return mixed
     */
    public function takeForJob($jobId)
    {
        return $this->build('job_id', [$jobId])->first();
    }

    /**
     * @param int $wikiId
     * @return mixed
     */
    public function takeForWiki($wikiId)
    {
        return $this->build('wiki_id', [$wikiId])->first();
    }

    /**
     * @param $key
     * @param $value
     * @param int|null $userId
     */
    public function deleteBy($key, $value, $userId = null)
    {
        $path = "metadata->>'$key' = ?";

        if ($userId !== null) {
            $this->model->whereRaw($path, [$value])->update(['moderator_id' => $userId]);
        }

        $this->model->whereRaw($path, [$value])->delete();
    }

    /**
     * @param string $index
     * @param mixed $data
     * @return Builder
     */
    private function build($index, $data)
    {
        $data = $this->join($data);

        return $this
            ->model
            ->select(['flags.*', 'users.name AS user_name', 'flag_types.name'])
                ->join('flag_types', 'flag_types.id', '=', 'type_id')
                ->join('users', 'users.id', '=', 'user_id')
            ->addSelect($this->raw("metadata->>'$index' AS metadata_id"))
            ->whereRaw("metadata->>'$index' IN($data)");
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
     * @param array $data
     * @return string
     */
    private function join(array $data)
    {
        return implode(',', array_map([&$this, 'strVal'], $data));
    }
}
