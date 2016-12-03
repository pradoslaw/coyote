<?php

namespace Coyote\Listeners;

use Coyote\Events\UserWasSaved;
use Coyote\Models\Job\Preferences;
use Coyote\Repositories\Contracts\SettingRepositoryInterface as SettingRepository;

class SaveLocationsInJobPreferences
{
    /**
     * @var SettingRepository
     */
    protected $setting;

    /**
     * @param SettingRepository $setting
     */
    public function __construct(SettingRepository $setting)
    {
        $this->setting = $setting;
    }

    /**
     * Handle the event.
     *
     * @param  UserWasSaved  $event
     * @return void
     */
    public function handle(UserWasSaved $event)
    {
        $preferences = new Preferences($this->setting->getItem('job.preferences', $event->user->id, null, '{}'));

        if (empty($preferences->city)) {
            $preferences->city = $event->user->location;
            $preferences->locations = [
                'latitude'  => $event->user->latitude,
                'longitude' => $event->user->longitude
            ];

            $this->setting->setItem('job.preferences', $preferences, $event->user->id, null);
        }
    }
}
