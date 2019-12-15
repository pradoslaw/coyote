<?php

namespace Coyote\Repositories\Eloquent;

use Coyote\Repositories\Contracts\PmRepositoryInterface;
use Coyote\Pm;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

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
        return 'Coyote\Pm';
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
    public function talk($userId, $authorId, $limit = 10, $offset = 0)
    {
        $builder = $this
            ->model->select([
                'pm.id',
                'pm.text_id',
                'pm.folder',
                'pm.read_at',
                $this->raw(
                    sprintf('(CASE WHEN folder = %d THEN user_id ELSE author_id END) AS author_id', Pm::SENTBOX)
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
                    'pm.author_id'
//                    'users.id AS user_id',
//                    'name',
//                    $this->raw('users.deleted_at IS NULL AS is_active'),
//                    'is_blocked',
//                    'photo'
                ])
                ->from($this->raw('(' . $this->toSql($builder) . ') AS pm'))
                ->join('pm_text', 'pm_text.id', '=', 'text_id')
//                ->leftJoin('users', 'users.id', '=', 'host_id')
                ->with(['author' => function ($builder) {
                    return $builder->select(['id', 'name', 'photo', 'is_blocked', 'deleted_at'])->withTrashed();
                }])
                ->orderBy('pm.id', 'DESC')
                ->get();

        return $result->reverse()->values();
    }

    /**
     * Submit a new message
     *
     * @param \Coyote\User $user    Message author
     * @param array $payload
     * @throws \Exception
     */
    public function submit(\Coyote\User $user, array $payload)
    {
        $rootId = empty($payload['root_id']) ? dechex(mt_rand(0, 0x7fffffff)) : $payload['root_id'];

        if (!$payload['author_id']) {
            throw new \Exception('Can not get recipient ID.');
        }

        if ($user->id === $payload['author_id']) {
            throw new \Exception('Recipient ID and sender ID have the same value.');
        }

        $text = Pm\Text::create(['text' => $payload['text']]);
        $fill = [
            'root_id' => $rootId,
            'text_id' => $text->id
        ];

        // we need to create two records. one for recipient and one for message author
        $this->model->create($fill + ['user_id' => $payload['author_id'], 'author_id' => $user->id, 'folder' => Pm::INBOX]);
        $pm = $this->model->create($fill + ['user_id' => $user->id, 'author_id' => $payload['author_id'], 'folder' => Pm::SENTBOX]);

        return $pm;
    }

    /**
     * Mark notifications as read
     *
     * @param int $textId
     */
    public function markAsRead($textId)
    {
        $this->model->where('text_id', $textId)->update(['read_at' => Carbon::now()]);
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
                'author_id',
                'm.folder',
                'm.read_at',
                'pm_text.text',
                'pm_text.created_at'
            ])
            ->from($this->raw('(' . $this->toSql($this->subSql($userId)) . ') AS m'))
            ->join('pm_text', 'pm_text.id', '=', 'text_id')
            ->with(['author' => function ($builder) {
                return $builder->select(['id', 'name', 'photo', 'is_blocked', 'deleted_at'])->withTrashed();
            }])
            ->orderBy('m.id', 'DESC');
    }

    /**
     * Returns subquery
     *
     * @param int $userId
     * @return mixed
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
