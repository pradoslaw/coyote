<?php declare(strict_types=1);

namespace Coyote\Services\Elasticsearch;

use Illuminate\Http\Request;

class SearchOptions
{
    const SCORE = 'score';
    const DATE = 'date';

    /**
     * @var string|null
     */
    public $query;

    /**
     * @var string|null
     */
    public $model;

    /**
     * @var string[]|null
     */
    public $categories = [];

    /**
     * @var string|null
     */
    public $sort;

    public function __construct(Request $request, string $model = null)
    {
        $this->query = $request->input('q');
        $this->model = $request->input('model');
        $this->categories = $request->input('categories');
        $this->sort = $request->input('sort');
    }

    public function getParams(): array
    {
        return array_filter(['q' => $this->query, 'model' => class_basename($this->model), 'categories' => $this->categories, 'sort' => $this->sort]);
    }
}
