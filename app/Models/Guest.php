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
    protected $keyType = 'string';
    protected $fillable = ['id', 'user_id', 'interests', 'settings'];
    protected $dateFormat = 'Y-m-d H:i:se';
    protected $casts = ['interests' => 'json', 'settings' => 'json'];
    public $incrementing = false;

    public function setSetting(string $name, string|array $value): void
    {
        $settings = $this->settings;
        $settings[$name] = $value;
        $this->settings = $settings;
    }

    public function saveWithSession(Session $session): void
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
