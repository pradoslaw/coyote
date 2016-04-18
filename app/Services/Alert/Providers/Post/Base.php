<?php

namespace Coyote\Services\Alert\Providers\Post;

use Coyote\Alert;
use Coyote\Services\Alert\Providers\Provider;
use Coyote\Services\Alert\Providers\ProviderInterface;

abstract class Base extends Provider implements ProviderInterface
{
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
