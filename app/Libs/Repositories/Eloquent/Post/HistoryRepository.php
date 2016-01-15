<?php

namespace Coyote\Repositories\Eloquent\Post;

use Coyote\Repositories\Contracts\Post\HistoryRepositoryInterface;
use Coyote\Repositories\Eloquent\Repository;
use Coyote\Post;
use Coyote\Topic;
use Coyote\Post\History;

class HistoryRepository extends Repository implements HistoryRepositoryInterface
{
    /**
     * @return \Coyote\Post\History
     */
    public function model()
    {
        return 'Coyote\Post\History';
    }

    /**
     * Add initial entries to the post history
     *
     * @param int $userId
     * @param Post $post
     * @param Topic $topic
     */
    public function initial($userId, Post $post, Topic $topic = null)
    {
        $guid = $this->guid();

        $this->add(History::INITIAL_BODY, $post->id, $userId, $post->text, $guid);

        if ($topic) {
            $this->add(History::INITIAL_SUBJECT, $post->id, $userId, $topic->subject, $guid);

            $tags = $topic->tags();
            if ($tags->count()) {
                $this->add(History::INITIAL_TAGS, $post->id, $userId, $tags->lists('name')->toJson(), $guid);
            }
        }
    }

    /**
     * @param int $typeId
     * @param int $postId
     * @param int $userId
     * @param string $data
     * @param string|null $guid
     */
    public function add($typeId, $postId, $userId, $data, $guid = null)
    {
        if (!$guid) {
            $guid = $this->guid();
        }

        $this->model->create([
            'type_id' => $typeId, 'post_id' => $postId, 'user_id' => $userId, 'data' => $data, 'guid' => $guid
        ]);
    }

    /**
     * @return string
     */
    private function guid()
    {
        $data = openssl_random_pseudo_bytes(16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
