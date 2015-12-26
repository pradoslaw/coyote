<?php

namespace Coyote\Alert\Providers\Topic;

use Coyote\Alert;
use Coyote\Alert\Providers\Provider;

class Delete extends Provider implements Alert\Providers\ProviderInterface
{
    const ID = Alert::TOPIC_DELETE;
    const EMAIL = null;

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
}
