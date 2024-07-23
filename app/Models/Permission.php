<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Permission extends Model
{
    /**
     * Nie bedziemy dodawac nowych uprawnien z poziomu Laravel
     *
     * @var array
     */
    protected $fillable = ['name', 'description', 'default'];
    public $timestamps = false;

    public static function boot()
    {
        parent::boot();

        static::saving(function () {
            Cache::tags('permissions')->flush();
            return true;
        });

        static::deleting(function () {
            Cache::tags('permissions')->flush();
            return true;
        });
    }
}
