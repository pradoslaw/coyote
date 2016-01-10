<?php

namespace Coyote\Alert\Providers\Topic;

use Coyote\Alert;

class Move extends Base implements Alert\Providers\ProviderInterface
{
    const ID = Alert::TOPIC_MOVE;
    const EMAIL = 'emails.alerts.topic.move';

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

    /**
     * Object ID for this action. We don't want to join notification of this type.
     *
     * @return string
     */
    public function objectId()
    {
        return substr(md5(uniqid()), 16);
    }
}
