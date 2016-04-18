<?php

namespace Coyote\Services\Alert;

use Coyote\Services\Alert\Providers\ProviderInterface;

/**
 * Class Alert
 */
class Alert
{
    /**
     * @var ProviderInterface[]
     */
    protected $alerts = [];

    /**
     * @param ProviderInterface|null $alert
     */
    public function __construct(ProviderInterface $alert = null)
    {
        if ($alert) {
            $this->alerts[] = $alert;
        }
    }

    /**
     * @param ProviderInterface $alert
     * @return $this
     */
    public function attach(ProviderInterface $alert)
    {
        $this->alerts[] = $alert;
        return $this;
    }

    /**
     * Generuje i wysyla powiadomienia
     */
    public function notify()
    {
        $recipients = [];

        foreach ($this->alerts as $alert) {
            $alert->setUsersId(array_diff($alert->getUsersId(), $recipients));

            $recipients = array_merge($recipients, $alert->notify());
        }
    }
}
