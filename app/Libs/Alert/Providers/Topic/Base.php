<?php

namespace Coyote\Alert\Providers\Topic;

use Coyote\Alert;
use Coyote\Alert\Providers\Provider;

abstract class Base extends Provider implements Alert\Providers\ProviderInterface
{
    /**
     * @var int
     */
    protected $topicId;

    /**
     * @var string
     */
    private $reasonName;

    /**
     * @var string
     */
    private $reasonText;

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
     * @return string
     */
    public function getReasonName()
    {
        return $this->reasonName;
    }

    /**
     * @param string $reasonName
     */
    public function setReasonName($reasonName)
    {
        $this->reasonName = $reasonName;
    }

    /**
     * @return string
     */
    public function getReasonText()
    {
        return $this->reasonText;
    }

    /**
     * @param string $reasonText
     */
    public function setReasonText($reasonText)
    {
        $this->reasonText = $reasonText;
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
