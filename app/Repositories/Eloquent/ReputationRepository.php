<?php

namespace Coyote\Repositories\Eloquent;

use Carbon\Carbon;
use Coyote\Repositories\Contracts\ReputationRepositoryInterface;
use Coyote\Reputation\Type;
use Coyote\User;

class ReputationRepository extends Repository implements ReputationRepositoryInterface
{
    /**
     * @return string
     */
    public function model()
    {
        return \Coyote\Reputation::class;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultValue($typeId)
    {
        return Type::find($typeId, ['points'])['points'];
    }

    /**
     * @param int $userId
     * @param int $offset
     * @param int $limit
     * @return mixed
     */
    public function history($userId, $offset = 0, $limit = 10)
    {
        return $this->model->select()
            ->join('reputation_types', 'reputation_types.id', '=', $this->raw('reputations.type_id'))
            ->where('user_id', $userId)
            ->orderBy('reputations.id', 'DESC')
            ->skip($offset)
            ->limit($limit)
            ->get();
    }

    /**
     * Get usr reputation for chart.
     *
     * @param int $userId
     * @return array
     */
    public function chart($userId)
    {
        $dt = new Carbon('-1 year');
        $interval = $dt->diffInMonths(new Carbon());

        $sql = $this
            ->model
            ->selectRaw(
                'extract(MONTH FROM created_at) AS month, extract(YEAR FROM created_at) AS year, SUM(value) AS value',
            )
            ->whereRaw("user_id = $userId")
            ->whereRaw("created_at >= '$dt'")
            ->groupBy('year')
            ->groupBy('month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $result = [];
        foreach ($sql as $row) {
            $result[sprintf('%d-%02d', $row['year'], $row['month'])] = $row->toArray();
        }

        $rowset = [];
        $months = ['styczeń', 'luty', 'marzec', 'kwiecień', 'maj', 'czerwiec', 'lipiec', 'sierpień', 'wrzesień', 'październik', 'listopad', 'grudzień'];
        for ($i = 0; $i <= $interval; $i++) {
            $key = $dt->format('Y-m');
            $label = $months[$dt->month - 1] . ' ' . $dt->format('Y');

            if (!isset($result[$key])) {
                $rowset[] = ['value' => 0, 'year' => $dt->format('Y'), 'month' => $dt->format('n'), 'label' => $label];
            } else {
                $rowset[] = array_merge($result[$key], ['label' => $label]);
            }

            $dt->addMonth();
        }

        return $rowset;
    }

    /**
     * Gets total reputation ranking
     *
     * @param int $limit
     * @return mixed
     */
    public function total($limit = 3)
    {
        return $this->percentage(User::orderBy('reputation', 'DESC')->take($limit)->get());
    }

    /**
     * Gets monthly reputation ranking
     *
     * @param int $limit
     * @return mixed
     */
    public function monthly($limit = 3)
    {
        return $this->getReputation(date('Y-m-1 00:00:00'), $limit);
    }

    /**
     * Gets yearly reputation ranking
     *
     * @param int $limit
     * @return mixed
     */
    public function yearly($limit = 3)
    {
        return $this->getReputation(date('Y-1-1 00:00:00'), $limit);
    }

    /**
     * @param string $dateTime
     * @param integer $limit
     * @return mixed
     */
    private function getReputation($dateTime, $limit)
    {
        $from = $this
            ->model
            ->selectRaw('user_id, GREATEST(0, SUM(value)) AS reputation')
            ->whereRaw("reputations.created_at >= '$dateTime'")
            ->orderBy('reputation', 'DESC')
            ->limit($limit)
            ->groupBy('user_id')
            ->toSql();

        $result = $this
            ->model
            ->select(['users.id', 'name', 'photo', 't.reputation'])
            ->from($this->raw("($from) AS t"))
            ->join('users', 'users.id', '=', 'user_id')
            ->get();

        return $this->percentage($result);
    }

    /**
     * Calculates percentage value of user ranking
     *
     * @param $result
     * @return mixed
     */
    private function percentage($result)
    {
        $max = $result->count() > 0 ? $result->first()->reputation : 0;

        foreach ($result as $row) {
            $row->percentage = $max > 0 ? ($row->reputation * 1.0 / $max) * 100 : 0;
        }

        return $result;
    }
}
