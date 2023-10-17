<?php

namespace Coyote\Services;

use Carbon\Carbon;
use Coyote\Guest as Model;

/**
 * @property int $id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Guest
{
    /**
     * @var string
     */
    private $guestId;

    /**
     * Default value is null. It means we have to retrieve settings from db. Once settings are retrieved from db, this will be an empty array.
     *
     * @var \Coyote\Guest|null
     */
    protected $model = null;

    /**
     * @var Carbon
     */
    private $defaultSessionTime;

    /**
     * @param string|null $guestId
     */
    public function __construct(?string $guestId)
    {
        $this->guestId = $guestId;
    }

    /**
     * @param Carbon $defaultSessionTime
     * @return $this
     */
    public function setDefaultSessionTime(Carbon $defaultSessionTime): self
    {
        $this->defaultSessionTime = $defaultSessionTime;

        return $this;
    }

    /**
     * @return Carbon
     */
    public function getDefaultSessionTime(): Carbon
    {
        return $this->defaultSessionTime ?? Carbon::now('utc');
    }

    /**
     * @param string $name
     * @param $value
     * @return string
     */
    public function setSetting(string $name, $value)
    {
        if ($this->getSetting($name) === $value) {
            return $value;
        }

        $this->model->setSetting($name, $value);
        $this->model->save();

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

        $this->load();

        return $this->model->settings;
    }

    /**
     * @param string $name
     * @param null $default
     * @return mixed|null
     */
    public function getSetting(string $name, $default = null)
    {
        return $this->getSettings()[$name] ?? $default;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        $this->load();

        return $this->model->$name ?? null;
    }

    protected function load()
    {
        if ($this->model !== null) {
            return;
        }

        $this->model = Model::findOrNew($this->guestId, ['id', 'settings', 'created_at', 'updated_at']);

        if (!$this->model->exists) {
            $this->model->id = $this->guestId;
        }
    }
}
