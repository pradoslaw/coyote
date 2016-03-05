<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'firm_id', 'name', 'description', 'recruitment'];

    /**
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:se';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return array
     */
    public static function getEmployeesList()
    {
        return ['1-5', '6-10'];
    }

    /**
     * @return array
     */
    public static function getFoundedList()
    {
        $result = [];

        for ($i = 1900; $i <= date('Y'); $i++) {
            $result[$i] = $i;
        }

        return $result;
    }
}
