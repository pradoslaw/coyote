<?php

namespace Coyote\Services;

use Coyote\Repositories\Contracts\GuestRepositoryInterface as GuestRepository;

class Guest
{
    /**
     * @var GuestRepository
     */
    private $guest;

    /**
     * @var string
     */
    private $guestId;

    /**
     * Default value is null. It means we have to retrieve settings from db. Once settings are retrieved from db, this will be an empty array.
     *
     * @var array
     */
    private $settings = null;

    /**
     * @param GuestRepository $guest
     */
    public function __construct(GuestRepository $guest)
    {
        $this->guest = $guest;
    }

    /**
     * @param string|null $guestId Can be nullable in case of error
     * @return $this
     */
    public function setGuestId(?string $guestId): self
    {
        $this->guestId = $guestId;

        return $this;
    }

    /**
     * @param string $name
     * @param $value
     * @return string
     */
    public function setSetting($name, $value)
    {
        if ($this->getSetting($name) === $value) {
            return $value;
        }

//        if (!is_array($this->settings)) {
//            $this->settings = [];
//        }

        $this->settings[$name] = $value;

        if ($this->guestId) {
            $this->guest->setSetting($name, $value, $this->guestId);
        }

        return $value;
    }

    /**
     * Get user's settings as array (setting => value)
     *
     * @return array|null
     */
    public function getSettings()
    {
        if (!$this->guestId) {
            return [];
        }

        if (is_null($this->settings)) {
            $this->settings = $this->guest->getSettings($this->guestId);
        }

        return $this->settings;
    }

    /**
     * @param string $name
     * @param null $default
     * @return mixed|null
     */
    public function getSetting($name, $default = null)
    {
        return isset($this->getSettings()[$name]) ? $this->settings[$name] : $default;
    }
}
