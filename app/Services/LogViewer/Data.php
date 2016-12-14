<?php

namespace Coyote\Services\LogViewer;

use Illuminate\Pagination\LengthAwarePaginator;

class Data
{
    /**
     * @var int
     */
    protected $count;

    /**
     * @var array
     */
    protected $data;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->count = count($data);
        $this->data = $this->escape($data);
    }

    /**
     * @param string $column
     * @param string $order
     * @return $this
     */
    public function sort($column, $order)
    {
        usort($this->data, function ($item1, $item2) use ($column, $order) {
            return 'asc' == $order ? ($item1[$column] <=> $item2[$column]) : ($item2[$column] <=> $item1[$column]);
        });

        return $this;
    }

    /**
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate($perPage = 15)
    {
        return new LengthAwarePaginator($this->data, $this->count, $perPage, LengthAwarePaginator::resolveCurrentPage(), ['path' => ' ']);
    }

    /**
     * @param array $data
     * @return array
     */
    protected function escape(array $data): array
    {
        // escape stack due to security reasons. log may contain html code.
        if (isset($data['stack'])) {
            $data['stack'] = array_map('htmlspecialchars', $data['stack']);
        }

        return $data;
    }
}
