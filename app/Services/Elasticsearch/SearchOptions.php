<?php declare(strict_types=1);

namespace Coyote\Services\Elasticsearch;

use Coyote\Services\Arrayable\ToArray;
use Illuminate\Http\Request;

class SearchOptions
{
    use ToArray;

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

    /**
     * @var string|null
     */
    public $user;

    /**
     * @var int|null
     */
    public $from;

    public function __construct(Request $request)
    {
        $this->query = $request->input('q');
        $this->model = class_basename($request->input('model'));
        $this->categories = $request->input('categories');
        $this->user = $request->input('user');
        $this->sort = $request->input('sort');
        $this->from = 10 * ($request->input('page', 1) - 1);
    }

    public function getParams(): array
    {
        $params = $this->toArray();
        unset($params['query']);

        return array_filter($params + ['q' => $this->query]);
    }
}
