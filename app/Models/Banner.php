<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $filename
 * @property string $url
 * @property int $campaign_id
 * @property int $impressions
 * @property int $clicks
 */
class Banner extends Model
{
    /**
     * @var bool
     */
    public $timestamps = false;
}
