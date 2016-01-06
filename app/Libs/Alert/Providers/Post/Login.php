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
    protected $postId;

    /**
     * @param int $postId
     * @return $this
     */
    public function setPostId($postId)
    {
        $this->postId = $postId;
        return $this;
    }

    /**
     * @return int
     */
    public function getPostId()
    {
        return $this->postId;
    }

    /**
     * Generowanie unikalnego ciagu znakow dla wpisu na mikro
     *
     * @return string
     */
    public function objectId()
    {
        return substr(md5($this->typeId . $this->subject . $this->postId), 16);
    }
}
