<?php

namespace Coyote\Firm;

use Coyote\Services\Media\SerializeClass;
use Illuminate\Database\Eloquent\Model;
use Coyote\Services\Media\Factory as MediaFactory;

/**
 * @deprecated
 * @property string $file
 * @property string $url
 */
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
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';
}
