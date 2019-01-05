<?php

namespace Coyote\Services\Elasticsearch\Builders\Stream;

use Coyote\Services\Elasticsearch\Filters\Stream\IncludeObject;
use Coyote\Services\Elasticsearch\Filters\Stream\IncludeTarget;
use Coyote\Services\Elasticsearch\QueryBuilder;

class TopicBuilder extends AdmBuilder
{
    /**
     * @var int
     */
    protected $topicId;

    /**
     * @param mixed $topicId
     * @return $this
     */
    public function setTopicId(int $topicId)
    {
        $this->topicId = $topicId;

        return $this;
    }

    public function build()
    {
        $this->buildSort();
        $this->buildSize();

        $this->should(new IncludeObject($this->topicId));
        $this->should(new IncludeTarget($this->topicId));

        return QueryBuilder::build();
    }
}
