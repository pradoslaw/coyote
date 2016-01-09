<?php

namespace Coyote\Alert\Providers\Post;

use Coyote\Alert;
use Coyote\Alert\Providers\Provider;

class Accept extends Provider implements Alert\Providers\ProviderInterface
{
    const ID = Alert::POST_ACCEPT;
    const EMAIL = null;

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
