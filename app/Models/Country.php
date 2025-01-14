<?php
namespace Coyote;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property float $vat_rate
 */
class Country extends Model
{
    protected $fillable = ['name'];
    public $timestamps = false;
}
