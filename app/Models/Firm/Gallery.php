<?php

namespace Coyote\Firm;

use Coyote\Services\Media\SerializeClass;
use Illuminate\Database\Eloquent\Model;
use Coyote\Services\Media\Factory as MediaFactory;

/**
 * @property string $file
 * @property string $url
 */
class Gallery extends Model
{
    use SerializeClass;

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
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            unset($model->photo);
        });
    }

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
