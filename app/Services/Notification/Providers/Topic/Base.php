<?php

namespace Coyote\Services\Notification\Providers\Topic;

use Coyote\Services\Notification\Providers\Provider;
use Coyote\Services\Notification\Providers\ProviderInterface;

abstract class Base extends Provider implements ProviderInterface
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
        return $this->reasonName ?: '(moderator nie podał powodu)';
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
        return $this->reasonText ?: '(moderator nie podał powodu)';
    }

    /**
     * @param string $reasonText
     */
    public function setReasonText($reasonText)
    {
        $this->reasonText = $reasonText;
    }

    /**
     * @inheritdoc
     */
    public function objectId()
    {
        // DO NOT use url parameter to build unique string. each notification has unique URL so we can't
        // use to to build unique notification ID.
        return substr(md5($this->typeId . $this->subject . $this->topicId), 16);
    }
}
