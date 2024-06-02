<?php
namespace Coyote\Wiki;

use Coyote\Services\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $name
 * @property string $mim
 * @property int $size
 * @property int $wiki_id
 * @property Media\File $file
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

    public function wiki(): BelongsTo
    {
        return $this->belongsTo('Coyote\Wiki\Page');
    }

    public function getFileAttribute($value): Media\File
    {
        if (!$value instanceof \Coyote\Services\Media\Attachment) {
            $photo = app(Media\Factory::class)->make('attachment', [
                'file_name' => $value,
                'name' => $this->attributes['name'],
            ]);
            $this->attributes['file'] = $photo;
        }
        return $this->attributes['file'];
    }
}
