<?php

namespace Coyote\Alert\Providers\Topic;

use Coyote\Alert;
use Coyote\Alert\Providers\Provider;

class Subscriber extends Provider implements Alert\Providers\ProviderInterface
{
    const ID = Alert::TOPIC_SUBSCRIBER;
    const EMAIL = null;

    /**
     * @var int
     */
    protected $topicId;

    /**
     * @param int $topicId
     * @return $this
     */
    public function setTopicId($topicId)
    {
        $this->topicId = $topicId;
        return $this;
    }

    /**
     * @return int
     */
    public function getTopicId()
    {
        return $this->topicId;
    }

    /**
     * Generowanie unikalnego ciagu znakow dla wpisu na mikro
     *
     * @return string
     */
    public function objectId()
    {
        return substr(md5($this->typeId . $this->subject . $this->topicId), 16);
    }
}
