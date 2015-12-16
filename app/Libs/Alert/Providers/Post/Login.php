<?php

namespace Coyote\Alert\Providers\Post;

use Coyote\Alert;
use Coyote\Alert\Providers\Provider;

/**
 * Class Login
 * @package Coyote\Alert\Providers\Post
 */
class Login extends Provider implements Alert\Providers\ProviderInterface
{
    const ID = Alert::POST_LOGIN;
    const EMAIL = 'emails.alerts.post.login';

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
