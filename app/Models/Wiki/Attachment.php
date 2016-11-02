<?php

namespace Coyote\Wiki;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property string $mim
 * @property int $size
 * @property int $wiki_id
 * @property \Coyote\Services\Media\MediaInterface $file
 */
class Attachment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['wiki_id', 'name', 'file', 'mime', 'size'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'wiki_attachments';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function wiki()
    {
        return $this->belongsTo('Coyote\Wiki\Page');
    }

    /**
     * @return \Coyote\Services\Media\MediaInterface
     */
    public function getFileAttribute()
    {
        return app('media.attachment')->make([
            'file_name' => $this->attributes['file'],
            'name' => $this->attributes['name']
        ]);
    }
}
