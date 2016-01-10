<?php

namespace Coyote\Alert\Providers\Topic;

use Coyote\Alert;

class Move extends Base implements Alert\Providers\ProviderInterface
{
    const ID = Alert::TOPIC_MOVE;
    const EMAIL = null;

    /**
     * @var string
     */
    protected $forum;

    /**
     * @param string $forum
     * @return $this
     */
    public function setForum($forum)
    {
        $this->forum = $forum;
        return $this;
    }

    /**
     * @return string
     */
    public function getForum()
    {
        return $this->forum;
    }
}
