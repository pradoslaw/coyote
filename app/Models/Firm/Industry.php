<?php

namespace Coyote\Firm;

use Illuminate\Database\Eloquent\Model;

class Industry extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'firm_industries';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['firm_id', 'industry_id'];

    /**
     * @var bool
     */
    public $timestamps = false;
}
