<?php
namespace Coyote\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $title
 */
class Survey extends Model
{
    protected $fillable = ['title'];
}
