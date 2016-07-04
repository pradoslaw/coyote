<?php

namespace Coyote\Wiki;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $path_id
 * @property int $ref_id
 * @property string $path
 */
class Link extends Model
{
    /**
     * @var string
     */
    protected $table = 'wiki_links';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['path_id', 'ref_id', 'path'];

    /**
     * @var bool
     */
    public $timestamps = false;
}
