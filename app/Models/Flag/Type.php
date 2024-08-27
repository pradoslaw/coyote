<?php
namespace Coyote\Flag;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int id
 * @property string $name
 * @property string $description
 */
class Type extends Model
{
    protected $table = 'flag_types';
    protected $fillable = ['id', 'name', 'description', 'order'];
    public $timestamps = false;
}
