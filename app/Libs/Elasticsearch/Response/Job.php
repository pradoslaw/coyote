<?php

namespace Coyote\Elasticsearch\Response;

use Carbon\Carbon;
use Coyote\Elasticsearch\ResponseInterface;
use Coyote\Elasticsearch\Response;

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
