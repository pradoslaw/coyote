<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

class Actkey extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['actkey', 'email', 'user_id'];
    public $timestamps = false;
    protected $primaryKey = 'actkey';
}
