<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\PmRepositoryInterface;
use Coyote\Pm;
use Coyote\User;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Class PmRepository
 * @package Coyote\Repositories\Eloquent
 */
class PmRepository extends Repository implements PmRepositoryInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return Pm::class;
    }

    /**
     * Get last messages
     *
     * @param int $userId
     * @param int $limit
     * @return mixed
     */
    public function groupByAuthor($userId, $limit = 10)
    {
        return $this->prepare($userId)->limit($limit)->get();
    }

    /**
     * @param int $userId
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function lengthAwarePaginate($userId, $perPage = 10)
    {
        $count = $this->model
                ->selectRaw('COUNT(*)')
                ->where('user_id', $userId)
                ->groupBy('author_id')
                ->get()
                ->count();

        $result = $this->prepare($userId)
                ->limit($perPage)
                ->skip((request('page') - 1) * $perPage)
                ->get();

        return new LengthAwarePaginator(
            $result,
            $count,
            $perPage,
            LengthAwarePaginator::resolveCurrentPage(),
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );
    }

    /**
     * Get conversation
     *
     * @param int $userId
     * @param int $authorId
     * @param int $limit
     * @param int $offset
     * @return mixed
     */
    public function conversation($userId, $authorId, $limit = 10, $offset = 0)
    {
        $builder = $this
            ->model
            ->select([
                'pm.id',
                'pm.text_id',
                'pm.folder',
                'pm.read_at',
                $this->raw(
                    sprintf('(CASE WHEN folder = %d THEN user_id ELSE author_id END) AS user_id', Pm::SENTBOX)
                )
            ])
            ->where('user_id', $userId)
            ->where('author_id', $authorId)
            ->take($limit)
            ->skip($offset)
            ->orderBy('pm.id', 'DESC');

        $result = $this->model->select([
            'pm.*',
            'pm_text.text',
            'pm_text.created_at',
            'pm.user_id'
        ])
        ->from($this->raw('(' . $this->toSql($builder) . ') AS pm'))
        ->join('pm_text', 'pm_text.id', '=', 'text_id')
        ->with(['user' => function ($builder) {
            return $builder->select(['id', 'name', 'photo', 'is_blocked', 'deleted_at'])->withTrashed();
        }])
        ->orderBy('pm.id', 'DESC')
        ->get();

        return $result->reverse()->values();
    }

    /**
     * @inheritDoc
     */
    public function getUnreadIds(int $userId, int $authorId)
    {
        return $this
            ->model
            ->select(['id', 'text_id'])
            ->where('user_id', $userId)
            ->where('author_id', $authorId)
            ->where('folder', Pm::INBOX)
            ->whereNull('read_at')
            ->get();
    }

    /**
     * @param User $user
     * @param array $payload
     * @return Pm[]
     */
    public function submit(User $user, array $payload)
    {
        $text = Pm\Text::create(['text' => $payload['text']]);

        $fill = [
            'text_id' => $text->id
        ];

        $result = [];

        // we need to create two records. one for recipient and one for message author
        $result[Pm::INBOX] = $this->model->create($fill + ['user_id' => $payload['author_id'], 'author_id' => $user->id, 'folder' => Pm::INBOX]);
        $result[Pm::SENTBOX] = $this->model->create($fill + ['user_id' => $user->id, 'author_id' => $payload['author_id'], 'folder' => Pm::SENTBOX]);

        return $result;
    }

    /**
     * Mark notifications as read
     *
     * @param int $textId
     */
    public function markAsRead($textId)
    {
        $this->model->where('text_id', $textId)->update(['read_at' => now()]);
    }

    /**
     * @param int $userId
     * @param int $authorId
     */
    public function trash($userId, $authorId)
    {
        $this->model->where('user_id', $userId)->where('author_id', $authorId)->delete();
    }

    /**
     * Prepare statement with subquery
     *
     * @param int $userId
     * @return mixed
     */
    private function prepare($userId)
    {
        return $this
            ->model
            ->select([
                'm.id',
                'author_id AS user_id',
                'm.folder',
                'm.read_at',
                'pm_text.text',
                'pm_text.created_at'
            ])
            ->from($this->raw('(' . $this->toSql($this->subSql($userId)) . ') AS m'))
            ->join('pm_text', 'pm_text.id', '=', 'text_id')
            ->with(['user' => function ($builder) {
                return $builder->select(['id', 'name', 'photo', 'is_blocked', 'deleted_at'])->withTrashed();
            }])
            ->orderBy('m.id', 'DESC');
    }

    /**
     * Returns subquery
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function subSql($userId)
    {
        return $this
            ->model
            ->select([$this->raw('DISTINCT ON (author_id) pm.*')])
            ->where("user_id", $userId)
            ->orderBy('author_id')
            ->orderBy('pm.id', 'DESC');
    }
}
