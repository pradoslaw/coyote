<?php

namespace Coyote;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property int $user_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property mixed interests
 * @property array $settings
 */
class Guest extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'user_id', 'interests', 'settings'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * @var array
     */
    protected $casts = ['interests' => 'json', 'settings' => 'json'];

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @param string $name
     * @param string $value
     */
    public function setSetting(string $name, string $value)
    {
        $settings = $this->settings;
        $settings[$name] = $value;

        $this->settings = $settings;
    }

    /**
     * @param Session $session
     */
    public function saveWithSession(Session $session)
    {
        $this->updated_at = Carbon::createFromTimestamp($session->updatedAt);
        // @todo mozna sprawdzac czy w tabeli users nie ma usera o guest_id = $session->guestId
        // dzieki temu ta kolumna bedzie zawsze wskazywala na prawidlowego usera
        $this->user_id = $session->userId;

        if (!$this->exists) {
            $this->id = $session->guestId;
            $this->created_at = Carbon::createFromTimestamp($session->createdAt);
        }

        $this->save();
    }
}
