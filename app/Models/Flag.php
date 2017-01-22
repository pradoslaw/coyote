<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $type_id
 * @property int $user_id
 * @property int $moderator_id
 * @property string $url
 * @property mixed $metadata
 * @property string $text
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property Flag\Type $type
 */
class Flag extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['type_id', 'user_id', 'url', 'metadata', 'text', 'moderator_id'];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {
        return $this->belongsTo('Coyote\Flag\Type');
    }

    /**
     * @param $metadata
     */
    public function setMetadataAttribute($metadata)
    {
        $this->attributes['metadata'] = json_encode($metadata);
    }

    /**
     * @return mixed
     */
    public function getMetadataAttribute()
    {
        return json_decode($this->attributes['metadata']);
    }
}
