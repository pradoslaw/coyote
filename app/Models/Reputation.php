<?php

namespace Coyote;

use Illuminate\Database\Eloquent\Model;

class Reputation extends Model
{
    const POST_VOTE = 1;
    const POST_ACCEPT = 2;
    const MICROBLOG = 3;
    const MICROBLOG_VOTE = 4;
    const WIKI_CREATE = 5;
    const WIKI_EDIT = 6;
    const CUSTOM = 7;
    const WIKI_RATE = 8;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['type_id', 'user_id', 'value', 'excerpt', 'url', 'metadata'];

    /**
     * @var bool
     */
    public $timestamps = false;

    public function getMetadataAttribute($metadata)
    {
        return json_decode($metadata, true);
    }

    public function setMetadataAttribute($metadata)
    {
        $this->attributes['metadata'] = json_encode($metadata);
    }

    /**
     * Pobiera reputacje usera w procentach (jak i rowniez pozycje usera w rankingu)
     *
     * @param $userId
     * @return null|array
     */
    public static function getUserRank($userId)
    {
        $sql = "SELECT u1.reputation AS reputation,
                (
                    u1.reputation / GREATEST(1, (

                        SELECT reputation
                        FROM users u2
                        ORDER BY u2.reputation DESC
                        LIMIT 1
                    )) * 100

                ) AS percentage,

                (
                    SELECT COUNT(*)
                    FROM users
                    WHERE reputation >= u1.reputation

                ) AS rank
                FROM users u1
                WHERE id = ?";

        $rowset = \DB::select($sql, [$userId]);

        // select() zwraca kolekcje. nas interesuje tylko jeden rekord
        if ($rowset) {
            return $rowset[0];
        } else {
            return null;
        }
    }

    /**
     * Podaje liczbe userow ktorzy maja jakakolwiek reputacje w systemie
     *
     * @return int
     */
    public static function getTotalUsers()
    {
        return \DB::table('users')->where('reputation', '>', 0)->count();
    }
}
