<?php

namespace Coyote\Firm;

use Illuminate\Database\Eloquent\Model;
use Coyote\Services\Media\Factory as MediaFactory;

class Gallery extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'firm_gallery';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['firm_id', 'file'];

    /**
     * @var array
     */
    protected $appends = ['photo'];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @param string $value
     * @return \Coyote\Services\Media\MediaInterface
     */
    public function getPhotoAttribute($value)
    {
        if (!($value instanceof \Coyote\Services\Media\Gallery)) {
            $photo = app(MediaFactory::class)->make('gallery', ['file_name' => $this->attributes['file']]);
            $this->attributes['photo'] = $photo;
        }

        return $this->attributes['photo'];
    }
}
