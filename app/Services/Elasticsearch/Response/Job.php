<?php

namespace Coyote\Services\Elasticsearch\Response;

use Carbon\Carbon;
use Coyote\Services\Elasticsearch\ResponseInterface;
use Coyote\Services\Elasticsearch\Response;

class Job extends Response implements ResponseInterface
{
    /**
     * Response constructor.
     * @param array $response
     */
    public function __construct($response)
    {
        parent::__construct($response);

        foreach ($this->hits as &$hit) {
            $hit['_source']['diff_in_days'] = Carbon::parse($hit['_source']['created_at'])->diffInDays(Carbon::now());
        }
    }
}
