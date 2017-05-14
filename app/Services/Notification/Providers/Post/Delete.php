<?php

namespace Coyote\Services\Notification\Providers\Post;

use Coyote\Notification;
use Coyote\Services\Notification\Providers\Provider;
use Coyote\Services\Notification\Providers\ProviderInterface;

class Delete extends Provider implements ProviderInterface
{
    const ID = Notification::POST_DELETE;
    const EMAIL = 'emails.notifications.post.delete';

    /**
     * @var string
     */
    private $reasonName;

    /**
     * @var string
     */
    private $reasonText;

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
}
