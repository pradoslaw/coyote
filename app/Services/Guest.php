<?php
namespace Coyote\Services;

use Carbon\Carbon;

/**
 * @property int $id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Guest
{
    /**
     * Default value is null. It means we have to retrieve settings from db. Once settings are retrieved from db, this will be an empty array.
     */
    protected ?\Coyote\Guest $model = null;
    private ?Carbon $defaultSessionTime;

    public function __construct(private ?string $guestId) {}

    public function setDefaultSessionTime(Carbon $defaultSessionTime): self
    {
        $this->defaultSessionTime = $defaultSessionTime;
        return $this;
    }

    public function getDefaultSessionTime(): Carbon
    {
        return $this->defaultSessionTime ?? Carbon::now('utc');
    }

    public function setSetting(string $name, $value)
    {
        if ($this->getSetting($name) === $value) {
            return $value;
        }
        $this->model->setSetting($name, $value);
        $this->model->save();
        return $value;
    }

    public function getSettings(): ?array
    {
        if (!$this->guestId) {
            return [];
        }
        $this->load();
        return $this->model->settings;
    }

    public function getSetting(string $name, $default = null)
    {
        return $this->getSettings()[$name] ?? $default;
    }

    public function __get($name)
    {
        $this->load();
        return $this->model->$name ?? null;
    }

    private function load(): void
    {
        if ($this->model !== null) {
            return;
        }
        $this->model = \Coyote\Guest::query()->findOrNew($this->guestId, ['id', 'settings', 'created_at', 'updated_at']);
        if (!$this->model->exists) {
            $this->model->id = $this->guestId;
        }
    }

    public function createIfMissing(): void
    {
        $this->load();
        $this->model->save();
    }
}
