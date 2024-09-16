<?php
namespace Coyote\Notification;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $user_id
 * @property string $name
 */
class Sender extends Model
{
    protected $table = 'notification_senders';
    public $timestamps = false;
    protected $fillable = ['notification_id', 'user_id', 'name'];
}
