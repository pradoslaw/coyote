<?php
namespace Coyote\Repositories\Eloquent;

use Carbon\Carbon;
use Coyote\Reputation\Type;
use Illuminate\Database\Eloquent;

class ReputationRepository extends Repository
{
    public function model(): string
    {
        return \Coyote\Reputation::class;
    }

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

    public function reputationSince(string $dateTime, int $limit): Eloquent\Collection
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
            ->orderBy('reputation', 'DESC')
            ->get();
        return $this->percentage($result);
    }

    private function percentage(Eloquent\Collection $result): Eloquent\Collection
    {
        $max = $result->count() > 0 ? $result->pluck('reputation')->max() : 0;
        foreach ($result as $row) {
            $row->percentage = $max > 0 ? ($row->reputation * 1.0 / $max) * 100 : 0;
        }
        return $result;
    }
}
